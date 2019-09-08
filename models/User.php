<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $user_name
 * @property string $key
 */
class User extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_name', 'key'], 'required'],
            [['user_name', 'key'], 'unique'],
            [['user_name'], 'string', 'max' => 255],
            [['key'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_name' => 'User Name',
            'key' => 'Key',
        ];
    }

    public function loadData(RequestModel $request){

        $this->key = $request->id;
        $this->user_name = $request->user;

        return $this->validate();
    }

    public function validateSecret($secret){
        return sha1($this->key . $this->user_name) == $secret;
    }
}
