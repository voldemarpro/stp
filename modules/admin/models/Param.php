<?php
namespace app\modules\admin\models;

use Yii;
use yii\db\Command;
use yii\db\ActiveRecord;

class Param extends \yii\db\ActiveRecord
{	
	/**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%params}}';
    }	
	
	/**
     * @var array Rendering format types of application params
     */	
	public static $formats = [
		0  => 'Целое число',
		1  => 'Вещественное число',
		2  => 'Строка',
		3  => 'Простой текст',
		4  => 'Форматируемый текст',
		5  => 'Время',
		6  => 'Дата',
		7  => 'Дата и время',
		8  => 'Дни недели',
		9  => 'Раздел из списка',
		10 => 'Тип позиции',
		11 => 'Счет-получатель',
		12 => 'Аттестат пользователя',
		13 => 'Флаг',
		14 => 'Номер карты',
		15 => 'Сотовый телефон',
		16 => 'Электронная почта',
		17 => 'Паспортные данные',
		18 => 'Опция'
	];
	
	public static $weekDays = [
		1 => 'Пн',
		2 => 'Вт',
		3 => 'Ср',
		4 => 'Чт',
		5 => 'Пт',
		6 => 'Сб',
		0 => 'Вс'
	];	
	
	public function scenarios() {
        return [
            'default' =>  \array_keys($this->tableSchema->columns),
			'update' =>  \array_slice(\array_keys($this->tableSchema->columns), 1, null)
        ];
	}
	
	/**
     * @return array Validation rules
     */	
	public function rules() {
		return [];
	}
	
	public function attributeLabels() {
		return [
			'name'		=>  'Переменная',
			'title'		=>  'Заголовок',
			'type'		=>  'Формат',
			'value'		=>  'Значение',
			'thread_id' =>  'Секция'
		];
	}
	
	public static function correctPhone($phone = '')
	{
		if ($phone) {
			$phone = preg_replace('/[^\d]/', '', $phone);
			if (strlen($phone)) {
				if ($phone[0] == '7' || $phone[0] == '8')
					$phone = substr($phone, 1);
			}
		}
		
		return $phone;
	}

	public static function encode($format, $param) {
		switch($format) {
			case 0:
				$val = (int)$param;
				break;

			case 1:
				$val = (float)$param;
				break;

			case 2:
			case 3:
				$val = htmlspecialchars($param);
				break;
				
			case 4:
				$val = (string)$param;
				break;

			case 5:
				if (is_array($param) && count($param) == 3 && implode('', $param)) {
					foreach ($param as $k=>&$p) {
						if ($p >= 60) 
							$p = $k ? 59 : 0;
						elseif ($k == 0 && $p > 23)
							$p = 0;
						
						$p = $p < 10 ? '0'.intval($p) : $p;
					}
					unset($p);
					$val = implode(':', $param);
					$val = date('H:i:s', strtotime($val) - \Yii::$app->params['dto']);
				} else
					$val = null;
				break;
			
			case 6:
				if (strlen($param) == 10) {
					$dateUnits = explode('.', $param);
					$val = implode('-', array_reverse($dateUnits));
				} else
					$val = null;
				break;
				
			case 7:
				if (is_array($param) && count($param) == 4 && implode('', $param)) {
					$date = $param['-1'];
					$dateUnits = explode('.', $date);
					unset($param['-1']);
					foreach ($param as $k=>&$p) {
						if ($p >= 60) 
							$p = $k ? 59 : 0;
						elseif ($k == 0 && $p > 23)
							$p = 0;
						
						$p = $p < 10 ? '0'.intval($p) : $p;
					}
					unset($p);
					$time = date('H:i:s', strtotime(implode(':', $param)) - \Yii::$app->params['dto']);
					$val = implode('-', array_reverse($dateUnits)).' '.$time;
				} else
					$val = null;
				break;
			
			case 8:
				if (is_array($param)) {
					$val = [];
					foreach (self::$weekDays as $k=>$w)
						$val[$k] = isset($param[$k]) ? 1 : 0;
				} else
					$val = [0, 0, 0, 0, 0, 0, 0];
				break;
				
			case 9:
			case 13:
				if (is_array($param))
					$val = (int)array_sum($param);
				else
					$val = (int)$param;
				break;

			case 12:
				if (is_array($param))
					$val = array_sum($param);
				else
					$val = 0;
				break;				
				
			case 14:
				if (is_array($param) && count($param) == 4 && implode('', $param))
					$val = implode('', array_map('intval', $param));
				else
					$val = null;
				break;
			
			case 15:
				$val = self::correctPhone(strval($param));
				if ($val == '')
					$val = null;
				break;
				
			case 17:
				if (is_array($param) && count($param) == 4 && implode('', $param)) {
					$val = implode('|', $param);
					$val = \app\models\Traider::myAESencrypt($val);
				} else
					$val = null;
				break;
			
			case 18:
				$val = (int)$param;
				break;
			
			default:
				break;				
		}
		
		return $val;
	}
	
	public static function getFormats($model) {
		if (\method_exists($model, 'attributeFormats'))
			$formats = $model->attributeFormats();
		else
			$formats = [];
		
		if ($model->tableSchema) {
			foreach ($model->tableSchema->columns as $col) {

				if (isset($formats[$col->name]))
					continue;

				if ($col->dbType == 'date')
					$f_i  = 6;
				elseif ($col->dbType == 'time')
					$f_i  = 5;
				elseif ($col->dbType == 'datetime')
					$f_i  = 7;
				elseif (strpos($col->dbType, 'int') !== false)
					$f_i  = 0;
				elseif (strpos($col->dbType, 'float') !== false)
					$f_i  = 1;
				elseif (strpos($col->dbType, 'char') !== false && $col->size <= 100)
					$f_i  = 2;
				elseif (strpos($col->dbType, 'char') !== false || strpos($col->dbType, 'text') !== false)
					$f_i  = 3;
				else
					$f_i  = 4;
					 
				$formats[$col->name] = $f_i;
			}
		}
		
		return $formats;
	}	
}
?>