<?php
namespace app\modules\admin\controllers;

use Yii;
use app\modules\admin\components\MainController;
use app\modules\admin\models\Param;
use app\models\Traider;
use app\models\Notice;
use yii\data\ActiveDataProvider;

/**
 * Notifications
 */
class NoticesController extends MainController
{
	public function actionIndex($date = '')
    {

		$query = Notice::find();

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

	public function actionWrite($id = 0)
    {
		$model = $this->getBaseModel($id);
		$users = [];
		
		if ($model->filter < 0)
			$users = $model->getTraiders()->indexBy('id')->all();

		return $this->render('write', [
			'model'		=> $model,
			'title'		=> $model->isNewRecord ? 'Новая запись' : 'Редактирование записи',
			'users' => $users
		]);
    }
	
	/**
     * Saving traider attributes to DB
     */	
	public function actionSave($id = 0)
    {
		$model = $this->getBaseModel($id);

		if (count($_POST)) {
			$formats = Param::getFormats($model);
			$attributes = $model->safeAttributes();
			
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
						} else
							$model->filter = 0;

					} else
						$model->$key = Param::encode($formats[$key], $val);
				}
			
			if (!empty($_POST['users']))
				$model->filter = -1;

			if ($model->save()) {
				$rows = [];

				if ($model->filter == -1) {
					foreach (explode(',', $_POST['users']) as $user_id)
						$rows[] = [$model->id, $user_id];
				
				} else {
					if ($model->filter == 0) {
						$t = Traider::find()->all();
						foreach ($t as $user)
							$rows[] = [$model->id, $user->id];
							
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
						
						foreach ($query->all() as $user)
							$rows[] = [$model->id, $user->id];						
					}
				}
				
				\Yii::$app->db->createCommand()->delete(
					'{{%user_notices}}',
					"`notice_id` = {$model->id}"
				)->execute();
				
				\Yii::$app->db->createCommand()->batchInsert(
					'{{%user_notices}}',
					['notice_id', 'user_id'],
					$rows
				)->execute();
					
				return 1;
					
			} else
				return \json_encode($model->firstErrors, JSON_FORCE_OBJECT);	
		}
    }
	
	public function actionDelete($id = 0)
    {
		$model = $this->getBaseModel($id);
		if ($model->id) {
			\Yii::$app->db->createCommand()->delete(
				'{{%user_notices}}',
				"`notice_id` = {$model->id}"
			)->execute();
			$model->delete();
		}
		
		return $this->goBack();
    }
	
	private function getBaseModel($id = 0)
    {
		if ($id = intval($id))
			$model = Notice::findOne($id);
		
		if (empty($model))
			$model = new Notice;
			
		$model->scenario = 'default';
			
		return $model;
    }
}
