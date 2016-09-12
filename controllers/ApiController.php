<?php

namespace app\controllers;

use app\models\Tag;
use app\models\Card;
use app\models\Location;
use app\models\Tagkind;
use app\models\Cardstack;
use yii\rest\Controller;

class ApiController extends Controller
{
	
    public function actionCardstack($id = 1)
    {
		$customer = new Cardstack();
       $arr = $customer::find()->where(['idCardStack' => $id])->one();
        return $arr; 
    }
        
    /*
    *
    * $id = [optional]
    */

    public function actionCard($id = 1)
    {
		$CardStack = [];
		$cardInstance = new Card();
        $resQuery = $cardInstance::find()->where(['idCardStack' => $id])->all();
        $i = 0;
		foreach ($resQuery as $res) {
			foreach ($res as $key => $r) {
				$CardStack[$i][$key] = $r;
				//var_dump($key.'=>'.$r);
				//echo '<br>';
			}
		$i++;
		} 
        return $CardStack; 
    }
    
    public function actionLocation($id = 1)
    {
		$locationList = [];
		$cardInstance = new Card();
        $resQuery = $cardInstance::findOne($id);
        $i = 0;
		foreach ($resQuery->location as $key => $res) {
			//foreach ($res as $key => $r) {
				$locationList[$i][$key] = $res;
			//}
		$i++;
		} 
//          
        return $locationList; 
    }
    
    /*
    *
    * $id = [optional]
    */
    public function actionTag($id = 4)
    {
		$tagList = [];
		$tagInstance = new Tag();
        $resQuery = $tagInstance::findOne($id);
         $i = 0;
 		foreach ($resQuery->card as $key => $res) {
 			//foreach ($res as $key => $r) {
 				$tagList[$i][$key] = $res;
 			//}
 		$i++;
 		} 
          
        return $tagList; 
    }
    
    /*
    *
    * $id = [optional]
    */
    public function actionTagkind($id = 1)
    {
		$tagKindList = [];
		$tagInstance = new Tag();
        $resQuery = $tagInstance::find()->all();
         $i = 0;
 		foreach ($resQuery as $key => $res) {
 			//foreach ($res as $key => $r) {
 				$tagList[$i][$key] = $res;
 			//}
 		$i++;
 		} 
          
        return $tagList; 
    }
}