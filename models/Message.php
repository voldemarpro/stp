<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Message extends ActiveRecord
{
	/**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%messages}}';
    }
	
	public function attributeLabels() {
		return [
			'text' => 'Текст сообщения',
			'date_time' => 'Дата и время'
		];
	}
	
	public function attributeFormats() {
		return [
			'text' => 3
		];
	}

	public function scenarios() {
        return [
            'default' =>  \array_slice(\array_keys($this->tableSchema->columns), 1, null)
        ];
	}
}
?>