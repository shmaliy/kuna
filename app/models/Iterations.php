<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%iterations}}".
 *
 * @property int $id
 * @property int $userId
 * @property int $localTs
 * @property int $serverTs
 * @property string $me
 * @property double $priceDiff
 * @property double $quotCurrencyBalance
 * @property double $mainCurrencyBalance
 * @property string $orders
 * @property string $trades
 * @property string $ticker
 * @property string $targetAccounts
 * @property int $tradeRecommendation
 */
class Iterations extends \yii\db\ActiveRecord
{
    
    public static $_jsonFields = [
        'me', 'orders', 'trades', 'ticker', 'targetAccounts'
    ];
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%iterations}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userId', 'localTs', 'serverTs'], 'required'],
            [['userId', 'localTs', 'serverTs', 'tradeRecommendation'], 'integer'],
            [['me', 'orders', 'trades', 'ticker', 'targetAccounts'], 'string'],
            [['priceDiff', 'quotCurrencyBalance', 'mainCurrencyBalance'], 'number'],
            [['userId'], 'exist', 'targetClass' => User::className(), 'targetAttribute' => 'id'],
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
            'localTs' => 'Local Ts',
            'serverTs' => 'Server Ts',
            'me' => 'Me',
            'priceDiff' => 'Price Diff',
            'quotCurrencyBalance' => 'Quot Currency Balance',
            'mainCurrencyBalance' => 'Main Currency Balance',
            'orders' => 'Orders',
            'trades' => 'Trades',
            'ticker' => 'Ticker',
            'targetAccounts' => 'Target Accounts',
            'tradeRecommendation' => 'Trade Recommendation',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\query\IterationsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\IterationsQuery(get_called_class());
    }
    
    public static function getRow($userId)
    {
        $row = self::find()
            ->where('userId = :i', [':i' => $userId])
            ->orderBy('id desc')->one();
        if (is_null($row)) {
            $row = new self;
            $row->localTs = time();
            $row->serverTs = time();
            $row->userId = $userId;
            $row->save();
        }
        
        return $row;
    }
    
    public static function register($serverTs, $userId, $columns)
    {
        $row = self::getRow($userId);
        $row->localTs = time();
        $row->serverTs = $serverTs;
        $row->attributes = $columns;
        $row->save();
    }
    
    public function getIteration()
    {
        $iteration = self::find()
            ->where('userId = :i', [':i' => $this->userId])
            ->orderBy('localTs')
            ->one();
        
        if (is_null($iteration)) return [];
        
        $iteration = $iteration->getAttributes();
        
        foreach (self::$_jsonFields as $f) {
            if (!empty($iteration[$f])) {
                $iteration[$f] = json_decode($iteration[$f], true);
            }
        }
        return $iteration;
        
    }
}
