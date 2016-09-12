<?php

namespace app\models;

use yii\db\ActiveRecord;

class User extends ActiveRecord
{
    
    /**
     * @return string название таблицы, сопоставленной с этим ActiveRecord-классом.
     */
    public static function tableName()
    {
        return 'a_users';
    }
}
