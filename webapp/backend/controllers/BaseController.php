<?php

namespace app\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\web\ForbiddenHttpException;

/**
 * Base Controller for API
 */
class BaseController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        // CORS filter
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 86400,
            ],
        ];
        
        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        if ($action->id === 'options') {
            return true;
        }
        
        return parent::beforeAction($action);
    }

    /**
     * Handle OPTIONS requests
     */
    public function actionOptions()
    {
        return '';
    }

    /**
     * Check user permission
     */
    protected function checkPermission($permission)
    {
        $user = Yii::$app->user->identity;
        
        if (!$user || !$user->can($permission)) {
            throw new ForbiddenHttpException('You do not have permission to perform this action');
        }
    }

    /**
     * Check branch access
     */
    protected function checkBranchAccess($branchId)
    {
        $user = Yii::$app->user->identity;
        
        if (!$user || !$user->canAccessBranch($branchId)) {
            throw new ForbiddenHttpException('You do not have access to this branch');
        }
    }

    /**
     * Success response
     */
    protected function successResponse($data = null, $message = 'Success', $statusCode = 200)
    {
        Yii::$app->response->statusCode = $statusCode;
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Error response
     */
    protected function errorResponse($message = 'Error', $statusCode = 400, $errors = null)
    {
        Yii::$app->response->statusCode = $statusCode;
        return [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Paginated response
     */
    protected function paginatedResponse($dataProvider, $message = 'Success')
    {
        $models = $dataProvider->getModels();
        $pagination = $dataProvider->getPagination();
        
        return [
            'success' => true,
            'message' => $message,
            'data' => $models,
            'pagination' => [
                'total_count' => $dataProvider->getTotalCount(),
                'page_count' => $pagination ? $pagination->getPageCount() : 1,
                'current_page' => $pagination ? $pagination->getPage() + 1 : 1,
                'per_page' => $pagination ? $pagination->getPageSize() : count($models),
            ],
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Log activity
     */
    protected function logActivity($action, $tableName, $recordId = null, $oldValues = null, $newValues = null)
    {
        $user = Yii::$app->user->identity;
        
        if (!$user) {
            return;
        }
        
        try {
            Yii::$app->db->createCommand()->insert('audit_logs', [
                'user_id' => $user->id,
                'action' => $action,
                'table_name' => $tableName,
                'record_id' => $recordId,
                'old_values' => $oldValues ? json_encode($oldValues) : null,
                'new_values' => $newValues ? json_encode($newValues) : null,
                'ip_address' => Yii::$app->request->userIP,
                'user_agent' => Yii::$app->request->userAgent,
                'created_at' => date('Y-m-d H:i:s'),
            ])->execute();
        } catch (\Exception $e) {
            Yii::error('Failed to log activity: ' . $e->getMessage());
        }
    }

    /**
     * Validate request data
     */
    protected function validateRequest($rules, $data = null)
    {
        if ($data === null) {
            $data = Yii::$app->request->post();
        }
        
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            // Required validation
            if (isset($rule['required']) && $rule['required'] && empty($value)) {
                $errors[$field][] = "{$field} is required";
                continue;
            }
            
            // Skip other validations if value is empty and not required
            if (empty($value) && !isset($rule['required'])) {
                continue;
            }
            
            // Type validation
            if (isset($rule['type'])) {
                switch ($rule['type']) {
                    case 'integer':
                        if (!is_numeric($value) || (int)$value != $value) {
                            $errors[$field][] = "{$field} must be an integer";
                        }
                        break;
                    case 'number':
                        if (!is_numeric($value)) {
                            $errors[$field][] = "{$field} must be a number";
                        }
                        break;
                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = "{$field} must be a valid email address";
                        }
                        break;
                    case 'array':
                        if (!is_array($value)) {
                            $errors[$field][] = "{$field} must be an array";
                        }
                        break;
                }
            }
            
            // Length validation
            if (isset($rule['min_length']) && strlen($value) < $rule['min_length']) {
                $errors[$field][] = "{$field} must be at least {$rule['min_length']} characters";
            }
            
            if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
                $errors[$field][] = "{$field} must not exceed {$rule['max_length']} characters";
            }
            
            // Range validation
            if (isset($rule['min']) && is_numeric($value) && $value < $rule['min']) {
                $errors[$field][] = "{$field} must be at least {$rule['min']}";
            }
            
            if (isset($rule['max']) && is_numeric($value) && $value > $rule['max']) {
                $errors[$field][] = "{$field} must not exceed {$rule['max']}";
            }
            
            // In array validation
            if (isset($rule['in']) && !in_array($value, $rule['in'])) {
                $allowed = implode(', ', $rule['in']);
                $errors[$field][] = "{$field} must be one of: {$allowed}";
            }
        }
        
        if (!empty($errors)) {
            return $this->errorResponse('Validation failed', 422, $errors);
        }
        
        return null; // No errors
    }

    /**
     * Format model for API response
     */
    protected function formatModel($model, $includes = [])
    {
        $data = $model->toArray();
        
        // Include related data
        foreach ($includes as $relation) {
            if ($model->hasProperty($relation)) {
                $relationData = $model->$relation;
                
                if (is_array($relationData)) {
                    $data[$relation] = array_map(function($item) {
                        return $item->toArray();
                    }, $relationData);
                } elseif ($relationData) {
                    $data[$relation] = $relationData->toArray();
                } else {
                    $data[$relation] = null;
                }
            }
        }
        
        return $data;
    }

    /**
     * Generate unique number
     */
    protected function generateUniqueNumber($prefix, $table, $column, $length = 8)
    {
        do {
            $number = $prefix . str_pad(rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
            $exists = Yii::$app->db->createCommand(
                "SELECT COUNT(*) FROM {$table} WHERE {$column} = :number"
            )->bindValue(':number', $number)->queryScalar();
        } while ($exists > 0);
        
        return $number;
    }
}