<?php

namespace app\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
use yii\filters\Cors;
use app\models\Product;
use app\models\ProductCategory;
use app\controllers\BaseController;

/**
 * Product Controller
 */
class ProductController extends BaseController
{
    public $modelClass = Product::class;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        // Authentication required for all actions
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['options'],
        ];
        
        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        $actions = parent::actions();
        
        // Customize default actions
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        
        return $actions;
    }

    /**
     * Prepare data provider for index action
     */
    public function prepareDataProvider()
    {
        $request = Yii::$app->request;
        
        $query = Product::find()->where(['is_active' => true]);
        
        // Search functionality
        $search = $request->get('search');
        if ($search) {
            $query->andWhere([
                'or',
                ['like', 'name', $search],
                ['like', 'name_sw', $search],
                ['like', 'sku', $search],
                ['like', 'barcode', $search],
                ['like', 'brand', $search],
                ['like', 'model', $search],
            ]);
        }
        
        // Filter by category
        $categoryId = $request->get('category_id');
        if ($categoryId) {
            $query->andWhere(['category_id' => $categoryId]);
        }
        
        // Filter by type
        $type = $request->get('type');
        if ($type) {
            $query->andWhere(['type' => $type]);
        }
        
        // Filter by brand
        $brand = $request->get('brand');
        if ($brand) {
            $query->andWhere(['brand' => $brand]);
        }
        
        // Low stock filter
        $lowStock = $request->get('low_stock');
        if ($lowStock === '1' || $lowStock === 'true') {
            $query->joinWith('inventoryStock')
                  ->andWhere('inventory_stock.quantity_available <= products.reorder_level');
        }
        
        // Include relations
        $query->with(['category', 'inventoryStock']);
        
        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $request->get('per_page', 20),
            ],
            'sort' => [
                'defaultOrder' => ['name' => SORT_ASC],
                'attributes' => [
                    'id', 'sku', 'name', 'brand', 'model', 
                    'cost_price', 'selling_price', 'created_at'
                ],
            ],
        ]);
    }

    /**
     * View product action
     */
    public function actionView($id)
    {
        $this->checkPermission('product.view');
        
        $product = $this->findModel($id);
        
        // Include inventory stock and serials
        $product = Product::find()
            ->where(['id' => $id])
            ->with(['category', 'inventoryStock.warehouse', 'serials'])
            ->one();
            
        if (!$product) {
            throw new NotFoundHttpException('Product not found');
        }
        
        // Format response
        $data = $product->toArray();
        $data['category'] = $product->category ? $product->category->toArray() : null;
        $data['images'] = $product->getImagesArray();
        $data['attributes'] = $product->getAttributesArray();
        $data['total_stock'] = $product->getTotalStock();
        $data['available_stock'] = $product->getAvailableStock();
        $data['is_low_stock'] = $product->isLowStock();
        
        // Warehouse stock details
        $data['warehouse_stock'] = [];
        foreach ($product->inventoryStock as $stock) {
            $data['warehouse_stock'][] = [
                'warehouse_id' => $stock->warehouse_id,
                'warehouse_name' => $stock->warehouse->name,
                'warehouse_code' => $stock->warehouse->code,
                'quantity_on_hand' => $stock->quantity_on_hand,
                'quantity_reserved' => $stock->quantity_reserved,
                'quantity_available' => $stock->quantity_available,
                'cost_price' => $stock->cost_price,
                'bin_location' => $stock->bin_location,
            ];
        }
        
        return $this->successResponse($data);
    }

    /**
     * Create product action
     */
    public function actionCreate()
    {
        $this->checkPermission('product.create');
        
        $model = new Product();
        $model->load(Yii::$app->request->post(), '');
        
        if ($model->validate() && $model->save()) {
            // Handle images and attributes if provided
            $this->handleProductAssets($model);
            
            return $this->successResponse($model->toArray(), 'Product created successfully', 201);
        }
        
        return $this->errorResponse('Validation failed', 422, $model->errors);
    }

    /**
     * Update product action
     */
    public function actionUpdate($id)
    {
        $this->checkPermission('product.update');
        
        $model = $this->findModel($id);
        $model->load(Yii::$app->request->post(), '');
        
        if ($model->validate() && $model->save()) {
            // Handle images and attributes if provided
            $this->handleProductAssets($model);
            
            return $this->successResponse($model->toArray(), 'Product updated successfully');
        }
        
        return $this->errorResponse('Validation failed', 422, $model->errors);
    }

    /**
     * Delete product action (soft delete)
     */
    public function actionDelete($id)
    {
        $this->checkPermission('product.delete');
        
        $model = $this->findModel($id);
        $model->is_active = false;
        $model->deleted_at = new \yii\db\Expression('NOW()');
        
        if ($model->save(false)) {
            return $this->successResponse(null, 'Product deleted successfully');
        }
        
        return $this->errorResponse('Failed to delete product');
    }

    /**
     * Search products for POS/Sales
     */
    public function actionSearch()
    {
        $this->checkPermission('product.view');
        
        $query = Yii::$app->request->get('q', '');
        $warehouseId = Yii::$app->request->get('warehouse_id');
        $limit = (int) Yii::$app->request->get('limit', 10);
        
        if (strlen($query) < 2) {
            return $this->errorResponse('Search query must be at least 2 characters', 400);
        }
        
        $products = Product::search($query, $limit);
        
        $results = [];
        foreach ($products as $product) {
            $stock = $warehouseId ? $product->getWarehouseStock($warehouseId) : $product->getAvailableStock();
            
            $results[] = [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->getLocalizedName(),
                'brand' => $product->brand,
                'model' => $product->model,
                'barcode' => $product->barcode,
                'selling_price' => $product->selling_price,
                'wholesale_price' => $product->wholesale_price,
                'tax_rate' => $product->tax_rate,
                'has_serial' => $product->has_serial,
                'has_imei' => $product->has_imei,
                'available_stock' => $stock,
                'is_low_stock' => $product->isLowStock(),
                'unit_of_measure' => $product->unit_of_measure,
            ];
        }
        
        return $this->successResponse($results);
    }

    /**
     * Get product by barcode
     */
    public function actionByBarcode()
    {
        $this->checkPermission('product.view');
        
        $barcode = Yii::$app->request->get('barcode');
        $warehouseId = Yii::$app->request->get('warehouse_id');
        
        if (!$barcode) {
            return $this->errorResponse('Barcode is required', 400);
        }
        
        $product = Product::findOne(['barcode' => $barcode, 'is_active' => true]);
        
        if (!$product) {
            return $this->errorResponse('Product not found', 404);
        }
        
        $stock = $warehouseId ? $product->getWarehouseStock($warehouseId) : $product->getAvailableStock();
        
        $data = [
            'id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->getLocalizedName(),
            'description' => $product->getLocalizedDescription(),
            'brand' => $product->brand,
            'model' => $product->model,
            'barcode' => $product->barcode,
            'selling_price' => $product->selling_price,
            'wholesale_price' => $product->wholesale_price,
            'tax_rate' => $product->tax_rate,
            'has_serial' => $product->has_serial,
            'has_imei' => $product->has_imei,
            'available_stock' => $stock,
            'is_low_stock' => $product->isLowStock(),
            'unit_of_measure' => $product->unit_of_measure,
            'images' => $product->getImagesArray(),
        ];
        
        return $this->successResponse($data);
    }

    /**
     * Get low stock products
     */
    public function actionLowStock()
    {
        $this->checkPermission('inventory.view');
        
        $query = Product::find()
            ->joinWith('inventoryStock')
            ->where(['products.is_active' => true])
            ->andWhere('inventory_stock.quantity_available <= products.reorder_level')
            ->with(['category', 'inventoryStock.warehouse']);
            
        $products = $query->all();
        
        $results = [];
        foreach ($products as $product) {
            $results[] = [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'brand' => $product->brand,
                'category' => $product->category->name,
                'reorder_level' => $product->reorder_level,
                'reorder_quantity' => $product->reorder_quantity,
                'available_stock' => $product->getAvailableStock(),
                'warehouse_stock' => array_map(function($stock) {
                    return [
                        'warehouse_name' => $stock->warehouse->name,
                        'available' => $stock->quantity_available,
                    ];
                }, $product->inventoryStock),
            ];
        }
        
        return $this->successResponse($results);
    }

    /**
     * Handle product images and attributes
     */
    protected function handleProductAssets(Product $product)
    {
        $request = Yii::$app->request;
        
        // Handle images
        $images = $request->post('images');
        if (is_array($images)) {
            $product->setImagesArray($images);
        }
        
        // Handle attributes
        $attributes = $request->post('attributes');
        if (is_array($attributes)) {
            $product->setAttributesArray($attributes);
        }
        
        if ($product->isDirty) {
            $product->save(false);
        }
    }

    /**
     * Find model by ID
     */
    protected function findModel($id)
    {
        $model = Product::findOne(['id' => $id, 'is_active' => true]);
        
        if (!$model) {
            throw new NotFoundHttpException('Product not found');
        }
        
        return $model;
    }
}