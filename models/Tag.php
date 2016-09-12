<?php

namespace app\models;

use yii\db\ActiveRecord;

class Tag extends ActiveRecord
{
	public function getCard()
    {
        return $this->hasMany(Card::className(), ['idCard' => 'idCard'])
        ->viaTable(CardTag::tableName(), ['idTag' => 'idTag']); 
    }

    
    /**
     * @return string название таблицы, сопоставленной с этим ActiveRecord-классом.
     */
    public static function tableName()
    {
        return "{{%tag}}" ;
    }
    
}