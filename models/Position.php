<?php
namespace app\models;

use Yii;
use yii\db\Command;
use yii\db\ActiveRecord;

class Position extends ActiveRecord
{
	public static $types = [1 => 'Покупка', -1 => 'Продажа'];
	
	/**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%positions}}';
    }
	

	public function attributeLabels() {
		return [];
	}

	public function scenarios()
    {
        return [
            'default' => \array_slice(\array_keys($this->tableSchema->columns), 1)
        ];
    }
	
	public static function formatSum($sum) {
		return abs($sum) >= 100000
			? trim(
				\preg_replace('|^(\d{1,2})?(\d{3})?(\d{3})(\.\d{2,3})?$|', '$1 $2 $3$4', $sum)
			)
			: trim(
				\preg_replace('|^(\d{1,2})?(\d{3})(\.\d{2,3})?$|', '$1 $2 $3', $sum)
			);
	}
	public static function formatSign($sum) {
		$signPref = [-1 => '<em class="monosign">&ndash;</em>', 0 => '<em class="monosign">&nbsp;</em>', 1 => '<em class="monosign">+</em>'];
		return floatval($sum) ? $signPref[$sum/abs($sum)] : $signPref[0];
	}
	
	/**
     * @return array Validation rules
     */	
	public function rules() {
		return [
			[['open_quot', 'close_quot', 'result'], 'double'],
			[['disabled'], 'boolean'],
			
			[['open_time', 'open_quot', 'open_sum'], 'required', 'message' => 'Обязательное поле']
		];
	}
	
	public function attributeFormats() {
		return [
			'user_id'  => 18,
			'disabled'  => 13
		];
	}
}
?>