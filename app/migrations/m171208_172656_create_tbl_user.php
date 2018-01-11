<?php

use yii\db\Migration;

/**
 * Class m171208_172656_create_tbl_user
 */
class m171208_172656_create_tbl_user extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
		$this->createTable('{{%user}}', [
			'id' => $this->primaryKey(11),
			'email' => 'varchar(50) not null',
			'password' => 'varchar(32) null',
			'status' => 'int(2) default 1',
			'firstname' => 'varchar(32) not null',
			'lastname' => 'varchar(32) not null',
			'role' => 'int(2) default 1',
			'lastLogin' => 'datetime',
			'created' => 'timestamp not null default CURRENT_TIMESTAMP',
			'updated' => 'datetime',
		]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%user}}');
    }
}
