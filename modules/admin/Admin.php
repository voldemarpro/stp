<?php
namespace app\modules\admin;

class Admin extends \yii\base\Module
{
    public $name;
	
	public function init()
    {
        parent::init();
		
		$user = \Yii::$app->user;
		$user->identityClass = 'app\modules\admin\models\Manager';
		$user->loginUrl = ['/secret/login'];
		
		\Yii::$app->errorHandler->errorAction = 'secret/thread/error';

		\Yii::configure($this,
			[
				'name' => 'STP-1 Admin',
				'layout' =>  'main',
				'defaultRoute' => 'thread',
				'components' => [
					'thread' => [
						'class' => 'app\modules\admin\components\AdmThread',
					]
				]
			]
		);
    }
}
?>