<?php
/**
 * Created by andrii
 * Date: 07.09.19
 * Time: 13:20
 */

namespace app\controllers;


use yii\filters\VerbFilter;
use yii\rest\ActiveController;
use yii\web\Response;

class TwitterController extends ActiveController
{

    public $modelClass = '';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_JSON;
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'add' => ['get'],
                'feed' => ['get'],
                'remove' => ['get'],
            ]
        ];
        return $behaviors;
    }


    public function actionAdd(){

    }

    public function actionFeed(){

    }

    public function actionRemove(){

    }
}