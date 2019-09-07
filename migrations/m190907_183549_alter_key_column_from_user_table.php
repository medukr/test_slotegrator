<?php

use yii\db\Migration;

/**
 * Class m190907_183549_alter_key_column_from_user_table
 */
class m190907_183549_alter_key_column_from_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('user', 'key', $this->string(32)->notNull()->unique());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('user','key', $this->string(32)->notNull());
    }

}
