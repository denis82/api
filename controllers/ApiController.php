<?php

namespace app\controllers;

use Yii;
use yii\db\Query;
use app\models\Tag;
use app\models\Card;
use app\models\CardTag;
use app\models\Tagkind;
use yii\rest\Controller;
use app\models\Location;
use app\models\Cardstack;
use yii\db\ActiveRecord;


class ApiController extends Controller
{
	public $topCardId = 'topCardId';
	public $idCardStack = 'idCardStack';
	public $cardIds = 'cardIds';
	public $datas = [];
	const DATAS = 'datas';  
	
    public function actionCardstack()
    {   
		$request = Yii::$app->request;
		$idArray = $request->post('ids');
        $CardStack = [];
        $arrayId = [];
        $temp = [];
        $finArray = [];
        if(is_array($idArray)) {
			foreach($idArray as $id) {  // формируется строка из условия для  запроса
				if(intval($id)) {
					$arrayId[] = intval($id); // в массив попадут только int
					$temp[] = "`idCardStack` = ".intval($id)."";
				}
			}
			if (!empty($temp)) {
				$cardStackInstance = new CardStack();	
				$resStackQuery = $cardStackInstance::find()->where(implode(' or ', $temp))->all();
				$cardInstance = new Card();
				$resQuery = $cardInstance::find()->where(implode(' or ', $temp))->all();
				foreach ($arrayId as $id) {
					$tempcart = [];
					foreach ($resQuery as $res) {
						foreach ($resStackQuery as $resStack) {
							if ($resStack->idCardStack == $id) {
									$tempcart[$this->topCardId] =  $resStack->topCardId;
							}
						}
						//$tempcart[$this->idCardStack] = $id; // идентификатор текущего стека
						$tempcart['sort'] = 30; //  маркер сортировки
						if ($id == $res->idCardStack) {
							$tempcart[$this->cardIds][] = $res->cardIds;
						}
					}
					$tempcart['id'] = $id;
					$CardStack[] = $tempcart;
				} 		
			} else {				
				$CardStack = [];
			}	
        } else {
			$CardStack = [];
        }
		$this->datas[self::DATAS] = $CardStack;
        //var_dump($CardStack);die;
        return $this->datas; 
    }
        public function actionCardstacksearch()
    {   
		$request = Yii::$app->request;
		$idArray = $request->post('tags');
        $CardStack = [];
        $arrayId = [];
        $temp = [];
        $finArray = [];
        $fish = array(
					"0"=>array('Lenin','Trockiy','Tolstoy'),
					"1"=>array('dom','dom2','dom3'),
					"2"=>array('forest','field'),
					"3"=>array('park'));
        $string = [];
        $longString = [];
        foreach ($fish as $f) {
        $string = [];
			foreach ($f as $det) {
				$string[] =  $det;
			}
			$longString[] = '('.implode( ' || ', $string).')'; 
        }
        $implodeString = '('.implode( ' && ', $longString).')';
        
        /*
        if(is_array($idArray)) {
			foreach($idArray as $id) {  // формируется строка из условия для  запроса
				if(intval($id)) {
					$arrayId[] = intval($id); // в массив попадут только int
					$temp[] = "`idCardStack` = ".intval($id)."";
				}
			}
			if (!empty($temp)) {
				$cardStackInstance = new CardStack();	
				$resStackQuery = $cardStackInstance::find()->where(implode(' or ', $temp))->all();
				$cardInstance = new Card();
				$resQuery = $cardInstance::find()->where(implode(' or ', $temp))->all();
				foreach ($arrayId as $id) {
					$tempcart = [];
					foreach ($resQuery as $res) {
						foreach ($resStackQuery as $resStack) {
							if ($resStack->idCardStack == $id) {
									$tempcart[$this->topCardId] =  $resStack->topCardId;
							}
						}
						//$tempcart[$this->idCardStack] = $id; // идентификатор текущего стека
						$tempcart['sort'] = 30; //  маркер сортировки
						if ($id == $res->idCardStack) {
							$tempcart[$this->cardIds][] = $res->cardIds;
						}
					}
					$tempcart['id'] = $id;
					$CardStack[] = $tempcart;
				} 		
			} else {				
				$CardStack = [];
			}	
        } else {
			$CardStack = [];
        }
		$this->datas[self::DATAS] = $CardStack;*/
        var_dump($implodeString);die;
       // return $this->datas; 
    }
    /*
    *
    * $id = [optional]
    */

    public function actionCard()
    {
		$request = Yii::$app->request;
		$idArray = $request->post('ids');
		$CardStack = [];
        $temp = [];
        
         if(is_array($idArray)) {
			foreach($idArray as $id) {  // формируется строка из условия для  запроса
				if(intval($id)) {
					$arrayId[] = intval($id); // в массив попадут только int
					$temp[] = "`cardIds` = ".intval($id)."";
				}
			}
			$cardInstance = new Card();
			$resQuery = $cardInstance::find()->where(implode(' or ', $temp))->all();
			foreach ($arrayId as $id) {
				$tempcart = [];
				foreach ($resQuery as $res) {
					/*foreach ($resStackQuery as $resStack) {
						if ($resStack->idCardStack == $id) {
								$tempcart[$this->topCardId] =  $resStack->topCardId;
						}
					}*/
					//$tempcart[$this->idCardStack] = $id; // идентификатор текущего стека
					$tempcart['sort'] = 30; //  маркер сортировки
					if ($id == $res->idCardStack) {
						$tempcart[$this->cardIds][] = $res->locationsIds;
					}
				}
				$tempcart['id'] = $id;
				$CardStack[] = $tempcart;
			} 
			
        }
        //var_dump($resQuery);
        
       /* $i = 0;
		foreach ($resQuery as $res) {
			foreach ($res as $key => $r) {
				$CardStack[$i][$key] = $r;
				//var_dump($key.'=>'.$r);
				//echo '<br>';
			}
		$i++;
		}*/
		var_dump($CardStack);die;
        //return implode(' or ', $temp); 
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
    public function actionAdd()
    {
		$arr = [];
		$newarr = [];
		for ($k=1;$k < 100000; $k++) {
		$arr[] = $k;
		}
		shuffle($arr);
		//$tag = new Card();
		$tag = new Card();
		$resQuery = $tag::find()->all();
		foreach ($resQuery as  $res) {
 			//foreach ($res as  $key =>$r) {
 			//$tag = new CardStack();
 			//$tag->idCardStack = $res;
 			//$tag->save();
 			$newarr[] = $res['cardIds'];
 				///echo '<pre>';var_dump($res['cardIds']);echo '</pre>';
 			//}
		//for ($k=1;$k < 100; $k++) {
		//	for ($i = 100+$k; $i <100000; $i = $i+100) {
			
		//	$tag->idTag = $i;
		//	$tag->idTagkind = $k;
			//$tag->save();
			
		}
		//var_dump($newarr);die;
		$tag = new CardTag();
		$resQuer = $tag::find()->all();
		
			foreach ($resQuer as $res) {
				$new = $tag::findOne($res['idCardTag']);
				$new->cardIds = array_pop ( $newarr );
				$new->save();
				
				//$new->idCardStack = array_pop ( $newarr );
				//$new->save();
			}
			
    }
}