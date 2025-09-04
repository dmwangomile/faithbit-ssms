<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Branch model
 *
 * @property integer $id
 * @property string $code
 * @property string $name
 * @property string $type
 * @property string $address
 * @property string $city
 * @property string $region
 * @property string $country
 * @property string $phone
 * @property string $email
 * @property integer $manager_id
 * @property boolean $is_active
 * @property string $created_at
 * @property string $updated_at
 * 
 * @property User $manager
 * @property Warehouse[] $warehouses
 * @property User[] $users
 */
class Branch extends ActiveRecord
{
    const TYPE_RETAIL_SHOP = 'retail_shop';
    const TYPE_WAREHOUSE = 'warehouse';
    const TYPE_SERVICE_CENTER = 'service_center';
    const TYPE_HEAD_OFFICE = 'head_office';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%branches}}';
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
            [['code', 'name', 'type'], 'required'],
            ['code', 'unique'],
            ['code', 'string', 'max' => 10],
            ['name', 'string', 'max' => 100],
            ['type', 'in', 'range' => [
                self::TYPE_RETAIL_SHOP,
                self::TYPE_WAREHOUSE,
                self::TYPE_SERVICE_CENTER,
                self::TYPE_HEAD_OFFICE
            ]],
            [['address'], 'string'],
            [['city', 'region', 'country'], 'string', 'max' => 100],
            ['country', 'default', 'value' => 'Tanzania'],
            ['phone', 'string', 'max' => 20],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['manager_id', 'integer'],
            ['manager_id', 'exist', 'targetClass' => User::class, 'targetAttribute' => 'id'],
            ['is_active', 'boolean'],
            ['is_active', 'default', 'value' => true],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'name' => 'Name',
            'type' => 'Type',
            'address' => 'Address',
            'city' => 'City',
            'region' => 'Region',
            'country' => 'Country',
            'phone' => 'Phone',
            'email' => 'Email',
            'manager_id' => 'Manager',
            'is_active' => 'Active',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Get manager relation
     */
    public function getManager()
    {
        return $this->hasOne(User::class, ['id' => 'manager_id']);
    }

    /**
     * Get warehouses relation
     */
    public function getWarehouses()
    {
        return $this->hasMany(Warehouse::class, ['branch_id' => 'id']);
    }

    /**
     * Get users relation
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['branch_id' => 'id']);
    }

    /**
     * Get active branches
     */
    public static function getActive()
    {
        return static::find()->where(['is_active' => true])->all();
    }

    /**
     * Get branches by type
     */
    public static function getByType($type)
    {
        return static::find()->where(['type' => $type, 'is_active' => true])->all();
    }
}