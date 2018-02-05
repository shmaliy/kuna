<?php

use yii\db\Migration;

/**
 * Class m180123_100406_create_table_trade_history
 */
class m180123_100406_create_table_trade_history extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%trade_history}}', [
            'id' => $this->primaryKey(25),
            'price' => 'float',
            'volume' => 'float',
            'funds' => 'float',
            'market' => "enum('btcuah', 'ethuah', 'wavesuah', 'gbguah', 'bchuah') not null default 'ethuah'",
            'created_at' => 'varchar(50)',
            'side' => 'enum("bid", "ask")',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%trade_history}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180123_100406_create_table_trade_history cannot be reverted.\n";

        return false;
    }
    */
}
