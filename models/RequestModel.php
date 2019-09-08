<?php
/**
 * Created by andrii
 * Date: 07.09.19
 * Time: 21:05
 */

namespace app\models;


use yii\base\Model;

class RequestModel extends Model
{
    public $id;
    public $user;
    public $secret;


    public function rules()
    {
        return [
//            [$this->required_params, 'required'],
            [['user'], 'string', 'max' => 255],
            [['id'], 'string', 'length' => 32],
            [['secret'], 'string', 'length' => 40]
        ];
    }


}