<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%market_seek}}".
 *
 * @property int $id
 * @property int $timestamp
 * @property string $market
 * @property int $at
 * @property double $buy
 * @property double $sell
 * @property double $low
 * @property double $high
 * @property double $last
 * @property double $vol
 * @property double $price
 * @property string $created
 * @property string $day
 * @property string $time
 */
class MarketSeek extends \yii\db\ActiveRecord
{
    const MARKET_btcuah = 'btcuah';
    const MARKET_ethuah = 'ethuah';
    const MARKET_wavesuah = 'wavesuah';
    const MARKET_gbguah = 'gbguah';
    const MARKET_bchuah = 'bchuah';
    
    public static $_markets = [
        self::MARKET_btcuah,
        self::MARKET_ethuah,
        self::MARKET_wavesuah,
        self::MARKET_gbguah,
        self::MARKET_bchuah,
    ];
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%market_seek}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['at', 'buy', 'sell', 'low', 'high', 'last', 'vol', 'price'], 'required'],
            [['timestamp', 'at'], 'integer'],
            [['market'], 'string'],
            [['buy', 'sell', 'low', 'high', 'last', 'vol', 'price'], 'number'],
            [['created', 'day', 'time'], 'safe'],
    
            [['timestamp'], 'default', 'value' => time()],
            [['market'], 'default', 'value' => self::MARKET_ethuah],
            [['market'], 'in', 'range' => self::$_markets],
            [['created'], 'default', 'value' => date("Y-m-d H:i:s")],
            [['day'], 'default', 'value' => date("Y-m-d")],
            [['time'], 'default', 'value' => date("H:i:s")],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'timestamp' => 'Timestamp',
            'market' => 'Market',
            'at' => 'At',
            'buy' => 'Buy',
            'sell' => 'Sell',
            'low' => 'Low',
            'high' => 'High',
            'last' => 'Last',
            'vol' => 'Vol',
            'price' => 'Price',
            'created' => 'Created',
            'day' => 'Day',
            'time' => 'Time',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\query\MarketSeekQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\MarketSeekQuery(get_called_class());
    }
    
    public static function seek($market)
    {
        $row = new self;
        $row->market = $market;
        $data = Yii::$app->cuna->getTickerData($market);
        if (!$data) return;
//        var_export($data);
        $row->at = $data->at;
        $row->attributes = (array) $data->ticker;
        if ($row->validate()) {
            $row->save();
//            var_export($row->getAttributes());
//            echo "\n";
        } else {
//            var_export($row->getErrors());
//            echo "\n";
        }
    }
    
    public function getCharts()
    {
        $list = self::find()
            ->where('market = :m', [':m' => $this->market])
            ->orderBy('day desc')
            ->all();
        $ret = [];
        /** @var MarketSeek $row */
        foreach ($list as $row) {
            $ret[$row->day][] = $row->getAttributes();
        }
        
        return $ret;
    }
}
