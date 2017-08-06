<?php
namespace app\models;

use Yii;
use yii\db\Command;
use yii\db\ActiveRecord;

class News extends ActiveRecord
{	
	/**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%news}}';
    }

	public function scenarios()
    {
        $default = \array_slice(\array_keys($this->tableSchema->columns), 1);
		
		// hash = md5 hash of the link to external source 
		$admin = array_diff($default, ['hash', 'link', 'title']);
		
		return [
            'default' => \array_slice(\array_keys($this->tableSchema->columns), 1),
			'admin' => $admin
        ];
    }
	
	public function attributeFormats() {
		return [
			'content'  => 4,
			'header'  => 2,
			'src'  => 13
		];
	}
	
	public function attributeLabels() {
		return [
			'pub_date' => 'Дата публикации',
			'header' => 'Заголовок (полностью)',
			'title' => 'Заголовок (кратко)',
			'content'  => 'Содержание',
			'preview'  => 'Анонс',
			'src'  => 'Краткий прогноз'
		];
	}
	
	/**
     * @return array Validation rules
     */	
	public function rules() {
		return [
			[['title', 'header', 'preview', 'content'], 'required', 'message' => 'Обязательное поле']
		];
	}
}
?>