<?php
namespace app\modules\admin\controllers;

use Yii;
use app\modules\admin\components\MainController;
use app\modules\admin\models\Param;
use app\models\Traider;
use app\models\Message;
use yii\data\ActiveDataProvider;

/**
 * SMS dispatch
 */
class SmsController extends MainController
{
	public function actionIndex($date = '')
    {

		$query = Message::find();

		if ($date)
			$query = $query->andWhere("DATE(`date_time`) = '".date('Y-m-d', strtotime($date))."'");
		else
			$query = $query->andWhere("DATE(`date_time`) >= '".date('Y-m-d', time() - 30*24*60*60)."'");
		
		$provider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'pageSize' => 30
			]
		]);
		
		return $this->render('index', [
			'items' => $provider->getModels(),
			'pagination'=>$provider->pagination,
			'date'=>$date
		]);
    }
	
	public function actionCopy($id = 0)
    {
		return $this->actionWrite($id);
    }
	
	public function actionWrite($id = 0)
    {
		$model = $this->getBaseModel($id);

		return $this->render('write', [
			'model'	=> $model,
			'title'	=> 'Новое сообщение',
			'users' => $model->users ? Traider::find()->where(['id'=>explode(',', $model->users)])->indexBy('id')->all() : []
		]);
    }
	
	/**
     * Populating list of target traiders and dispatch
     */	
	public function actionSave()
    {
		$model = $this->getBaseModel(); //$_POST = $_GET;

		if (count($_POST)) {
			$formats = Param::getFormats($model);
			$attributes = $model->safeAttributes();
			$isNewRecord = $model->isNewRecord;
			
			if (!$model->date_time)
				$model->date_time = date('Y-m-d H:i:s');
			
			foreach ($_POST as $key=>$val)
				if (in_array($key, $attributes)) {
					if ($key == 'filter') {
						if (isset($val['grade']) || isset($val['opt']) || isset($val['deposit'])) {
							$val['grade'] = isset($val['grade']) ? $val['grade'] : 0;
							$val['opt'] = isset($val['opt']) ? $val['opt'] : 0;
							$val['deposit'] = isset($val['deposit']) ? $val['deposit'] : 0;
							$model->filter = array_sum($val);
							$model->users = '';
							
						} else {
							$model->filter = 0;
							$model->users = '';
						}
						
					} else
						$model->$key = Param::encode($formats[$key], $val);
				}
		
			if (!empty($_POST['users'])) {
				$model->filter = -1;
				$model->users = $_POST['users'];
			}

			if ($model->save()) {
				
				if ($isNewRecord)
				{
					if ($model->filter == -1) {
						
						$users = Traider::find()->where(['id'=>explode(',', $model->users)])->all();
					
					} else {
						if ($model->filter == 0) {
							
							$users = Traider::findAll();
						
						} else {
							$where = [];
							$query = Traider::find();
							
							if ($model->filter & 1)
								$where[] = '(`grade` & 4) = 0';
							if ($model->filter & 2 || $model->filter & 4)
								$where[] = '`deposit` = '.($model->filter & 4 ? '1' : '0');
							if ($model->filter & 8 || $model->filter & 16)
								$where[] = '`opt` = '.($model->filter & 16 ? '1' : '0');
							
							if ($whereStr = implode(' AND ', $where))
								$query = $query->where($whereStr);
							
							$users = $query->all();						
						}
					}
					
					if ($users) {		
										
						$phones = [];
						foreach ($users as $user)
							$phones[] = '8'.$user->phone;
						
						$sms = new \app\components\Sms;
						$sms->send($phones, $model->text, \Yii::$app->params['sms_sender']);
					}
				}
				
				return 1;
					
			} else
				return \json_encode($model->firstErrors, JSON_FORCE_OBJECT);
		}
    }
	
	public function actionDelete($id = 0)
    {
		$model = $this->getBaseModel($id);
		if ($model->id)
			$model->delete();
		
		return $this->goBack();
    }
	
	private function getBaseModel($id = 0)
    {
		if ($id = intval($id))
			$model = Message::findOne($id);
		
		if (empty($model))
			$model = new Message;
			
		$model->scenario = 'default';
			
		return $model;
    }
}
