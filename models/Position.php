<?php
namespace app\models;

use Yii;
use yii\db\Command;
use yii\db\ActiveRecord;
use app\models\Contract;

class Position extends ActiveRecord
{
	const BUY_ID = 1;
	const SELL_ID = -1;
	
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
	
	/**
     * @return array Validation rules
     */	
	public function rules() {
		return [
			[['open_quot', 'close_quot', 'result'], 'double'],
			[['disabled'], 'boolean'],
			
			[['open_time', 'open_quot', 'volume'], 'required', 'message' => 'Обязательное поле']
		];
	}
	
	public function attributeFormats() {
		return [
			'user_id'  => 18,
			'disabled'  => 13
		];
	}
	
	public function open($type = 1) {
		if (!($q = Contract::getQuotes()) || empty($q[ STP_VRS ]))
			return false;
		else
			$q = $q[ STP_VRS ];
		
		$this = new (self::className());
		$this->type = $type;
		$this->open_quot = $this->type > 0 ? $q['ask'] : $q['bid'];
		$this->open_time = date('Y-m-d H:i:s');
		$this->volume = floor(Yii::$app->user->identity->credit / $this->open_quot);
		
		Yii::$app->user->identity->balance -= $this->volume * $this->open_quot;
		
		$conn = \Yii::$app->db;
		$dbt = $conn->beginTransaction(); 

		try {
			Yii::$app->user->identity->save();
			$this->save();
			$dbt->commit();
			return true;
		} catch (\Exception $e) {
			$dbt->rollBack();
			throw $e;
		}	
	}
	
	public function close() {
		if (!($q = Contract::getQuotes()) || empty($q[ STP_VRS ]))
			return false;
		else
			$q = $q[ STP_VRS ];
		
		$this->close_quot = $this->type > 0 ? $q['bid'] : $q['ask'];
		$this->result = $this->type * ($this->close_quot - $this->open_quot) * $this->volume;
		$this->close_time = date('Y-m-d H:i:s');

		Yii::$app->user->identity->balance = Yii::$app->user->identity->credit;

		$conn = \Yii::$app->db;
		$dbt = $conn->beginTransaction(); 

		try {
			Yii::$app->user->identity->save();
			$this->save();
			$dbt->commit();
			return true;
		} catch (\Exception $e) {
			$dbt->rollBack();
			throw $e;
		}	
	}
	
	public static function validateOpen() {
		$allowTrade = time() < Yii::$app->params['close_time'] && time() > Yii::$app->params['open_time'];
		$allowOpenByTime = $allowTrade && time() < Yii::$app->params['input_before'];
		$p = Position::find()
				->where('`user_id` = '.Yii::$app->user->id)
				->andWhere('DATE(`open_time`) = CURDATE()')
				->one();
		if ($p || !$allowOpenByTime)	
			return false;
		else
			return true;
	}
	
	public static function validateClose() {
		$allowTrade = time() < Yii::$app->params['close_time'] && time() > Yii::$app->params['open_time'];
		$p = Position::find()
				->where('`user_id` = '.Yii::$app->user->id)
				->andWhere('DATE(`open_time`) = CURDATE()')
				->one();
		if (!$p || !$allowTrade || (time() - $p->open_time) < 600)	
			return false;
		else
			return $p;
	}
}
?>