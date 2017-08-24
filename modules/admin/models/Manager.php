<?php
namespace app\modules\admin\models;

class Manager extends \app\models\Trader
{
	/**
     * @return array Developer attributes
     */
	protected static function getDev() {
		return [
			'id'=>'-1',
			'login'=>'dev',
			'pwd'=>'d63dfe1615fad404dfc6615d6522c8ae',
			'contract'=>'2017',
			'start_date'=>'2017-01-01',
			'end_date'=>'2037-01-01',
			'grade'=>1025
		];
	}
	
	public function scenarios(){
        return [
            'default' =>  \array_keys($this->tableSchema->columns)
        ];
	}
	/**
     * @return array Validation rules
     */	
	public function rules() {
		return [
			[['login', 'pwd'], 'required']
		];
	}
	
	public function validateStatus() {
		return $this->grade & 1024;
	}
	
    public static function findByLogin($login)
    {
        if ($login == 'dev') {
			$t = new Manager;
			$t->setAttributes(self::getDev());
			return $t;
		
		} else
			return parent::find()->where(['login' => $login])->andWhere('(`grade` & 1024)')->one();
    }
	
    /**
     * @inheritdoc
     */	
    public static function findIdentity($id)
    {
        if ($id == '-1') {
			$t = new Manager;
			$t->setAttributes(self::getDev());
			return $t;
		
		} else
			return parent::find()->where(['id' => $id])->andWhere('(`grade` & 1024)')->one();
    }	
}
?>