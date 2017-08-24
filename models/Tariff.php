<?php
namespace app\models;

use Yii;
use yii\db\Command;
use yii\db\ActiveRecord;

/**
 * Торговый тариф
 */
class Tariff extends ActiveRecord
{	
	/**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%tariffs}}';
    }
	

	public function attributeLabels() {
		return [
			'name'  => 'Название',
			'terms' => 'Правила'
		];
	}

	public function attributeFormats() {
		return [];
	}

	public function scenarios()
    {
        return [
            'default' =>  \array_slice(\array_keys($this->tableSchema->columns), 1),
        ];
    }
	
    public function getTrader()
    {
        return $this->hasOne(Trader::className(), ['tariff_id' => 'id']);
    }
}
?>