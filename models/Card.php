<?php

namespace app\models;

use yii\db\ActiveRecord;

class Card extends ActiveRecord
{
	public function getLocation()
    {
        return $this->hasOne(Location::className(), ['idLocation' => 'idLocation']);
    }
    
// 	public function getTag()
//     {
//         return $this->hasMany(Tag::className(), ['idTag' => 'idTag']);
//         ->viaTable(CardTag::tableName(), ['resource_id' => 'id']); 
//     }
    
    /**
     * @return string название таблицы, сопоставленной с этим ActiveRecord-классом.
     */
    public static function tableName()
    {
        return "{{%card}}" ;
    }
    
}
