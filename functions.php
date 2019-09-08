<?php

use yii\web\Response;

/**
 * Created by andrii
 * Date: 07.09.19
 * Time: 22:26
 */

function debug($mixed, $var_damp = false, $die = true){

    Yii::$app->response->format = Response::FORMAT_HTML;

    echo '<pre>';
    !$var_damp
        ? print_r($mixed)
        : var_dump($mixed);
    echo '<pre>';

    if ($die) die;
}