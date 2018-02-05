<?php

use yii\db\Migration;

/**
 * Class m180204_175927_add_admin
 */
class m180204_175927_add_admin extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->insert('{{%user}}', [
            'email' => 'shmaliy.maxim@gmail.com',
            'firstname' => 'Maxim',
            'lastname' => 'Shmaliy',
            'password' => \app\models\User::hashPassword(ADM_PWD),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->delete('{{%user}}', 'email = :e', [':e' => 'shmaliy.maxim@gmail.com']);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180204_175927_add_admin cannot be reverted.\n";

        return false;
    }
    */
}
