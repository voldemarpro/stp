<?php
namespace app\controllers;

use Yii;
use app\components\MainController;
use app\models\MoneyTransfer;
use app\models\Request;

/**
 * Контроль за состянием счета и платежами
 */
class AccountController extends MainController
{
	/**
     * @inheritdoc
     */
    public function behaviors()
    {
        $arr = parent::behaviors();
		$arr['access']['rules'][0]['verbs'] = ['GET', 'POST'];
		
		return $arr;
    }   
	
	public function actionIndex()
    {
        return $this->render('index', [
           'items' => MoneyTransfer::find()
					   ->where('`user_id` = '.Yii::$app->user->id)
					   ->orderBy('`date_time` DESC')
					   ->limit(18)
					   ->all()
        ]);
    }
	
	public function actionPost()
    {
		if (count($_POST)) {
			$auxSql = isset($_POST['aux']) ? '`aux` IS NOT NULL' : '`aux` IS NULL';
			$itemExists = Request::find()->where('`user_id` = '.Yii::$app->user->id)->andWhere($auxSql)->andWhere('`status` = 0')->one();
			if ($itemExists)
				return 'Похожая заявка уже отправлена';
			
			$model = new Request();
			$params = Yii::$app->request->post();
			$params['user_id'] = Yii::$app->user->id;
			$params['date_time'] = \date('Y-m-d H:i:s');
			$params['amount'] = (float)$params['amount'];
			
			/*if (\Yii::$app->params['payout_minimum'] > $params['amount'])
				return '{"amount": "От 500 руб"}';*/
			if (\Yii::$app->user->identity->debit < $params['amount'])
				return '{"amount": "Недостаточно средств"}';
			
			if (!empty($params['aux'])) {
				$mxAmount = \Yii::$app->user->identity->debit < 10000 ? floor(\Yii::$app->user->identity->debit) : 10000;
				if ($params['amount'] > $mxAmount)
					$params['amount'] = $mxAmount;
			}
			
			if (!empty($params['_csrf']))
				unset($params['_csrf']);
			
			if ($model->load($params, '')) {
				if ($model->save()) {
					if (\Yii::$app->params['email_request']) {
						$u = Yii::$app->user->identity;
						$rt = $model->aux ? 1 : 0;
						$title = (Request::$types[$rt]) . ', ' . "{$u->last_name} {$u->first_name} {$u->mid_name}";
						$body = '<p>' . (Request::$types[$rt]) . ', ' . (number_format($model->amount, 2, '.', ' ')) . '<br/>';
						$body .= "<b>{$u->last_name} {$u->first_name} {$u->mid_name}</b></p>";
						//$from = [\Yii::$app->params['admin_email'] => 'SOTA-1'];
						\app\components\Thread::sendmail(Yii::$app->params['admin_email'], '', $title, $body);
					}
					
					return '1';
				} else
					return \json_encode($model->firstErrors, JSON_FORCE_OBJECT);
			}
		}
    }
}
