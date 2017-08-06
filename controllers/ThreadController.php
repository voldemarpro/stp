<?php
namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\models\LoginForm;
use app\models\Quotation;

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
                        'actions' => ['index', 'logout', 'getstate', 'readnotice'],
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
        if ($model->load(Yii::$app->request->post(), '') && $model->login()) {
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
     * Обновление состояния текущих котировок USD ЦБ/BRENT и т.п. для "шапки" страницы.
     *
     * @return json
     */	
    public function actionGetstate($ajax = false) {
		// Форматирование знака числовой величины
		$signPref = [-1 => '<em class="monosign">&ndash;</em>', 0 => '<em class="monosign">&nbsp;</em>', 1 => '<em class="monosign">+</em>'];
		
		$open = 1;
		$nArr = [];
		$hQ = Quotation::getheaderquotes();

		foreach ($hQ as &$val) {
			$val['avg'] = \number_format($val['avg'], 2);
			$val['diff'] = $signPref[$val['diff'] ? $val['diff']/(\abs($val['diff'])) : 0].(\number_format(\abs($val['diff']), 2));
		}
		if (time() >= \Yii::$app->params['close_time'] || time() < \Yii::$app->params['open_time'])
			$open = 0;
		
		if ($notices = \Yii::$app->user->identity->getNotices()->limit(2)->all())
			foreach ($notices as $obj)
				$nArr[] = ['id'=>$obj->id, 'text'=>$obj->message];

		return \json_encode(['quot'=>$hQ, 'open'=>$open, 'notices'=>$nArr], JSON_FORCE_OBJECT);
    }
	
    /**
     * Mark notice as read
     */	
    public function actionReadnotice($id) {
		if ($id = intval($id)) {
			$user_id = \Yii::$app->user->id;
			\Yii::$app->db->createCommand()->update('{{%user_notices}}', ['read'=>1], "`notice_id` = $id AND `user_id` = $user_id")->execute();
		}
    }
}
