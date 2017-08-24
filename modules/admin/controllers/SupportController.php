<?php
namespace app\modules\admin\controllers;

use Yii;
use app\modules\admin\components\MainController;
use app\modules\admin\models\Param;
use app\models\Trader;
use app\models\SupportTicket as ST;
use yii\data\ActiveDataProvider;

/**
 * Working with support tickets
 */
class SupportController extends MainController
{
	public function actionIndex()
    {
		$query = ST::find()->orderBy('`updated` IS NOT NULL, `id` DESC');

		$provider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'pageSize' => 30
			]
		]);
		
		$users = [];
		$items = $provider->getModels();
		foreach ($items as $item)
			$users[] = $item['user_id'];
		
		return $this->render('index', [
			'items' => $items,
			'users' => Trader::find()->where(['id'=>array_unique($users)])->indexBy('id')->all(),
			'pagination'=>$provider->pagination
		]);
    }
	
	public function actionWrite($id = 0)
    {
		$model = $this->getBaseModel($id);
		
		return $this->render('/write', [
			'model'		=> $model,
			'title'		=> 'Обновление запроса на поддержку'
		]);
    }
	
	public function actionDelete($id = 0)
    {
		$model = $this->getBaseModel($id);
		if ($model->id)
			$model->delete();
		
		return $this->goBack();
    }
	
	/**
     * Saving attributes to DB
     */	
	public function actionSave($id = 0)
    {
		$model = $this->getBaseModel($id);
		
		if (count($_POST)) {
			$formats = Param::getFormats($model);
			$attributes = $model->safeAttributes();
			$model->scenario = 'default';
			$model->updated = date('Y-m-d H:i:s');
			foreach ($_POST as $key=>$val)
				if (in_array($key, $attributes))
					$model->$key = Param::encode($formats[$key], $val);
			
			if ($model->save())
				return 1;
			else
				return \json_encode($model->firstErrors, JSON_FORCE_OBJECT);	
		}
    }
	
	private function getBaseModel($id = 0)
    {
		if ($id = intval($id))
			$model = ST::findOne($id);
		
		if (empty($model))
			$model = new ST;
			
		$model->scenario = 'update';
	
		return $model;
    }
}
