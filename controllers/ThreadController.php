<?php
namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\models\LoginForm;
use app\models\Trader;

class ThreadController extends Controller
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
                        'actions' => ['index', 'logout', 'summary', 'readnotice'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['login'],
                        'allow' => true,
                        'roles' => ['?', '@']
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

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
		if (Yii::$app->thread->defaultItem) {
			// Запускаем действие по умолчанию
			return Yii::$app->runAction(Yii::$app->thread->defaultItem['vname']);
		}	
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect("/");
        }

        $model = new LoginForm();
		$model->load(Yii::$app->request->post(), '');
		$model->login = Trader::correctPhone($model->login);
        
		if ($model->login()) {
            return $this->redirect("/");
        }

        return $this->renderPartial('login', [
            'model' => $model,
			'errors'=> $model->firstErrors
        ]);
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
	
    /**
     * Возвращает состояние торгового кабинета
	 * 
	 * @return array
     */	
    public function actionSummary() {
		Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
		return Yii::$app->user->identity->getTrading();
	}
	
    /**
     * Mark notice as read
     */	
    public function actionReadnotice($id) {
		if ($id = intval($id)) {
			$user_id = \Yii::$app->user->id;
			Yii::$app->db->createCommand()->update('{{%user_notices}}', ['read'=>1], "`notice_id` = $id AND `user_id` = $user_id")->execute();
		}
    }
}