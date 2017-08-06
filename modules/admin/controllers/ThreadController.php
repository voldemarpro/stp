<?php
namespace app\modules\admin\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\modules\admin\models\LoginForm;

class ThreadController extends Controller
{
	//public $layout = 'main';
	
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
                        'actions' => ['index', 'logout', 'error'],
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
                'class' => 'yii\web\ErrorAction'
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
		if ($this->module->thread->defaultItem) {
			// Запускаем действие по умолчанию
			return $this->module->runAction($this->module->thread->defaultItem['vname']);
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
            return $this->redirect("/{$this->module->id}");
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post(), '') && $model->login())
			 return $this->redirect("/{$this->module->id}");

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

        return $this->redirect("/{$this->module->id}");
    }
	
    /**
     * Обновление состояния текущих котировок USD ЦБ/BRENT для "шапки" страницы.
     *
     * @return json
     	
    public function actionGetheaderquotes($ajax = false) {
		// Форматирование знака числовой величины
		$signPref = [-1 => '<em class="monosign">&ndash;</em>', 0 => '<em class="monosign">&nbsp;</em>', 1 => '<em class="monosign">+</em>'];
		
		$hQ = $hQ = Quotation::getheaderquotes();
		foreach ($hQ as &$val) {
			$val['avg'] = \number_format($val['avg'], 2);
			$val['diff'] = $signPref[$val['diff'] ? $val['diff']/(\abs($val['diff'])) : 0].(\number_format(\abs($val['diff']), 2));
		}
		
		return \json_encode($hQ, JSON_FORCE_OBJECT);
    }*/
}
