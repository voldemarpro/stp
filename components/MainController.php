<?php
namespace app\components;

use Yii;
use yii\filters\AccessControl;

class MainController extends \yii\web\Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
				'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@']
                    ],
                ],
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ]
        ];
    }
	
	public function beforeAction($action) {
		if ($action->id == 'index') {
			Yii::$app->view->params['summary'] = Yii::$app->user->identity->getTrading();
		}
		return parent::beforeAction($action);
	}
}