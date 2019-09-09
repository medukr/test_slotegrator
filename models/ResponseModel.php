<?php
/**
 * Created by andrii
 * Date: 08.09.19
 * Time: 12:37
 */

namespace app\models;


class ResponseModel
{


    private $_response;

    public function __construct($response)
    {
        $this->_response = $response;
    }

    public function getFormattedResponse (){

            $arr = [];
            foreach ($this->_response as $key => $value)
                $arr['feed'][] = [
                    'name' => $value->user->name,
                    'tweet' => $value->text,
                    'hashtag' => $value->entities->hashtags
                ];
            return $arr;


    }


}