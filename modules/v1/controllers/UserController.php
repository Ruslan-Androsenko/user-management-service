<?php

namespace app\modules\v1\controllers;

use Yii;
use yii\rest\ActiveController;
use app\modules\v1\models\User;


class UserController extends ActiveController
{
    public $modelClass = 'app\modules\v1\models\User';

}
