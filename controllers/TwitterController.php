<?php
/**
 * Created by andrii
 * Date: 07.09.19
 * Time: 13:20
 */

namespace app\controllers;


use app\components\ApiException;
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


    /**
     * @param array $params
     * @param array $request
     * @return RequestModel
     * @throws ApiException
     */
    public function getValidRequest(array $params, array $request)
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


    /**
     * @return |null
     * @throws ApiException
     */
    public function actionAdd()
    {

        $request = $this->getValidRequest(['id', 'user', 'secret'], \Yii::$app->request->get());

        $this->validateSecret($request->id, $request->user, $request->secret);

        $user = new User();

        //Загрузка данных из запроса в модель пользователя, проверка уникальности id
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


    /**
     * @return array
     * @throws ApiException
     */
    public function actionFeed()
    {
        $request = $this->getValidRequest(['id', 'secret'], \Yii::$app->request->get());

        $user = $this->findUser($request->id);

        //Ппроверяем соответсвие секретного ключа в запросе к сохраненному пользователю
        $this->validateSecret($user->key, $user->user_name, $request->secret);

        $responseTwitter = $this->getTwitter()->getUserTimeline($user->user_name);

        if (is_array($responseTwitter)) {
            $responseModel = new ResponseModel($responseTwitter);
            return $responseModel->getFormattedResponse();
        }
        throw new ApiException(500, 'Internal error');

    }


    /**
     * @throws ApiException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionRemove()
    {
        $request = $this->getValidRequest(['id', 'user', 'secret'], \Yii::$app->request->get());

        //проверяем правильность секретного ключа в запросе
        $this->validateSecret($request->id, $request->user, $request->secret);

        $user = $this->findUser($request->id);

        //Ппроверяем соответсвие секретного ключа в запросе к сохраненному пользователю
        $this->validateSecret($user->key, $user->user_name, $request->secret);

        //Исключаем шанс коллизии
        if ($user->user_name == $request->user) {

            if ($user->delete()) {
                return;
            }
            throw new ApiException(500, 'Internal error');
        }
        throw new ApiException(403, 'Access denied');

    }


    /**
     * @param string $id
     * @param string $user
     * @param string $secret
     * @return bool
     * @throws ApiException
     */
    public function validateSecret(string $id, string $user, string $secret): bool
    {

        if ($this->generateSecret($id, $user) === $secret) return true;
        throw new ApiException(403, 'Access denied');

    }


    /**
     * @param $id
     * @param $user
     * @return string
     */
    public function generateSecret($id, $user)
    {
        return sha1($id . $user);
    }


    /**
     * @param $user_name
     * @return string
     */
    private function generateExampleAddRequestString($user_name)
    {
        return http_build_query([
            'id' => md5($user_name),
            'user' => $user_name,
            'secret' => $this->generateSecret(md5($user_name), $user_name)
        ]);
    }


    /**
     * @param $key
     * @return array|\yii\db\ActiveRecord|null
     * @throws ApiException
     */
    protected function findUser($key)
    {
        if (($model = User::find()->where('`key` = :key', [':key' => $key])->one()) !== null) {
            return $model;
        }

        throw new ApiException(404, 'The requested user does not exist.');
    }


    /**
     * @return Twitter
     */
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