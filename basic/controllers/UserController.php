<?php

namespace app\controllers;

use yii\rest\ActiveController;

class UserController extends ActiveController
{
	
    public $modelClass = 'app\models\User';
    
    public function actionFoo() {
    var_dump('ura');
		//return $this->render('')
    }
}