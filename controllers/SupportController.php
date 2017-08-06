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

	public function actionIndex()
    {
        return $this->render('index', [
           'items' => SupportTicket::find()->where('`user_id` = '.Yii::$app->user->id)->orderBy('`date_time` DESC')->limit(5)->all()
        ]);
    }
	
    public function actionPost()
    {
		if (count($_POST)) {
			$itemExists = SupportTicket::find()->where('`user_id` = '.Yii::$app->user->id)->andWhere('`response` IS NULL OR `response` = ""')->one();
			if ($itemExists)
				return '{"error":"У вас есть незакрытое обращение"}';
				
			$model = new SupportTicket();
			$params = Yii::$app->request->post();
			$params['user_id'] = Yii::$app->user->id;
			$params['date_time'] = \date('Y-m-d H:i:s');
			
			if ($model->load($params, '')) {
				if ($model->save())
					return $this->renderPartial('index', [
					   'items' => SupportTicket::find()->where('`user_id` = '.Yii::$app->user->id)->orderBy('`date_time` DESC')->limit(5)->all()
					]);
				else
					return \json_encode($model->firstErrors, JSON_FORCE_OBJECT);
			}
		}
    }
}
