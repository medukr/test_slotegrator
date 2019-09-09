<?php

use yii\db\Migration;

/**
 * Class m190909_073454_alter_user_name_column_from_user_table
 */
class m190909_073454_alter_user_name_column_from_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('user', 'user_name', $this->string()->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('user', 'user_name', $this->string()->notNull()->unique());
    }

}
