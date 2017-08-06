<?php
namespace app\models;

use Yii;
use yii\db\Command;
use yii\db\ActiveRecord;
use app\models\Quotation;

class Traider extends ActiveRecord implements \yii\web\IdentityInterface
{
	const BLOCKED = 1; // клиент заблокирован
    const ACTIVE = 0; // клиент активен
	
	const ERR_AUTH_DATA = 'Неверный логин или пароль';
	const ERR_PHONE_USED = 'Номер телефона уже зарегистрирован';
	const ERR_PROFILE_EDIT = 'Ошибка редактирования профиля';
	
	public static $terms = [
		['Зачет убыточных сделок',	'Выкуп убыточных сделок в конце дня'],
		['Свободное закрытие позиции',	'Автоматическое закрытие позиции'],
		['Нахождение в позиции не менее 30 мин'],
		['Ежемесячное списание долгов на счете SOTACARD']
	];
	
	public $oldLogin = '';
	
	public $grades = [
		0 => 'Новый',
		1 => 'Авторизован',
		2 => 'Анкета заполнена',
		3 => 'Телефон проверен',
		4 => 'Эл.почта проверена',
		
		10 => 'Администратор'
	];
	
	public function attributeFormats() {
		return [
			'phone'		=> 15,
			'pay_card'	=> 14,
			'sotacard'	=> 14,
			'opt'		=> 13,
			'blocked'	=> 13,
			'grade'		=> 12,
			'passport'	=> 17
		];
	}
	
	/**
	 * @return array Attribute labels
	 */	
	public function attributeLabels() {
		return [
			'id'		=> '#',
			'first_name'=> 'Имя',
			'mid_name'	=> 'Отчество',
			'last_name'	=> 'Фамилия',
			'phone'		=> 'Сотовый телефон',
			'email'		=> 'Электронная почта',
			'birth_date'=> 'Дата рождения',
			'passport'	=> 'Паспорт',
			
			'login'		=> 'Логин',
			'pwd'		=> 'Пароль',
			
			'deposit'	=> 'Депозит',
			'balance'	=> 'Баланс',
			'credit'	=> 'Сумма договора',
			'debit'		=> 'Текущая прибыль',
			'start_date'=> 'Договор (начало)',
			'end_date'	=> 'Договор (окончание)',
			'contract'	=> 'Номер договора',
			
			'pay_card'	=> 'Номер карты',
			'pay_bank'	=> 'Наименование банка',
			'sotacard'	=> 'Номер SOTA-карты',
			'fee'		=> 'Комиссия (%)',
			
			'opt'		=> 'Выкуп сделок',
			'blocked'	=> 'Отключен',
			'grade'		=> 'Аттестат'
		];
	}
	
	private static $aesKey = '^__&ZEEgwergSb8__$';

	/**
	 *	Метод для кодирования личных данных
	 */	
	public static function myAESencrypt($val) {
		$key = self::$aesKey;
		$mysqlKey = str_repeat(chr(0), 16);
		for ($i = 0, $len = strlen($key); $i < $len; $i++)
			$mysqlKey[$i%16] = $mysqlKey[$i%16] ^ $key[$i];
		$padValue = 16 - (strlen($val) % 16);
		$val = str_pad($val, (16*(floor(strlen($val) / 16)+1)), chr($padValue));
		$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB), MCRYPT_DEV_URANDOM);
		
		return bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $mysqlKey, $val, MCRYPT_MODE_ECB, $iv));		
	}
	
	/**
	 *	Метод для расшифровки личных данных
	 */
	public static function myAESdecrypt($aesVal) {
		$n = strlen($aesVal);		
		$hexstr = $aesVal;
		$aesVal = '';
        $i = 0; 
        while ($i < $n) 
        {              
            $c = pack("H*", substr($hexstr, $i, 2)); 
            if ($i == 0) $aesVal = $c; 
            else $aesVal .= $c;
            $i += 2; 
        }
 		
		$key = self::$aesKey;	
		$mysqlKey = str_repeat(chr(0), 16);
		for ($i = 0, $len = strlen($key); $i < $len; $i++)
			$mysqlKey[$i%16] = $mysqlKey[$i%16] ^ $key[$i];
		$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB), MCRYPT_DEV_URANDOM);

		return preg_replace('~[^/А-Яа-я\.\s\|A-Za-z0-9_]~u', '', mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $mysqlKey, $aesVal, MCRYPT_MODE_ECB, $iv));
	}
	
	public static function formatName($t = [], $shortMode = true) {
		if (!empty($t->last_name) && !empty($t['first_name'])&& !empty($t->mid_name)) {
			if ($shortMode)
				return $t->last_name.' '.mb_substr($t->first_name, 0, 1, 'utf-8').'.'.($t->mid_name ? ' '.mb_substr($t->mid_name, 0, 1, 'utf-8').'. ' : '');
			else
				return $t->last_name.' '.$t->first_name.' '.$t->mid_name;
		
		} else
			return 'Без имени';
	}
	
	/**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%users}}';
    }
	
	/**
     * @return array Validation rules
     */	
	public function rules() {
		return [
			[['sotacard', 'login', 'pwd', 'phone', 'balance', 'credit', 'end_date'], 'required', 'on'=>'insert', 'message' => 'Обязательное поле'],
			[['birth_date', 'first_name', 'last_name', 'mid_name', 'email', 'phone'], 'required', 'on'=>'update', 'message' => 'Обязательное поле'],
			
			[['first_name', 'last_name', 'mid_name'], 'string', 'max' => 15],
			[['birth_date', 'start_date', 'end_date'], 'date', 'format'=>'Y-m-d', 'message'=>'Некорректная дата'],			
			
			['login', 'unique', 'message' => 'Логин уже занят', 'filter' => ['!=', 'login', $this->oldLogin]],
			['login', 'checkDevLogin'],
			['email', 'email', 'message' => 'Неправильный email'],
			['grade', 'number'],
			['fee', 'integer', 'max' => 100, 'min'=>0],
			['phone',    'string', 'min' => 10, 'max' => 10],
			['sotacard', 'string', 'max' => 17],
			['pay_card', 'string', 'max' => 19],
			['pay_bank', 'string', 'max' => 20],
			['passport', 'string', 'max' => 200],
			
			[['balance', 'credit'], 'double', 'max' => 2000000, 'min'=>1],
			
			[['opt', 'blocked'], 'boolean']
		];
	}
	
	public function checkDevLogin($login = '')
    {
        if (!$login && $this->login)
			$login = $this->login;
		
		return $login != 'dev';
    }	

	public function scenarios() {
       
	   $cols = $this->tableSchema->columns;
	   $columns1 = \array_slice(\array_keys($cols), 1);
	   $columns2 = \array_diff($columns1, ['stat']);
	   $columns3 = \array_diff(
						$columns1,
						['birth_date', 'stat', 'grade', 'blocked', 'pay_bank', 'pay_card', 'passport']
					);
	
	   return [
            'default' => $columns1,
			'update' => $columns1,
			'admin' => $columns2,
			'insert' => $columns3,
       ];
	}
   
    /**
     * Query to get all traider positions
     */		
    public function getPositions()
    {
        return $this->hasMany(Position::className(), ['user_id' => 'id']);
    }
    
    /**
     * Query to get all notices written for current traider
     */		
	public function getNotices()
    {
       return $this->hasMany(Notice::className(), ['id'=>'notice_id'])
				->viaTable('{{%user_notices}}', ['user_id'=>'id'], function($q) { return $q->andWhere('`read` = 0'); });
    }

    public static function findByLogin($login)
    {
        return Traider::find()->where(['login' => $login])->one();
    }	

    /**
     * @inheritdoc
     */	
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * @inheritdoc
     */	
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['pwd' => $token]);
    }

    public function getId()
    {
        return $this->id;
    }
	
    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return \strrev($this->pwd);
    }
	
    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }
	
    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePwd($pwd)
    {
        return $this->pwd === self::myAESencrypt($pwd);
    }
	
    /**
     * Текущее состояние по сделкам и базовой котировке
     *
     * @return array
     */	
	public static function getState() {
        $state = [];
		$user = Yii::$app->user->identity;
		
		if (!($user->grade & 2)) {
			$user->grade += 2;
			$user->save(false);
		}
		
		$isActive = time() <= strtotime($user->end_date) && time() >= strtotime($user->start_date);

		//открытые активные позиции
		$positions = $user->getPositions()
			->where('DATE(`open_time`) = CURDATE()')
			->andWhere(['=', 'disabled', 0])
			->orderBy('`id`')
			->all();
		
		$state['balance'] = $user->balance;
		
		$state['allowBuy'] = 1;
		$state['allowSell'] = 1;
		$state['allowOpen'] = intval(!\count($positions) && $isActive && ($user->grade & 4));
		$state['allowClose'] = 0;

		if ($quot = Quotation::getBase()) {
			$state['quot'] = [
				'bid' => (float)$quot['bid'],
				'ask' => (float)$quot['ask'],
				'diff'=> 0,
				'avg' => (float)$quot['avg'],
				'date_time'=>$quot['date_time']
			];
			$state['quot']['ref'] = $quot['ref'];
			$state['quot']['diff'] = $quot['ref'] ? round(($quot['avg'] - $quot['ref'])/$quot['ref'] * 100, 2) : 0.00;
			if (\strtotime($quot['date_time']) <= Yii::$app->params['open_time'] || \time() > Yii::$app->params['input_before'])
				$state['allowOpen'] = 0;
		}

		if (\count($positions)) {
			$current = \end($positions);
			$state['currentPosition']['id'] = $current->id;
			$state['currentPosition']['openTime'] = \date('H:i', \strtotime($current->open_time) + 3*60*60);
			$state['currentPosition']['openQuot'] = $current->open_quot;
			$state['currentPosition']['openSum'] = $current->open_sum;
			$state['currentPosition']['type'] = $current->type;

			if ((time() - \strtotime($current->open_time)) < \Yii::$app->params['pos_min_time'] * 60)
				$state['allowClose'] = 0;
			elseif ($user->deposit || !$user->opt)
				$state['allowClose'] = !$current->close_time;
			
			if ($current->close_time) {
				$state['currentPosition']['closeTime'] = \date('H:i', \strtotime($current->close_time) + 3*60*60);
				$state['currentPosition']['closeQuot'] = $current->close_quot;	
			}
			
			// Разрешаем куплю/продажу при закрытии сделки
			// Если была покупка, запрещаем повторную покупку, и наоборот
			if ($current->type > 0) {
				$state['allowBuy'] = 0;
				if (!$current->close_time)
					$state['currentPosition']['result'] = ($quot['bid'] - $current->open_quot) * $current->open_sum;
			} else {
				$state['allowSell'] = 0;
				if (!$current->close_time)
					$state['currentPosition']['result'] = -($quot['ask'] - $current->open_quot) * $current->open_sum;
			}
			
			if ($current->close_time)
				$state['currentPosition']['result'] = (float)$current->result; 
		
		} else {
			$state['currentPosition'] = ['openQuot'=>0, 'openSum'=>0, 'type'=>0, 'result'=>0];
		}
		
		return $state;
	}
	
    /**
     * Update notification bind records after user data modification
     *
     * @return array
     */		
	public function afterSave($insert, $changedAttributes) {
		parent::afterSave($insert, $changedAttributes);
		
		if ($notices = Notice::find()->where('`auto` = 1 AND `filter` >= 0')->all()) {
			foreach ($notices as $item) {
				if ($item->filter > 0) {
					//new user
					$target1 = true;
					if ($item->filter & 1)
						$target1 = !($this->grade & 4) ? true : false;
					
					// deposit criteria
					if ($item->filter & 2)
						$target2 = $this->deposit ? false : true;
					elseif ($item->filter & 4)
						$target2 = $this->deposit ? true : false;
					else
						$target2 = false;
						
					//position buy-out criteria
					if ($item->filter & 8)
						$target3 = $this->opt ? false : true;
					elseif ($item->filter & 16)
						$target3 = $this->opt ? true : false;
					else
						$target3 = false;
				
				} else {
					$target1 = $target2 = $target3 = true;
				}
				
				if ($target1 && ($target2 || $target3 || $target2 == $target3)) {
					$rel_id = (new \yii\db\Query)
						->select('id')
						->from('{{%user_notices}}')
						->where("`notice_id` = {$item->id} AND `user_id` = {$this->id}")
						->createCommand()->queryScalar();
					if (!$rel_id)
						\Yii::$app->db->createCommand()->insert('{{%user_notices}}', [
							'user_id' => $this->id,
							'notice_id' => $item->id
						])->execute();									
				} else {
					\Yii::$app->db->createCommand()->delete(
						'{{%user_notices}}',
						"`notice_id` = {$item->id} AND `user_id` = {$this->id}"
					)->execute();				
				}
			}
		}
	}
}
?>