<?php

use yii\db\Migration;

/**
 * Class m180204_202118_add_columns_to_iteration
 */
class m180204_202118_add_columns_to_iteration extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%iterations}}', 'userId', 'int(11) not null after id');
        $this->addColumn('{{%iterations}}', 'me', 'longtext');
        $this->addColumn('{{%iterations}}', 'priceDiff', 'float');
        $this->addColumn('{{%iterations}}', 'quotCurrencyBalance', 'float');
        $this->addColumn('{{%iterations}}', 'mainCurrencyBalance', 'float');
        $this->addColumn('{{%iterations}}', 'orders', 'longtext');
        $this->addColumn('{{%iterations}}', 'trades', 'longtext');
        $this->addColumn('{{%iterations}}', 'ticker', 'longtext');
        $this->addColumn('{{%iterations}}', 'targetAccounts', 'longtext');
        $this->addColumn('{{%iterations}}', 'tradeRecommendation', 'int(1)');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%iterations}}', 'userId');
        $this->dropColumn('{{%iterations}}', 'me');
        $this->dropColumn('{{%iterations}}', 'priceDiff');
        $this->dropColumn('{{%iterations}}', 'quotCurrencyBalance');
        $this->dropColumn('{{%iterations}}', 'mainCurrencyBalance');
        $this->dropColumn('{{%iterations}}', 'orders');
        $this->dropColumn('{{%iterations}}', 'trades');
        $this->dropColumn('{{%iterations}}', 'ticker');
        $this->dropColumn('{{%iterations}}', 'targetAccounts');
        $this->dropColumn('{{%iterations}}', 'tradeRecommendation');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180204_202118_add_columns_to_iteration cannot be reverted.\n";

        return false;
    }
    */
}
