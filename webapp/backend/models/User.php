<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property string $first_name
 * @property string $last_name
 * @property string $phone
 * @property integer $branch_id
 * @property string $role
 * @property string $status
 * @property string $last_login_at
 * @property string $password_reset_token
 * @property string $auth_key
 * @property string $access_token
 * @property string $refresh_token
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * 
 * @property Branch $branch
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';
    
    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';
    const ROLE_CASHIER = 'cashier';
    const ROLE_SALES_REP = 'sales_rep';
    const ROLE_TECHNICIAN = 'technician';
    const ROLE_INVENTORY_MANAGER = 'inventory_manager';
    const ROLE_PROCUREMENT_OFFICER = 'procurement_officer';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%users}}';
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
            [['username', 'email', 'password_hash', 'first_name', 'last_name', 'role'], 'required'],
            [['username', 'email'], 'unique'],
            ['email', 'email'],
            ['username', 'string', 'max' => 100],
            ['email', 'string', 'max' => 255],
            [['first_name', 'last_name'], 'string', 'max' => 100],
            ['phone', 'string', 'max' => 20],
            ['branch_id', 'integer'],
            ['role', 'in', 'range' => [
                self::ROLE_ADMIN,
                self::ROLE_MANAGER,
                self::ROLE_CASHIER,
                self::ROLE_SALES_REP,
                self::ROLE_TECHNICIAN,
                self::ROLE_INVENTORY_MANAGER,
                self::ROLE_PROCUREMENT_OFFICER
            ]],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_SUSPENDED]],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'email' => 'Email',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'phone' => 'Phone',
            'branch_id' => 'Branch',
            'role' => 'Role',
            'status' => 'Status',
            'last_login_at' => 'Last Login',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Get branch relation
     */
    public function getBranch()
    {
        return $this->hasOne(Branch::class, ['id' => 'branch_id']);
    }

    /**
     * Get full name
     */
    public function getFullName()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        try {
            $jwtParams = Yii::$app->params['jwt'];
            $decoded = JWT::decode($token, new Key($jwtParams['key'], $jwtParams['algorithm']));
            
            if ($decoded->type !== 'access') {
                return null;
            }
            
            return static::findOne(['id' => $decoded->uid, 'status' => self::STATUS_ACTIVE]);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Find user by username
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Find user by email
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validate password
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Set password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generate access token
     */
    public function generateAccessToken()
    {
        $jwtParams = Yii::$app->params['jwt'];
        
        $payload = [
            'uid' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            'branch_id' => $this->branch_id,
            'type' => 'access',
            'iat' => time(),
            'exp' => time() + $jwtParams['expiration'],
        ];
        
        $token = JWT::encode($payload, $jwtParams['key'], $jwtParams['algorithm']);
        
        $this->access_token = $token;
        $this->save(false);
        
        return $token;
    }

    /**
     * Generate refresh token
     */
    public function generateRefreshToken()
    {
        $jwtParams = Yii::$app->params['jwt'];
        
        $payload = [
            'uid' => $this->id,
            'type' => 'refresh',
            'iat' => time(),
            'exp' => time() + $jwtParams['refresh_expiration'],
        ];
        
        $token = JWT::encode($payload, $jwtParams['key'], $jwtParams['algorithm']);
        
        $this->refresh_token = $token;
        $this->save(false);
        
        return $token;
    }

    /**
     * Revoke tokens
     */
    public function revokeTokens()
    {
        $this->access_token = null;
        $this->refresh_token = null;
        $this->save(false);
    }

    /**
     * Update last login
     */
    public function updateLastLogin()
    {
        $this->last_login_at = new Expression('NOW()');
        $this->save(false);
    }

    /**
     * Check if user has role
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Check if user can access branch
     */
    public function canAccessBranch($branchId)
    {
        if ($this->role === self::ROLE_ADMIN) {
            return true;
        }
        
        return $this->branch_id == $branchId;
    }

    /**
     * Get role permissions
     */
    public function getPermissions()
    {
        $permissions = [
            self::ROLE_ADMIN => ['*'],
            self::ROLE_MANAGER => [
                'branch.*', 'sales.*', 'service.*', 'inventory.*', 
                'customer.*', 'product.*', 'report.*', 'dashboard.*'
            ],
            self::ROLE_CASHIER => [
                'pos.*', 'customer.view', 'customer.create', 'product.view', 
                'payment.*', 'inventory.view'
            ],
            self::ROLE_SALES_REP => [
                'sales.*', 'customer.*', 'product.view', 'inventory.view',
                'quote.*', 'order.*'
            ],
            self::ROLE_TECHNICIAN => [
                'service.*', 'customer.view', 'product.view', 'inventory.view'
            ],
            self::ROLE_INVENTORY_MANAGER => [
                'inventory.*', 'product.*', 'procurement.*', 'supplier.*',
                'warehouse.*', 'report.inventory'
            ],
            self::ROLE_PROCUREMENT_OFFICER => [
                'procurement.*', 'supplier.*', 'product.view', 'inventory.view',
                'report.procurement'
            ],
        ];
        
        return $permissions[$this->role] ?? [];
    }

    /**
     * Check permission
     */
    public function can($permission)
    {
        $permissions = $this->getPermissions();
        
        if (in_array('*', $permissions)) {
            return true;
        }
        
        foreach ($permissions as $perm) {
            if ($perm === $permission) {
                return true;
            }
            
            if (str_ends_with($perm, '.*')) {
                $prefix = substr($perm, 0, -2);
                if (str_starts_with($permission, $prefix . '.')) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->generateAuthKey();
            }
            return true;
        }
        return false;
    }

    /**
     * Generate auth key
     */
    private function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString(32);
    }
}