<?php

namespace app\controllers;

use yii\rest\ActiveController;

class UserController extends ActiveController
{
	
    public $modelClass = 'app\models\User';
    
    public function actionView($id)
    {
        
        return User::findOne($id); 
    }
}