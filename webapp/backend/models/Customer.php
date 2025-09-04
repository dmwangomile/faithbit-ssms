<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Customer model
 *
 * @property integer $id
 * @property string $customer_number
 * @property string $type
 * @property string $first_name
 * @property string $last_name
 * @property string $company_name
 * @property string $email
 * @property string $phone
 * @property string $phone2
 * @property string $date_of_birth
 * @property string $gender
 * @property string $preferred_language
 * @property string $address
 * @property string $city
 * @property string $region
 * @property string $postal_code
 * @property string $country
 * @property string $tax_number
 * @property float $credit_limit
 * @property integer $credit_terms
 * @property integer $loyalty_points
 * @property float $total_purchases
 * @property string $last_purchase_date
 * @property boolean $is_active
 * @property string $notes
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * 
 * @property SalesOrder[] $salesOrders
 * @property ServiceWorkOrder[] $workOrders
 * @property PaymentTransaction[] $payments
 */
class Customer extends ActiveRecord
{
    const TYPE_INDIVIDUAL = 'individual';
    const TYPE_BUSINESS = 'business';
    
    const GENDER_MALE = 'male';
    const GENDER_FEMALE = 'female';
    const GENDER_OTHER = 'other';
    
    const LANGUAGE_ENGLISH = 'en';
    const LANGUAGE_SWAHILI = 'sw';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%customers}}';
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
            [['customer_number'], 'required'],
            ['customer_number', 'unique'],
            ['customer_number', 'string', 'max' => 20],
            ['type', 'in', 'range' => [self::TYPE_INDIVIDUAL, self::TYPE_BUSINESS]],
            ['type', 'default', 'value' => self::TYPE_INDIVIDUAL],
            [['first_name', 'last_name'], 'string', 'max' => 100],
            ['company_name', 'string', 'max' => 200],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            [['phone', 'phone2'], 'string', 'max' => 20],
            ['date_of_birth', 'date', 'format' => 'php:Y-m-d'],
            ['gender', 'in', 'range' => [self::GENDER_MALE, self::GENDER_FEMALE, self::GENDER_OTHER]],
            ['preferred_language', 'in', 'range' => [self::LANGUAGE_ENGLISH, self::LANGUAGE_SWAHILI]],
            ['preferred_language', 'default', 'value' => self::LANGUAGE_ENGLISH],
            [['address', 'notes'], 'string'],
            [['city', 'region', 'country'], 'string', 'max' => 100],
            ['postal_code', 'string', 'max' => 20],
            ['country', 'default', 'value' => 'Tanzania'],
            ['tax_number', 'string', 'max' => 50],
            [['credit_limit', 'total_purchases'], 'number', 'min' => 0],
            [['credit_limit', 'total_purchases'], 'default', 'value' => 0.00],
            ['credit_terms', 'integer', 'min' => 0],
            ['credit_terms', 'default', 'value' => 0],
            ['loyalty_points', 'integer', 'min' => 0],
            ['loyalty_points', 'default', 'value' => 0],
            ['last_purchase_date', 'date', 'format' => 'php:Y-m-d'],
            ['is_active', 'boolean'],
            ['is_active', 'default', 'value' => true],
            
            // Conditional validations
            [['first_name', 'last_name'], 'required', 'when' => function($model) {
                return $model->type === self::TYPE_INDIVIDUAL;
            }],
            ['company_name', 'required', 'when' => function($model) {
                return $model->type === self::TYPE_BUSINESS;
            }],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_number' => 'Customer Number',
            'type' => 'Type',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'company_name' => 'Company Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'phone2' => 'Phone 2',
            'date_of_birth' => 'Date of Birth',
            'gender' => 'Gender',
            'preferred_language' => 'Preferred Language',
            'address' => 'Address',
            'city' => 'City',
            'region' => 'Region',
            'postal_code' => 'Postal Code',
            'country' => 'Country',
            'tax_number' => 'Tax Number',
            'credit_limit' => 'Credit Limit',
            'credit_terms' => 'Credit Terms (days)',
            'loyalty_points' => 'Loyalty Points',
            'total_purchases' => 'Total Purchases',
            'last_purchase_date' => 'Last Purchase Date',
            'is_active' => 'Active',
            'notes' => 'Notes',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Get sales orders relation
     */
    public function getSalesOrders()
    {
        return $this->hasMany(SalesOrder::class, ['customer_id' => 'id']);
    }

    /**
     * Get work orders relation
     */
    public function getWorkOrders()
    {
        return $this->hasMany(ServiceWorkOrder::class, ['customer_id' => 'id']);
    }

    /**
     * Get payments relation
     */
    public function getPayments()
    {
        return $this->hasMany(PaymentTransaction::class, ['customer_id' => 'id']);
    }

    /**
     * Get full name
     */
    public function getFullName()
    {
        if ($this->type === self::TYPE_BUSINESS) {
            return $this->company_name;
        }
        
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get display name
     */
    public function getDisplayName()
    {
        $name = $this->getFullName();
        return $name ?: $this->customer_number;
    }

    /**
     * Add loyalty points
     */
    public function addLoyaltyPoints($points)
    {
        $this->loyalty_points += $points;
        return $this->save(false);
    }

    /**
     * Redeem loyalty points
     */
    public function redeemLoyaltyPoints($points)
    {
        if ($this->loyalty_points < $points) {
            return false;
        }
        
        $this->loyalty_points -= $points;
        return $this->save(false);
    }

    /**
     * Update purchase stats
     */
    public function updatePurchaseStats($amount)
    {
        $this->total_purchases += $amount;
        $this->last_purchase_date = date('Y-m-d');
        return $this->save(false);
    }

    /**
     * Check credit limit
     */
    public function canPurchaseOnCredit($amount)
    {
        if ($this->credit_limit <= 0) {
            return false;
        }
        
        $currentBalance = $this->getCurrentCreditBalance();
        return ($currentBalance + $amount) <= $this->credit_limit;
    }

    /**
     * Get current credit balance
     */
    public function getCurrentCreditBalance()
    {
        // Calculate outstanding invoices/orders
        $outstanding = SalesOrder::find()
            ->where(['customer_id' => $this->id])
            ->andWhere(['!=', 'status', 'completed'])
            ->sum('total_amount');
            
        return (float) $outstanding;
    }

    /**
     * Search customers
     */
    public static function search($query, $limit = 50)
    {
        return static::find()
            ->where(['is_active' => true])
            ->andWhere([
                'or',
                ['like', 'first_name', $query],
                ['like', 'last_name', $query],
                ['like', 'company_name', $query],
                ['like', 'customer_number', $query],
                ['like', 'email', $query],
                ['like', 'phone', $query],
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
            if ($insert && !$this->customer_number) {
                $this->customer_number = $this->generateCustomerNumber();
            }
            
            return true;
        }
        return false;
    }

    /**
     * Generate customer number
     */
    private function generateCustomerNumber()
    {
        $prefix = 'C';
        $date = date('ym'); // Year and month
        
        // Find the next sequential number for this month
        $lastCustomer = static::find()
            ->where(['like', 'customer_number', $prefix . $date])
            ->orderBy('customer_number DESC')
            ->one();
            
        if ($lastCustomer) {
            $lastNumber = (int) substr($lastCustomer->customer_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . $date . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}