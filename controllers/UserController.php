<?php

namespace app\controllers;

use app\models\User;
use yii\rest\Controller;

class UserController extends Controller
{
	
   // public $modelClass = 'app\models\User';
    
    public function actionDemotest()
    {
		$customer = new User();
		//var_dump($customer::find()->all());
       $arr = $customer::find()->all();
       //die;
        return $arr; 
    }
}