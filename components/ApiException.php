<?php
/**
 * Created by andrii
 * Date: 08.09.19
 * Time: 17:58
 */

namespace app\components;


use yii\base\ExitException;

class ApiException extends ExitException
{
    public function __construct($status = 0, $message = null, $code = 0, \Exception $previous = null)
    {
        $response = \Yii::$app->getResponse();
        $response->data = ['error' => $message];

        parent::__construct($status, $message, $code, $previous);
    }
}