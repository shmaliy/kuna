<?php

use yii\db\Migration;

/**
 * Class m180201_130124_create_table_iterations
 */
class m180201_130124_create_table_iterations extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%iterations}}', [
            'id' => $this->primaryKey(11),
            'localTs' => 'int(30) not null',
            'serverTs' => 'int(30) not null',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%iterations}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180201_130124_create_table_iterations cannot be reverted.\n";

        return false;
    }
    */
}
