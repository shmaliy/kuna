<?php

use yii\db\Migration;

/**
 * Class m180204_180333_create_tbl_param
 */
class m180204_180333_create_tbl_param extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%param}}', [
            'id' => $this->primaryKey(11),
            'userId' => 'int(11) not null',
            'name' => 'varchar(255) not null',
            'value' => 'varchar(255) not null',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%param}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180204_180333_create_tbl_param cannot be reverted.\n";

        return false;
    }
    */
}
