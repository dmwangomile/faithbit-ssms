<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\Json;

/**
 * Product model
 *
 * @property integer $id
 * @property string $sku
 * @property string $name
 * @property string $name_sw
 * @property string $description
 * @property string $description_sw
 * @property integer $category_id
 * @property string $brand
 * @property string $model
 * @property string $type
 * @property string $unit_of_measure
 * @property string $barcode
 * @property string $qr_code
 * @property float $weight
 * @property string $dimensions
 * @property string $color
 * @property integer $warranty_months
 * @property boolean $has_serial
 * @property boolean $has_imei
 * @property boolean $track_expiry
 * @property float $cost_price
 * @property float $selling_price
 * @property float $wholesale_price
 * @property float $minimum_price
 * @property float $tax_rate
 * @property integer $reorder_level
 * @property integer $reorder_quantity
 * @property boolean $is_active
 * @property string $images JSON
 * @property string $attributes JSON
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * 
 * @property ProductCategory $category
 * @property InventoryStock[] $inventoryStock
 * @property ProductSerial[] $serials
 */
class Product extends ActiveRecord
{
    const TYPE_PRODUCT = 'product';
    const TYPE_SERVICE = 'service';
    const TYPE_KIT = 'kit';
    const TYPE_SPARE_PART = 'spare_part';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%products}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sku', 'name', 'category_id'], 'required'],
            ['sku', 'unique'],
            ['sku', 'string', 'max' => 50],
            [['name', 'name_sw'], 'string', 'max' => 200],
            [['description', 'description_sw'], 'string'],
            ['category_id', 'integer'],
            ['category_id', 'exist', 'targetClass' => ProductCategory::class, 'targetAttribute' => 'id'],
            [['brand', 'model', 'unit_of_measure'], 'string', 'max' => 100],
            ['type', 'in', 'range' => [self::TYPE_PRODUCT, self::TYPE_SERVICE, self::TYPE_KIT, self::TYPE_SPARE_PART]],
            ['type', 'default', 'value' => self::TYPE_PRODUCT],
            ['unit_of_measure', 'default', 'value' => 'piece'],
            ['barcode', 'string', 'max' => 100],
            ['qr_code', 'string', 'max' => 500],
            ['weight', 'number', 'min' => 0],
            ['dimensions', 'string', 'max' => 100],
            ['color', 'string', 'max' => 50],
            ['warranty_months', 'integer', 'min' => 0],
            ['warranty_months', 'default', 'value' => 0],
            [['has_serial', 'has_imei', 'track_expiry', 'is_active'], 'boolean'],
            [['has_serial', 'has_imei', 'track_expiry'], 'default', 'value' => false],
            ['is_active', 'default', 'value' => true],
            [['cost_price', 'selling_price', 'wholesale_price', 'minimum_price', 'tax_rate'], 'number', 'min' => 0],
            [['cost_price', 'selling_price', 'wholesale_price', 'minimum_price'], 'default', 'value' => 0.00],
            ['tax_rate', 'default', 'value' => 18.00],
            [['reorder_level', 'reorder_quantity'], 'integer', 'min' => 0],
            ['reorder_level', 'default', 'value' => 10],
            ['reorder_quantity', 'default', 'value' => 50],
            [['images', 'attributes'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sku' => 'SKU',
            'name' => 'Name',
            'name_sw' => 'Name (Swahili)',
            'description' => 'Description',
            'description_sw' => 'Description (Swahili)',
            'category_id' => 'Category',
            'brand' => 'Brand',
            'model' => 'Model',
            'type' => 'Type',
            'unit_of_measure' => 'Unit of Measure',
            'barcode' => 'Barcode',
            'qr_code' => 'QR Code',
            'weight' => 'Weight (kg)',
            'dimensions' => 'Dimensions',
            'color' => 'Color',
            'warranty_months' => 'Warranty (months)',
            'has_serial' => 'Has Serial Number',
            'has_imei' => 'Has IMEI',
            'track_expiry' => 'Track Expiry',
            'cost_price' => 'Cost Price',
            'selling_price' => 'Selling Price',
            'wholesale_price' => 'Wholesale Price',
            'minimum_price' => 'Minimum Price',
            'tax_rate' => 'Tax Rate (%)',
            'reorder_level' => 'Reorder Level',
            'reorder_quantity' => 'Reorder Quantity',
            'is_active' => 'Active',
            'images' => 'Images',
            'attributes' => 'Attributes',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Get category relation
     */
    public function getCategory()
    {
        return $this->hasOne(ProductCategory::class, ['id' => 'category_id']);
    }

    /**
     * Get inventory stock relation
     */
    public function getInventoryStock()
    {
        return $this->hasMany(InventoryStock::class, ['product_id' => 'id']);
    }

    /**
     * Get product serials relation
     */
    public function getSerials()
    {
        return $this->hasMany(ProductSerial::class, ['product_id' => 'id']);
    }

    /**
     * Get product kit components
     */
    public function getKitComponents()
    {
        return $this->hasMany(ProductKit::class, ['parent_product_id' => 'id']);
    }

    /**
     * Get available serials
     */
    public function getAvailableSerials($warehouseId = null)
    {
        $query = $this->getSerials()->where(['status' => 'available']);
        if ($warehouseId) {
            $query->andWhere(['warehouse_id' => $warehouseId]);
        }
        return $query->all();
    }

    /**
     * Get total stock across all warehouses
     */
    public function getTotalStock()
    {
        return (int) $this->getInventoryStock()->sum('quantity_on_hand');
    }

    /**
     * Get available stock across all warehouses
     */
    public function getAvailableStock()
    {
        return (int) $this->getInventoryStock()->sum('quantity_available');
    }

    /**
     * Get stock for specific warehouse
     */
    public function getWarehouseStock($warehouseId)
    {
        $stock = $this->getInventoryStock()->where(['warehouse_id' => $warehouseId])->one();
        return $stock ? $stock->quantity_available : 0;
    }

    /**
     * Check if product is low stock
     */
    public function isLowStock()
    {
        return $this->getAvailableStock() <= $this->reorder_level;
    }

    /**
     * Get images as array
     */
    public function getImagesArray()
    {
        return $this->images ? Json::decode($this->images) : [];
    }

    /**
     * Set images from array
     */
    public function setImagesArray($images)
    {
        $this->images = $images ? Json::encode($images) : null;
    }

    /**
     * Get attributes as array
     */
    public function getAttributesArray()
    {
        return $this->attributes ? Json::decode($this->attributes) : [];
    }

    /**
     * Set attributes from array
     */
    public function setAttributesArray($attributes)
    {
        $this->attributes = $attributes ? Json::encode($attributes) : null;
    }

    /**
     * Get localized name
     */
    public function getLocalizedName($language = null)
    {
        if (!$language) {
            $language = Yii::$app->language;
        }
        
        if ($language === 'sw-TZ' && $this->name_sw) {
            return $this->name_sw;
        }
        
        return $this->name;
    }

    /**
     * Get localized description
     */
    public function getLocalizedDescription($language = null)
    {
        if (!$language) {
            $language = Yii::$app->language;
        }
        
        if ($language === 'sw-TZ' && $this->description_sw) {
            return $this->description_sw;
        }
        
        return $this->description;
    }

    /**
     * Calculate selling price with tax
     */
    public function getSellingPriceWithTax()
    {
        return $this->selling_price * (1 + $this->tax_rate / 100);
    }

    /**
     * Calculate wholesale price with tax
     */
    public function getWholesalePriceWithTax()
    {
        return $this->wholesale_price * (1 + $this->tax_rate / 100);
    }

    /**
     * Get price for customer type
     */
    public function getPriceForCustomer($customerType = 'retail')
    {
        switch ($customerType) {
            case 'wholesale':
                return $this->wholesale_price;
            case 'retail':
            default:
                return $this->selling_price;
        }
    }

    /**
     * Reserve stock
     */
    public function reserveStock($warehouseId, $quantity)
    {
        $stock = InventoryStock::findOne(['product_id' => $this->id, 'warehouse_id' => $warehouseId]);
        if (!$stock) {
            throw new \Exception('Product not found in warehouse');
        }
        
        if ($stock->quantity_available < $quantity) {
            throw new \Exception('Insufficient stock available');
        }
        
        $stock->quantity_reserved += $quantity;
        return $stock->save();
    }

    /**
     * Release reserved stock
     */
    public function releaseStock($warehouseId, $quantity)
    {
        $stock = InventoryStock::findOne(['product_id' => $this->id, 'warehouse_id' => $warehouseId]);
        if (!$stock) {
            return false;
        }
        
        $stock->quantity_reserved = max(0, $stock->quantity_reserved - $quantity);
        return $stock->save();
    }

    /**
     * Consume stock (remove from inventory)
     */
    public function consumeStock($warehouseId, $quantity)
    {
        $stock = InventoryStock::findOne(['product_id' => $this->id, 'warehouse_id' => $warehouseId]);
        if (!$stock) {
            throw new \Exception('Product not found in warehouse');
        }
        
        if ($stock->quantity_reserved < $quantity) {
            throw new \Exception('Insufficient reserved stock');
        }
        
        $stock->quantity_on_hand -= $quantity;
        $stock->quantity_reserved -= $quantity;
        $stock->last_movement_date = new Expression('NOW()');
        
        return $stock->save();
    }

    /**
     * Search products
     */
    public static function search($query, $limit = 50)
    {
        return static::find()
            ->where(['is_active' => true])
            ->andWhere([
                'or',
                ['like', 'name', $query],
                ['like', 'name_sw', $query],
                ['like', 'sku', $query],
                ['like', 'barcode', $query],
                ['like', 'brand', $query],
                ['like', 'model', $query],
            ])
            ->limit($limit)
            ->all();
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Auto-generate barcode if not provided
            if (!$this->barcode && $insert) {
                $this->barcode = $this->generateBarcode();
            }
            
            return true;
        }
        return false;
    }

    /**
     * Generate barcode
     */
    private function generateBarcode()
    {
        // Simple EAN-13 style barcode generation
        // In production, use proper barcode generation library
        $prefix = '620'; // Tanzania country code
        $company = '1234'; // Company code
        $product = str_pad($this->id ?? rand(10000, 99999), 5, '0', STR_PAD_LEFT);
        
        $partial = $prefix . $company . $product;
        $checksum = $this->calculateEAN13Checksum($partial);
        
        return $partial . $checksum;
    }

    /**
     * Calculate EAN-13 checksum
     */
    private function calculateEAN13Checksum($code)
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $code[$i] * (($i % 2 === 0) ? 1 : 3);
        }
        return (10 - ($sum % 10)) % 10;
    }
}