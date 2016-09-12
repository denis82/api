<?php

namespace app\models;

use yii\db\ActiveRecord;

class Location extends ActiveRecord
{
    
    /**
     * @return string название таблицы, сопоставленной с этим ActiveRecord-классом.
     */
    public static function tableName()
    {
        return 'l_location';
    }
    
    public function fields()
    {
        return [
            'latitude',
            'lo'=> function () {
            return $this->latitude . ' ' . $this->date;
            },
        ];
    }
}