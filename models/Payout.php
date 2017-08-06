<?php
namespace app\models;

use Yii;
use yii\db\Command;
use yii\db\ActiveRecord;

/**
 * Платежи и переводы
 */
class Payout extends ActiveRecord
{	
	public static $types = [
		'SOTACARD',
		'Торговый счет',
		'Банковская карта',
		'Телефон'
	];
	
	/**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%payouts}}';
    }
	

	public function attributeLabels() {
		return [
			'sum'	  =>  'Сумма',
			'type' 	  =>  'Счет-получатель',
			'date' 	  =>  'Дата',
			'user_id' =>  'Трейдер'
		];
	}
	
	public function attributeFormats() {
		return [
			'type'  => 18
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