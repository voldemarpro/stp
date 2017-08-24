<?php
namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\models\LoginForm;
use app\models\Trader;
use app\models\Notice;
use app\models\Contract;


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
		
		$noticesToArray = [];
		if ($notices = \Yii::$app->user->identity->getNotices()->limit(2)->all())
			foreach ($notices as $obj)
				$noticesToArray[] = ['id'=>$obj->id, 'text'=>$obj->message];
		
		$allowTrade = time() < Yii::$app->params['close_time'] || time() > Yii::$app->params['open_time'];
		$s = [
			'session' => [
				'time' => time() + 3 * 24 * 3600,
				'allowTrade' => (int)$allowTrade,
				'allowBuy' => (int)$allowTrade,
				'allowSell' => (int)$allowTrade
			],
			'notices'  => $noticesToArray,
			'quotes'   => [],
			'position' => []
		];
		
		$q = Contract::getQuotes();
		$s['quotes'] = $q[STP_VRS];
		$s['quotes']['diff'] = $s['quotes']['close'] 
								? ($s['quotes']['close'] - ($s['quotes']['bid'] + $s['quotes']['ask']) / 2) / $s['quotes']['close'] * 100
								: 0;
		$s['quotes']['diff'] = round($s['quotes']['diff'], 2);						
		
		$p = Position::find()
					->where('`user_id` = '.Yii::$app->user->id)
					->andWhere('DATE(`open_time`) = CURDATE()')
					->one();
		if ($p) {
			if ($p->close_time === null) {
				$tradeQuot = $p->type > 0 ? $s['quotes']['bid'] ? $s['quotes']['ask'];
				$result = $p->type * ($tradeQuot - $p->open_quot) * $p->volume;	
			} else
				$result = $p->result;
			
			if ($p->type > 0)
				$s['session']['allowBuy'] = 0;
			else
				$s['session']['allowSell'] = 0;
			
			$s['position'] = [
				'type'       => $p->type,
				'volume'     => $p->volume,
				'close_time' => $p->close_time,
				'result'     => $p->result
			];
		
		} else {
			$s['session']['allowBuy'] = (int)($allowTrade && time() < Yii::$app->params['input_before']);
			$s['session']['allowSell'] = (int)($allowTrade && time() < Yii::$app->params['input_before']);
		}
		
		return $s;
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
