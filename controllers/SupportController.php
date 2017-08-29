<?php
namespace app\controllers;

use Yii;
use app\components\MainController;
use app\models\SupportTicket;

/**
 * Работа с обращениями (служба поддержки)
 */
class SupportController extends MainController
{
	/**
     * @inheritdoc
     */
    public function behaviors()
    {
        $arr = parent::behaviors();
		$arr['access']['rules'][] =  ['allow' => true, 'verbs' => ['GET', 'POST']];
		
		return $arr;
    }    

	public function actionList()
    {
        $oitems = SupportTicket::find()
			->where('`user_id` = '.Yii::$app->user->id)
			->orderBy('`date_time` DESC')
			->limit(3)
			->all();
		$items = [];
		foreach ($oitems as $i=>$oi) {
			$items[$i] = $oi->toArray();
			$items[$i]['date_time'] = Yii::$app->formatter->asDate(strtotime($items[$i]['date_time']) + DTIME_OFFSET, "EE, d MMMM HH:mm") . " MSK";
			if ($items[$i]['updated'])
				$items[$i]['updated'] = Yii::$app->formatter->asDate(strtotime($items[$i]['updated']) + DTIME_OFFSET, "EE, d MMMM HH:mm") . " MSK";
		}	
		return json_encode($items);
    }
	
    public function actionSend()
    {
		$itemExists = SupportTicket::find()
						->where('`user_id` = '.Yii::$app->user->id)
						->andWhere('`response` IS NULL OR `response` = ""')
						->one();
		if ($itemExists)
			return '-1';
			
		$model = new SupportTicket();
		
		$params = Yii::$app->request->get();
		$params['user_id'] = Yii::$app->user->id;
		$params['date_time'] = \date('Y-m-d H:i:s');
		
		if ($model->load($params, '')) {
			if ($model->save())
				return $this->actionList();
			else
				return json_encode($model->firstErrors);
		}
	}
}
