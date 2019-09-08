<?php
/**
 * Created by andrii
 * Date: 08.09.19
 * Time: 14:40
 */

namespace app\models;


class RequiredParamsModel extends RequestModel
{


    private $required_params;

    public function __construct(array $required_params = [],$config = [])
    {
        parent::__construct($config);

        $this->required_params = $required_params;

    }


    public function rules()
    {
        return [
            [$this->required_params, 'required']
        ];
    }


}