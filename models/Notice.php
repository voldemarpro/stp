<?php
namespace app\models;

use Yii;
use yii\db\Command;
use yii\db\ActiveRecord;

class Notice extends ActiveRecord
{
	/**
     * @var array recipients of the message associated with current record
     */	
	public $Traders = [];
	
	/**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%notices}}';
    }
	
	public function attributeLabels() {
		return [
			'message' => 'Сообщение',
			'date_time' => 'Дата и время',
			'auto' => 'Автообновление'
		];
	}
	
	public function attributeFormats() {
		return [
			'message' => 3,
			'auto' => 13
		];
	}

	public function scenarios() {
        return [
            'default' =>  \array_slice(\array_keys($this->tableSchema->columns), 1, null)
        ];
	}
	
    /**
     * Query to get all Traders for whom the current notice is created
     */		
	public function getTraders()
    {
       return $this->hasMany(Trader::className(), ['id'=>'user_id'])
				->viaTable('{{%user_notices}}', ['notice_id'=>'id']);
    }
}
?>