<?php

namespace app\controllers;

use Yii;
use yii\BaseYii;
use yii\db\Query;
use app\models\Tag;
use app\models\Card;
use app\models\CardTag;
use app\models\Tagkind;
use yii\rest\Controller;
use app\models\Location;
use yii\db\ActiveRecord;
use app\models\Cardstack;
use app\models\CardLocation;



class ApiController extends Controller
{
	public $topCardId = 'topCardId';
	public $idCardStack = 'cardStackIds';
	public $cardIds = 'cardIds';
	public $tagIds = 'tagIds';
	public $locationIds = 'locationIds';
	public $datas = [];
	const DATAS = 'datas';  
	
    public function actionCardstack()
    {   
		$request = Yii::$app->request;
		$idArray = $request->post('ids');
        $CardStack = [];
        $arrayId = [];
        $temp = [];
        if(is_array($idArray)) {
			foreach($idArray as $id) {  // формируется строка из условия для  запроса
				if(intval($id)) {
					$arrayId[] = intval($id); // в массив попадут только int
					$temp[] = "`cardStackIds` = ".intval($id)."";
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
							if ($resStack->cardStackIds == $id) {
									$tempcart[$this->topCardId] =  $resStack->topCardId;
							}
						}
						//$tempcart[$this->idCardStack] = $id; // идентификатор текущего стека
						$tempcart['sort'] = 30; //  маркер сортировки
						if ($id == $res->cardStackIds) {
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
        $aliasTable = '';
        $oldIds = '';
        $ChangeStringForQuery = '';
        $count = 1;
        if(is_array($idArray)) {
			foreach($idArray as $ids) { 
				if ( $aliasTable == '') {
					$aliasTable = 'alias';
				}
				if (isset($idArray[$count])) {
				$ChangeStringForQuery .= ' INNER JOIN 
					(SELECT cardIds FROM l_cardTag 
						WHERE tagIds IN ('.implode(',',$idArray[$count]) .')) as `'.$aliasTable.$count.'` ON '.$aliasTable.$oldIds.'.cardIds = '.$aliasTable.$count.'.cardIds';
				$oldIds = $count;
				}		
				$aliasTable = 'alias';					
				$count++;
			}		
			$query ="SELECT cardStackIds FROM 
					(SELECT alias.cardIds FROM l_cardTag as `alias`	
					".$ChangeStringForQuery."
					WHERE alias.tagIds IN (".implode(',',$idArray[array_keys($idArray)[0]]) .")) as `res` 
					INNER JOIN   `l_card` as `c` ON res.cardIds = c.cardIds 
					GROUP BY cardStackIds";
			$resPost = Yii::$app->db->createCommand($query)->queryAll();
			foreach ($resPost as $res) {
				foreach($res as $r) {
					$CardStack[] = $r;
				}
			}	
		} else {
			$CardStack = [];
        }
		$this->datas[self::DATAS] = $CardStack;
        var_dump($query);die;
       //return $this->datas; 
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
		if (null != $idArray) {
			foreach ($idArray as $key => $id) {  // в цикле валидируются входные данные
				if (!is_array($id)) {
					if((int)$id) {
						$temp[] =(int)$id;
					}
				}
			}
        }
		if(!empty($temp)) {
			$query = 'SELECT c.date, c.name,c.image,c.info, c.cardIds, cl.locationIds, ct.tagIds FROM `l_card`  as  `c`
						INNER JOIN `l_cardLocation` as `cl` ON c.cardIds = cl.cardIds
						INNER JOIN `l_cardTag` as `ct` ON c.cardIds = ct.cardIds 
						WHERE c.cardIds IN ('.implode(' , ', $temp).')';
			$resQuery = Yii::$app->db->createCommand($query)->queryAll();
			
			foreach($temp as $id) {  // формируется вывод из результата запроса
				$tempcart = [];
				foreach ($resQuery as $res) {
					if ( $res[$this->cardIds] == $id) {
						if(isset($tempcart[$this->tagIds])) {
							if (!in_array($res['tagIds'],$tempcart[$this->tagIds])) {
								$tempcart[$this->tagIds][] = 	$res['tagIds'];
							}
						} else {
							$tempcart[$this->tagIds][] = 	$res['tagIds'];
						}
						
						if(isset($tempcart[$this->locationIds])) {
							if (!in_array($res['locationIds'],$tempcart[$this->locationIds])) {
								$tempcart[$this->locationIds][] = 	$res['locationIds'];
							}
						} else {
							$tempcart[$this->locationIds][] = 	$res['locationIds'];
						}
					}
					if($res[$this->cardIds] == $id) {
						$tempcart['date'] = strtotime($res['date']);
						$tempcart['image'] = $res['image'];
						$tempcart['name']  = $res['name'];
						$tempcart['info'] = $res['info'];
						$tempcart['sort'] = 30;
					}
				}
				$tempcart['id'] = $id;
					$CardStack[] = $tempcart;
			} 	
        } else {
			$CardStack = [];
        }
		$this->datas[self::DATAS] = $CardStack;
        return $this->datas; 
    }
    
    public function actionLocation()
    {
		$request = Yii::$app->request;
		$idArray = $request->post('id');
		$CardStack = [];
		$temp = '';
		//var_dump($idArray);
		if (null != $idArray and 1 == count($idArray)) {
			foreach ($idArray as $id) {  // в цикле валидируются входные данные
				$temp =(int)$id;
			}
			$locationInstance = new Location();
			$resQuery = $locationInstance::findOne($temp);
			$i = 0;
			foreach ($resQuery as $key =>$res) {
					$CardStack[$key] = $res;
			} 
			$CardStack['sort'] = 30;
        } else  {
			$CardStack = [];
        }

		//$locationList = [];
		
		//var_dump($CardStack);die;   
		$this->datas[self::DATAS][] = $CardStack;
        return $this->datas; 
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
    /*
    
    
    */
    public function checkArray($array) 
    {
		$res = true;
		if(is_array($array)) {
			foreach ($array as $firstKey => $firstStep) {
				if (!is_numeric($firstKey)  ) {
					$res = false;
					}	
				if(is_array($firstStep)) {	
					foreach($firstStep as $secondKey => $secondStep) {
						
						if (!is_numeric($secondStep) || !is_numeric($secondKey) || is_array($secondStep) ) {
						$res = false;
						}
					}
				} else {
					$res = false;
				}
			}
		} else {
			$res = false;
		}
		return  $res;
    }
}