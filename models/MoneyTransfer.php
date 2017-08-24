<?php
namespace app\models;

use Yii;
use yii\db\Command;
use yii\db\ActiveRecord;

/**
 * Платежи и переводы
 */
class MoneyTransfer extends ActiveRecord
{	
	public static $grades = [
		'Клиринг по сделке',
		'Монетизация',
		'Бонус'
	];
	
	public static $recipients = [
		'Лицевой счет',
		'Торговый счет',
		'Банковская карта',
		'Телефон'
	];
	
	/**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%transfers}}';
    }
	

	public function attributeLabels() {
		return [
			'sum'	    =>  'Сумма',
			'rec_type'  =>  'Счет-получатель',
			'grade'     =>  'Предмет перевода',
			'date_time' =>  'Дата и время',
			'user_id'   =>  'Трейдер'
		];
	}
	
	public function attributeFormats() {
		return [
			'rec_type'  => 11,
			'grade'     => 19
		];
	}
	
	public function scenarios()
    {
        return [
            'default' =>  \array_slice(\array_keys($this->tableSchema->columns), 1)
        ];
    }
}
?>