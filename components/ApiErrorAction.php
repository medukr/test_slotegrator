<?php
/**
 * Created by andrii
 * Date: 08.09.19
 * Time: 15:47
 */

namespace app\components;
use yii\web\ErrorAction;

class ApiErrorAction extends ErrorAction
{

   public function run(){
       return ['error' => $this->exception->getMessage()];
   }
}