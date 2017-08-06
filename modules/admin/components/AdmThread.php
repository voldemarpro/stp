<?php
namespace app\modules\admin\components;


class AdmThread extends \app\components\Thread
{
	/**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%adm_threads}}';
    }
}
