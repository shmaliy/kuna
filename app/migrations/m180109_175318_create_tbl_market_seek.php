<?php

use yii\db\Migration;

/**
 * Class m180109_175318_create_tbl_market_seek
 */
class m180109_175318_create_tbl_market_seek extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%market_seek}}', [
            'id' => $this->primaryKey(11),
            'timestamp' => 'bigint(30) not null',
            'market' => "enum('btcuah', 'ethuah', 'wavesuah', 'gbguah', 'bchuah') not null default 'ethuah'",
            'at' => 'bigint(30) not null',
            'buy' => 'float not null',
            'sell' => 'float not null',
            'low' => 'float not null',
            'high' => 'float not null',
            'last' => 'float not null',
            'vol' => 'float not null',
            'price' => 'float not null',
            'created' => 'timestamp not null default CURRENT_TIMESTAMP',
            'day' => 'date',
            'time' => 'time'
        ]);
        
        $this->createIndex('idx-market_seek-timestamp', '{{%market_seek}}', 'timestamp');
        $this->createIndex('idx-market_seek-market', '{{%market_seek}}', 'market');
        $this->createIndex('idx-market_seek-at', '{{%market_seek}}', 'at');
        $this->createIndex('idx-market_seek-buy', '{{%market_seek}}', 'buy');
        $this->createIndex('idx-market_seek-sell', '{{%market_seek}}', 'sell');
        $this->createIndex('idx-market_seek-low', '{{%market_seek}}', 'low');
        $this->createIndex('idx-market_seek-high', '{{%market_seek}}', 'high');
        $this->createIndex('idx-market_seek-created', '{{%market_seek}}', 'created');
        $this->createIndex('idx-market_seek-day', '{{%market_seek}}', 'day');
        $this->createIndex('idx-market_seek-time', '{{%market_seek}}', 'time');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropIndex('idx-market_seek-timestamp', '{{%market_seek}}');
        $this->dropIndex('idx-market_seek-market', '{{%market_seek}}');
        $this->dropIndex('idx-market_seek-at', '{{%market_seek}}');
        $this->dropIndex('idx-market_seek-buy', '{{%market_seek}}');
        $this->dropIndex('idx-market_seek-sell', '{{%market_seek}}');
        $this->dropIndex('idx-market_seek-low', '{{%market_seek}}');
        $this->dropIndex('idx-market_seek-high', '{{%market_seek}}');
        $this->dropIndex('idx-market_seek-created', '{{%market_seek}}');
        $this->dropIndex('idx-market_seek-day', '{{%market_seek}}');
        $this->dropIndex('idx-market_seek-time', '{{%market_seek}}');
    
        $this->dropTable('{{%market_seek}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180109_175318_create_tbl_market_seek cannot be reverted.\n";

        return false;
    }
    */
}
