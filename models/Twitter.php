<?php
/**
 * Created by andrii
 * Date: 07.09.19
 * Time: 19:44
 */

namespace app\models;


use Abraham\TwitterOAuth\TwitterOAuth;
use yii\web\HttpException;

class Twitter
{

    private $connection;

    public function __construct($consumer_key, $consumer_secret, $oauth_token, $oauth_secret)
    {

        if (!$this->connection = new TwitterOAuth(
            \Yii::$app->params['auth_twitter']['consumer_key'],
            \Yii::$app->params['auth_twitter']['consumer_secret'],
            \Yii::$app->params['auth_twitter']['oauth_token'],
            \Yii::$app->params['auth_twitter']['oauth_secret']));



    }

    public function getUserTimeline($user, $params = []){

        return $this->connection->get("statuses/user_timeline", array_merge(['screen_name' => $user], $params));
    }


    public function getUsersLookup($user,$params = []){

        return $this->connection->get("users/lookup", array_merge(['screen_name' => $user], $params));
    }

    public function post($path, array $parameters = [], $json = false){

        return $this->connection->post($path, $parameters, $json);

    }

    public function get($path, array $parameters = []){

        return $this->connection->get($path,$parameters);

    }

    public function accountVerifyCredentials(){
        return $this->connection->get("account/verify_credentials");
    }

    public function getConnection(){
        return $this->connection;
    }

}