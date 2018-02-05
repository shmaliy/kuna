<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%trade_history}}".
 *
 * @property int $id
 * @property double $price
 * @property double $volume
 * @property double $funds
 * @property string $market
 * @property string $created_at
 * @property string $side
 */
class TradeHistory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%trade_history}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['price', 'volume', 'funds'], 'number'],
            [['market', 'side'], 'string'],
            [['created_at'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'price' => 'Price',
            'volume' => 'Volume',
            'funds' => 'Funds',
            'market' => 'Market',
            'created_at' => 'Created At',
            'side' => 'Side',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\query\TradeHistoryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\TradeHistoryQuery(get_called_class());
    }
}
