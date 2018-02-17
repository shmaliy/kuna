<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%param}}".
 *
 * @property int $id
 * @property int $userId
 * @property string $name
 * @property string $value
 */
class Param extends \yii\db\ActiveRecord
{
    const P_iterationTimeout = 'iterationTimeout';
    const P_minSellLevel = 'minSellLevel';
    const P_sellLevel = 'sellLevel';
    const P_market = 'market';
    const P_baseCurrency = 'baseCurrency';
    const P_quotCurrency = 'quotCurrency';
    const P_trading = 'trading';
    const P_buyNow = 'buyNow';
    const P_buy = 'buy';
    const P_wait = 'wait';
    const P_sell = 'sell';
    const P_sellNow = 'sellNow';
    const P_sellOrderNow = 'sellOrderNow';
    const P_buyOrderNow = 'buyOrderNow';
    const P_buyOrderLifetime = 'buyOrderLifetime';
    const P_sellOrderLifetime = 'sellOrderLifetime';
    
    public static $_formList = [
        self::P_iterationTimeout,
        self::P_minSellLevel,
        self::P_sellLevel,
        self::P_market,
        self::P_baseCurrency,
        self::P_quotCurrency,
        self::P_buyNow,
        self::P_buy,
        self::P_wait,
        self::P_sell,
        self::P_sellNow,
        self::P_buyOrderLifetime,
        self::P_sellOrderLifetime,
    ];
    
    public static $_defaults = [
        self::P_iterationTimeout => '12',
        self::P_minSellLevel => '490',
        self::P_sellLevel => '500',
        self::P_market => 'ethuah',
        self::P_baseCurrency => 'eth',
        self::P_quotCurrency => 'uah',
        self::P_trading => '1',
        self::P_buyNow => '15',
        self::P_buy => '35',
        self::P_wait => '65',
        self::P_sell => '80',
        self::P_sellNow => '100',
        self::P_sellOrderNow => '0',
        self::P_buyOrderNow => '0',
        self::P_buyOrderLifetime => '600',
        self::P_sellOrderLifetime => '600',
    ];
    
    public static $_names = [
        self::P_iterationTimeout => 'Iteration Timeout (s)',
        self::P_minSellLevel => 'Minimum Sell Level (+)',
        self::P_sellLevel => 'Base Sell Level (+)',
        self::P_market => 'Market',
        self::P_baseCurrency => 'Base Currency',
        self::P_quotCurrency => 'Quot Currency',
        self::P_buyNow => 'Max BuyNow Level (%)',
        self::P_buy => 'Max Buy Level (%)',
        self::P_wait => 'Max Wait Level (%)',
        self::P_sell => 'Max Sell Level (%)',
        self::P_sellNow => 'Max SellNow Level (%)',
        self::P_buyOrderLifetime => 'Buy Order Lifetime (s)',
        self::P_sellOrderLifetime => 'Sell Order Lifetime (s)',
    ];
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%param}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userId', 'name', 'value'], 'required'],
            [['userId'], 'integer'],
            [['name', 'value'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'userId' => 'User ID',
            'name' => 'Name',
            'value' => 'Value',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\query\ParamQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\ParamQuery(get_called_class());
    }
    
    public function getParams()
    {
        $params = self::find()
            ->where('userId = :i', [':i' => $this->userId])
            ->all();
        
        $ret = [];
        
        foreach (self::$_defaults as $key=>$val) {
            $exist = false;
            /** @var Param $param */
            foreach ($params as $param) {
                if ($param->name == $key) {
                    $ret[] = $param->getAttributes();
                    $exist = true;
                    break;
                }
            }
            if ($exist) continue;
            
            $new = new self;
            $new->userId = $this->userId;
            $new->name = $key;
            $new->value = $val;
            if ($new->save()) $ret[] = $new->getAttributes();
            
        }
        return $ret;
    }
}
