<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%balance}}".
 *
 * @property int $id
 * @property int $userId
 * @property double $balance
 * @property string $created
 */
class Balance extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%balance}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userId'], 'required'],
            [['userId'], 'integer'],
            [['balance'], 'number'],
            [['created'], 'safe'],
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
            'balance' => 'Balance',
            'created' => 'Created',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\query\BalanceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\BalanceQuery(get_called_class());
    }
}
