<?php
namespace app\models;

use Yii;
use yii\db\Command;
use yii\db\ActiveRecord;

/**
 * Обращения за поддержкой
 */
class SupportTicket extends ActiveRecord
{	
	/**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%tickets}}';
    }
	

	public function attributeLabels() {
		return [
			'response' => 'Ответ',
			'message' => 'Сообщение'
		];
	}

	public function attributeFormats() {
		return [];
	}

	public function scenarios()
    {
        return [
            'default' =>  \array_slice(\array_keys($this->tableSchema->columns), 1),
			'update' => ['message', 'response']
        ];
    }
	
    public function getTrader()
    {
        return $this->hasOne(Trader::className(), ['id' => 'user_id']);
    }
}
?>