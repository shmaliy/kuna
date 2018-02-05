<?php

use yii\db\Migration;

/**
 * Class m180123_093230_create_table_order_log
 */
class m180123_093230_create_table_order_log extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%active_orders}}', [
            'id' => $this->primaryKey(25),
            'side' => 'enum("buy", "sell")',
            'ord_type' => 'enum("limit", "market")',
            'price' => 'float',
            'avg_price' => 'float',
            'state' => 'varchar(50)',
            'market' => "enum('btcuah', 'ethuah', 'wavesuah', 'gbguah', 'bchuah') not null default 'ethuah'",
            'created_at' => 'varchar(50)',
            'volume' => 'float',
            'remaining_volume' => 'float',
            'executed_volume' => 'float',
            'trades_count' => 'float',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%active_orders}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180123_093230_create_table_order_log cannot be reverted.\n";

        return false;
    }
    */
}
