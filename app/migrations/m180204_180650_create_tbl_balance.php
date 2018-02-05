<?php

use yii\db\Migration;

/**
 * Class m180204_180650_create_tbl_balance
 */
class m180204_180650_create_tbl_balance extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%balance}}', [
            'id' => $this->primaryKey('11'),
            'userId' => 'int(11) not null',
            'balance' => 'float default 0',
            'created' => 'timestamp not null default CURRENT_TIMESTAMP'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%balance}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180204_180650_create_tbl_balance cannot be reverted.\n";

        return false;
    }
    */
}
