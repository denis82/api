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
	
	
	public $id = 'id';
	public $sort = 30;
	public $date = 'date';
	public $name = 'name';
	public $info = 'info';
	public $image = 'image';
	public $tagIds = 'tagIds';
	public $active = 'active';
	public $cardIds = 'cardIds';
	public $latitude = 'latitude';
	public $findName = 'findName';
	public $topCardId = 'topCardId';
	public $longitude = 'longitude';
	public $tagKindIds = 'tagkindIds';
	public $locationIds = 'locationIds';
	public $idCardStack = 'cardStackIds';
	public $datas = [];
	
        
	const IDS = 'ids';
	const VERSION = 1;
	const RADIUS = 0.5;
	const TAGS = 'tags';
	const DATAS = 'datas';
	const RAD = 'distance';
	const LAT = 'latitude';
	const LON = 'longitude';
	const LENGTHPACKAGE = 1000;
	const DATEINFO = 'DateInfo';
	const TAGKIND = 'tagKindIds';
        
	
	
    public function actionCardstack()
    {   
		$request = Yii::$app->request;
		$idArray = $request->post(self::IDS);
        $CardStack = [];
        $arrayId = [];
        $temp = [];
		$temp = $this->simpleArray($idArray);
		if (!empty($temp)) {
		
			foreach($temp as $id) {  // формируется строка из условия для  запроса
					$arrayId[] = "`cardStackIds` = ".intval($id)."";
			}
			$cardStackInstance = new CardStack();	
			$resStackQuery = $cardStackInstance::find()->where(implode(' or ', $arrayId))->all();
			$cardInstance = new Card();
			$resQuery = $cardInstance::find()->where(implode(' or ', $arrayId))->all();
			foreach ($temp as $id) {
				$tempcart = [];
				foreach ($resQuery as $res) {
					foreach ($resStackQuery as $resStack) {
						if ($resStack->cardStackIds == $id) {
								$tempcart[$this->topCardId] =  $resStack->topCardId;
						}
					}
					//$tempcart[$this->idCardStack] = $id; // идентификатор текущего стека
					$tempcart['sort'] = $this->sort; //  маркер сортировки
					if ($id == $res->cardStackIds) {
						$tempcart[$this->cardIds][] = $res->cardIds;
					}
				}
				$tempcart[$this->id] = $id;
				$CardStack[] = $tempcart;
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
		$idArray = $request->post(self::TAGS);
        $CardStack = [];
        $aliasTable = '';
        $oldIds = '';
        $ChangeStringForQuery = '';
        $count = 1;
        if($this->checkArray($idArray)) {
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
        //var_dump($query);die;
        return $this->datas; 
    }
    /*
    *
    * $id = [optional]
    */

    public function actionCard()
    {
		$request = Yii::$app->request;
		$idArray = $request->post(self::IDS);
		$CardStack = [];
		$temp = [];
		$temp = $this->simpleArray($idArray);
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
							if (!in_array($res[$this->tagIds],$tempcart[$this->tagIds])) {
								$tempcart[$this->tagIds][] = 	$res[$this->tagIds];
							}
						} else {
							$tempcart[$this->tagIds][] = 	$res[$this->tagIds];
						}
						
						if(isset($tempcart[$this->locationIds])) {
							if (!in_array($res[$this->locationIds],$tempcart[$this->locationIds])) {
								$tempcart[$this->locationIds][] = 	$res[$this->locationIds];
							}
						} else {
							$tempcart[$this->locationIds][] = 	$res[$this->locationIds];
						}
						$tempcart[$this->date] = strtotime($res[$this->date]);
						$tempcart[$this->image] = $res[$this->image];
						$tempcart[$this->name]  = $res[$this->name];
						$tempcart[$this->info] = $res[$this->info];
						$tempcart['sort'] = $this->sort;
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
		$idArray = $request->post(self::IDS);
		$CardStack = [];
		$temp = [];
		$temp = $this->simpleArray($idArray);
		if(!empty($temp)) {
			$query = 'SELECT date, locationIds as `id` ,latitude,longitude, active
						FROM  `l_location` as `c`
						WHERE locationIds IN ('.implode(' , ', $temp).')';
			$resQuery = Yii::$app->db->createCommand($query)->queryAll();
			foreach ($temp as $id) {
			
				$tempcart = [];
				foreach ($resQuery as $res) {
					if ($id == $res['id']) {
						$tempcart['id'] = $res['id'];
						$tempcart[$this->date] = strtotime($res[$this->date]);
						($res[$this->active]) ? $tempcart[$this->active] = true : $tempcart[$this->active] = false;
						$tempcart[$this->latitude] = $res[$this->latitude]; 
						$tempcart[$this->longitude] = $res[$this->latitude];
						$tempcart['sort'] = $this->sort;	
					}	
				}
				$CardStack[] = $tempcart;
			}
		}	else {
			$CardStack = [];
        }	
		//var_dump($resQuery);die;   
		$this->datas[self::DATAS] = $CardStack;
        return $this->datas; 
    }
    
    /*
    *
    * $id = [optional]
    */
    public function actionTag()
    {
		$request = Yii::$app->request;
		$idArray = $request->post(self::IDS);
		$tagArray = $request->post(self::TAGKIND);
		$stringForQuery = '';
		$CardStack = [];
		$tagTemp = [];
		$idsTemp = [];
		$tagTemp = $this->simpleArray($tagArray);
		$idsTemp = $this->simpleArray($idArray);
		
		if (empty($tagTemp) and  !empty($idsTemp)) {
			$stringForQuery = 'WHERE tagIds IN ('.implode(' , ', $idsTemp).')';
		}
		if (!empty($tagTemp) and  empty($idsTemp)) {
			$stringForQuery = 'WHERE tagKindIds IN ('.implode(' , ', $tagTemp).')';
		}
		if (!empty($tagTemp) and  !empty($idsTemp)) {
			$stringForQuery = 'WHERE tagIds IN ('.implode(' , ', $idsTemp).') AND tagKindIds IN ('.implode(' , ', $tagTemp).')';
		}
		if (!empty($stringForQuery) ) {
			$query = 'SELECT tagIds as `id`, date, findName, name, tagkindIds as `tagkindId`
						FROM  `l_tag`'.$stringForQuery; 
			$resQuery = Yii::$app->db->createCommand($query)->queryAll();
			foreach ($resQuery as $id) {
				$tempcart = [];
				foreach ($resQuery as $res) {
					if ($id['id'] == $res['id']) {
						$tempcart['id'] = $res['id'];
						$tempcart[$this->date] = strtotime($res[$this->date]);
						$tempcart['tagkindId'] = $res['tagkindId'];
						$tempcart[$this->name] = $res[$this->name]; 
						$tempcart[$this->findName] = $res[$this->findName];
					}	
				}
				$CardStack[] = $tempcart; 
			}
		} else {
			$CardStack = [];
		}
		//var_dump($query);die;   
		$this->datas[self::DATAS] = $CardStack;
        return $this->datas; 
    }
    
    /*
    *
    * $id = [optional]
    */
    public function actionTagkind()
    {
		$request = Yii::$app->request;
		$idArray = $request->post(self::IDS);
		$CardStack = [];
		$temp = [];
		$temp = $this->simpleArray($idArray);
		if(!empty($temp)) {
			$query = 'SELECT t.tagIds,tk.date, tk.name, tk.tagkindIds as `tagkindIds` FROM `l_tagKind` as `tk`
						INNER JOIN `l_tag` as `t`  ON t.tagKindIds =tk.tagKindIds 
						WHERE  tk.tagKindIds IN ('.implode(' , ', $temp).')';
			$resQuery = Yii::$app->db->createCommand($query)->queryAll();
			foreach($temp as $id) {  // формируется вывод из результата запроса
				$tempcart = [];
				foreach ($resQuery as $res) {
					if ( $res[$this->tagKindIds] == $id) {
						if(isset($tempcart[$this->tagIds])) {
							if (!in_array($res[$this->tagIds],$tempcart[$this->tagIds])) {
								$tempcart[$this->tagIds][] = 	$res[$this->tagIds];
							}
						} else {
							$tempcart[$this->tagIds][] = 	$res[$this->tagIds];
						}
						$tempcart[$this->date] = strtotime($res[$this->date]);
						$tempcart[$this->name]  = $res[$this->name];
						$tempcart['sort'] = $this->sort;
						$tempcart['id'] = $id;
					}
				}
				$CardStack[] = $tempcart;
			} 
		}	else {
			$CardStack = [];
        }		
		//var_dump($resQuery);die;   
		$this->datas[self::DATAS] = $CardStack;
        return $this->datas; 
    }
    
    public function actionLocationsearch()
    {  
        $request = Yii::$app->request;
        $latitude = $request->post(self::LAT);
        $longitude = $request->post(self::LON);
        $radius = $request->post(self::RAD);
        $CardStack = [];
        false == (int)$radius ? $radius = self::RADIUS : $radius = ((int)$radius)/1000;
        if($radius and floatval($longitude) and floatval($latitude)) {
					
			$query ='SELECT locationIds, ( 6371 * acos( cos( radians('.$latitude.') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('.$longitude.') ) + sin( radians('.$latitude.') ) * sin( radians( latitude ) ) ) ) AS distance FROM l_location  HAVING distance < '.$radius.' ORDER BY distance  ';
			$resPost = Yii::$app->db->createCommand($query)->queryAll();
			foreach ($resPost as $res) {
					$CardStack[] = $res['locationIds'];
			}	
		} else {
			$CardStack = [];
        }
		$this->datas[self::DATAS] = $CardStack;
        //var_dump($query);die;
        return $this->datas; 
    }
    
    
    public function actionTagupdatetime()
    {  
        $CardStack = [];
		$query ='SELECT tagIds AS `id`,date FROM `l_tag` ORDER BY date DESC LIMIT 1';
		$resPost = Yii::$app->db->createCommand($query)->queryAll();
		if ($resPost) {
			$tempcart = [];
			foreach ($resPost as $res) {
					$tempcart['id'] = $res['id'];
					$tempcart['date'] = strtotime($res['date']);
					$tempcart['sort'] = 30;
			}
			$CardStack[] = $tempcart;
		} else {
			$CardStack = [];
        }
		$this->datas[self::DATEINFO] = $CardStack;
        return $this->datas; 
    }
    
    public function actionTagkindupdatetime()
    {  
        $CardStack = [];
		$query ='SELECT tagKindIds AS `id`,date FROM `l_tagKind` ORDER BY date DESC LIMIT 1';
		$resPost = Yii::$app->db->createCommand($query)->queryAll();
		if ($resPost) {
			$tempcart = [];
			foreach ($resPost as $res) {
					$tempcart['id'] = $res['id'];
					$tempcart['date'] = strtotime($res['date']);
					$tempcart['sort'] = 30;
			}
			$CardStack[] = $tempcart;
		} else {
			$CardStack = [];
        }
		$this->datas[self::DATEINFO] = $CardStack;
        return $this->datas; 
    }

    public function actionLastversion()
    {  
        $request = Yii::$app->request;
        $os = $request->post('os');
        $version = $request->post('version');
        $CardStack = [];
        $query ='SELECT active,os FROM `l_version`';
		$resPost = Yii::$app->db->createCommand($query)->queryAll();
        
        if ($resPost) {
            $tempcart = [];
            foreach ($resPost as $res) {
                if ($os == $res['os']) { 
                    ($version == $res['active']) ? $tempcart['needUpdate'] = false: $tempcart['needUpdate'] = true;
                    $tempcart['version'] = $res['active'];
                    $tempcart['sort'] = 30;
                }
            }
            $CardStack[] = $tempcart;
        } else {
            $CardStack = [];
        }
	$this->datas[self::DATEINFO] = $CardStack;
        return $this->datas; 
    }
    
    
    public function actionTagpackage()
    {  
        $request = Yii::$app->request;
        $packageIds = (int)$request->post('pack');
		if ($packageIds >= 0 ) {
			$query = 'SELECT tagIds as `id`, date, findName, name, tagkindIds as `tagkindId` FROM `l_tag`  ORDER BY  `tagIds` ASC LIMIT 1000  OFFSET '.(int)$packageIds.'000'; 
			$resQuery = Yii::$app->db->createCommand($query)->queryAll();
			if ($resQuery) {
				foreach ($resQuery as $id) {
					$tempcart = [];
					foreach ($resQuery as $res) {
						if ($id['id'] == $res['id']) {
							$tempcart['id'] = $res['id'];
							$tempcart[$this->date] = strtotime($res[$this->date]);
							$tempcart['tagkindId'] = $res['tagkindId'];
							$tempcart[$this->name] = $res[$this->name]; 
							$tempcart[$this->findName] = $res[$this->findName];
							$tempcart['sort'] = $this->sort;	
						}	
					}
					$CardStack[] = $tempcart; 
				}
			}
        } else {
            $CardStack = [];
        } 
        //var_dump($CardStack);
		$this->datas[self::DATAS] = $CardStack;
		$this->datas['count'] = $this->tagpackagecount();
        return $this->datas; 
    }
    
        
    public function actionAdd()
    {
		$arr = [];
		$newarr = [];
		for ($k=700000;$k < 900000; $k++) {
		$rand = rand ( 211111 , 299999 );
		$rand2 = rand ( 611111 , 699999 );
		$r = '('.$k.' , 56.'.$rand.' , 53.'.$rand2.')';
		$query ="INSERT INTO `l_location`(`locationIds`, `latitude`, `longitude`) VALUES".$r;
		//echo $query;
			//$resPost = Yii::$app->db->createCommand($query)->execute();
		//$arr[] = $k;
		}
		//shuffle($arr);
		//$tag = new Card();
		//$tag = new Card();
		//$resQuery = $tag::find()->all();
		//foreach ($resQuery as  $res) {
 
 		//	$newarr[] = $res['cardIds'];

			
		//}
		//var_dump($newarr);die;
		//$tag = new CardTag();
		//$resQuer = $tag::find()->all();
		
		//	foreach ($resQuer as $res) {
		//		$new = $tag::findOne($res['idCardTag']);
		//		$new->cardIds = array_pop ( $newarr );
				//$new->save();
				
				//$new->idCardStack = array_pop ( $newarr );
				//$new->save();
		//	}
			
    }
    
    public function tagpackagecount()
    {  
        $query ='SELECT count(tagIds) as `count` FROM `l_tag`';
		$resPost = Yii::$app->db->createCommand($query)->queryAll();
        $CardStack = '';
        if ($resPost) {
            foreach ($resPost as $res) {
				if($res['count'] >= self::LENGTHPACKAGE) {
					$CardStack = (int)round($res['count']/self::LENGTHPACKAGE);
				} else {
					$CardStack = false;
				}
            }
        } else {
            $CardStack = false;
        }
        return $CardStack; 
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
    
    public function simpleArray($array)
    {
		$temp = [];
		if (null != $array and is_array($array)) {
			foreach ($array as $key => $id) {  // в цикле валидируются входные данные
				if (!is_array($id)) {
					if((int)$id) {
						$temp[] =(int)$id;
					}
				}
			}
        } else {
			$temp = [];
        }
		return $temp;
    }
    

}