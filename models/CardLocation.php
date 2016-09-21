<?php

namespace app\models;

use yii\db\ActiveRecord;

class Cardlocation extends ActiveRecord
{
	//public function getCard()
    //{
    //    return $this->hasMany(Card::className(), ['idCardStack' => 'idCardStack']);
    //}
    
    /**
     * @return string название таблицы, сопоставленной с этим ActiveRecord-классом.
     */
    public static function tableName()
    {
        return "{{%cardLocation}}" ;
    }
    
}