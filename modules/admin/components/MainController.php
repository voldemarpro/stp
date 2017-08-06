<?php
namespace app\modules\admin\components;

use yii\filters\AccessControl;

class MainController extends \yii\web\Controller
{
	/*
	public function init() {
		parent::init();
	}*/
	
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
                        'roles' => ['@'],
						'verbs' => ['GET', 'POST', 'PUT']
                    ]
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
	
	public function goBack($defaultUrl = NULL) {
		$this->redirect(["/{$this->module->id}/{$this->id}"]);
	}	
}