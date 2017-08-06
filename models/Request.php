<?php
namespace app\models;

use Yii;
use yii\db\Command;
use yii\db\ActiveRecord;

/**
 * Заявки от трейдеров
 */
class Request extends ActiveRecord
{	
	public static $types = [
		'Увеличение счета',
		'Перевод средств'
	];
	
	public static $statusArray = [
		-1 => 'Отклонена',
		 0 => 'Новая',
		 1 => 'Одобрена'
	];
	
	/**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%requests}}';
    }
	

	public function attributeLabels() {
		return [
			'date_time'=> 'Дата и время',
			'user_id'  => 'Пользователь',
			'amount'   => 'Сумма',
			'status'   => 'Статус',
			'comment'  => 'Комментарий'
		];
	}	
	
	public function attributeFormats() {
		return [
			'date_time'=> 7,
			'status'   => 18,
			'comment'  => 2
		];
	}
	
	public function rules() {
		return [
			['comment', 'string', 'max'=>50]
		];
	}

	public function scenarios()
    {
        return [
            'default' =>  \array_slice(\array_keys($this->tableSchema->columns), 1),
			'update'  =>  ['comment', 'status']
        ];
    }
	
    public function getTraider()
    {
        return $this->hasOne(Traider::className(), ['id' => 'user_id']);
    }

}
?>