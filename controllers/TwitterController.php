<?php
/**
 * Created by andrii
 * Date: 07.09.19
 * Time: 13:20
 */

namespace app\controllers;


use app\components\ApiException;
use app\models\RequiredParamsModel;
use app\models\RequestModel;
use app\models\ResponseModel;
use app\models\Twitter;
use app\models\User;
use yii\filters\VerbFilter;
use yii\rest\ActiveController;
use yii\web\Response;

class TwitterController extends ActiveController
{

    public $modelClass = 'app\models\User';

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


    public function actions()
    {
        return [
            'error' => [
                'class' => 'app\components\ApiErrorAction',
            ],
        ];
    }


    public function getValidRequest(array $params,array $request)
    {
        foreach ($params as $value) {
            if (!isset($request[$value])) throw new ApiException(400, 'Missing parameter');
        }

        $requestModel = new RequestModel();

        //Валидация входящих параметров
        if ($requestModel->load($request, '') && $requestModel->validate()) {

            return $requestModel;
        }

        throw new ApiException(403, 'Access denied');

    }


    public function actionAdd()
    {

        $request = $this->getValidRequest(['id', 'user', 'secret'], \Yii::$app->request->get());

        //Валидация секретного ключа
        if ($this->generateSecret($request->id, $request->user) == $request->secret) {

            $user = new User();

            //Загрузка данных из запроса в модель пользователя, проверка уникальности полльзователя и id
            if ($user->loadData($request)) {

                //Проверка существования пользователя в твиттере
                $response = $this->getTwitter()->getUsersLookup($user->user_name);
                if (is_array($response) && isset($response[0]->screen_name) && $response[0]->screen_name == $request->user) {

                    //Попытка сохранить нового пользователся
                    if ($user->save()) {
                        return null;

                    }
                    throw new ApiException(500, 'Internal error');
                }
                throw new ApiException(400, 'Wrong user name');
            }
            throw new ApiException(404, 'User already exist');
        }
        throw new ApiException(403, 'Access denied');


        //            return 'id=' . md5('jeffbezos') . '&user=jeffbezos&secret=' . sha1(md5('jeffbezos') . 'jeffbezos');

    }


    public function actionFeed()
    {

        $request = $this->getValidRequest(['id', 'secret'], \Yii::$app->request->get());

        $user = $this->findUser($request->id);

        if ($this->generateSecret($user->key, $user->user_name) == $request->secret) {
            $responseTwitter = $this->getTwitter()->getUserTimeline($user->user_name);

            if (is_array($responseTwitter)) {
                $responseModel = new ResponseModel($responseTwitter);
                return $responseModel->getFormattedResponse();
            }
            throw new ApiException(500, 'Internal error');
        }
        throw new ApiException(403, 'Access denied');


    }


    public function actionRemove()
    {
        $request = $this->getValidRequest(['id', 'user', 'secret'], \Yii::$app->request->get());

        if ($this->generateSecret($request->id, $request->user) == $request->secret) {
            $user = $this->findUser($request->id);

            if ($this->generateSecret($user->key, $user->user_name) == $request->secret && $user->user_name == $request->user) {

                if ($user->delete()) {
                    return;
                }
                throw new ApiException(500, 'Internal error');
            }
        }
        throw new ApiException(403, 'Access denied');
    }


    public function generateSecret($id, $user)
    {
        return sha1($id . $user);
    }


    protected function findUser($key)
    {
        if (($model = User::find()->where('`key` = :key', [':key' => $key])->one()) !== null) {
            return $model;
        }

        throw new ApiException(404, 'The requested user does not exist.');
    }


    protected function getTwitter()
    {

        $twitter = new Twitter(
            \Yii::$app->params['auth_twitter']['consumer_key'],
            \Yii::$app->params['auth_twitter']['consumer_secret'],
            \Yii::$app->params['auth_twitter']['oauth_token'],
            \Yii::$app->params['auth_twitter']['oauth_secret']);

        return $twitter;
    }

}