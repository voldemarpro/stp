<?php
namespace app\modules\admin\controllers;

use Yii;
use app\modules\admin\components\MainController;
use app\modules\admin\models\Param;
use app\models\News;
use yii\data\ActiveDataProvider;

/**
 * News monitoring && edit
 */
class NewsController extends MainController
{
	public function actionIndex()
    {
		$provider = new ActiveDataProvider([
			'query' => News::find()->orderBy(['pub_date' => SORT_DESC]),
			'pagination' => [
				'pageSize' => 30
			]
		]);

		return $this->render('index', [
			'items' => $provider->getModels(),
			'pagination'=>$provider->pagination
		]);
    }

	public function actionWrite($id = 0)
    {
		$model = $this->getBaseModel($id);
		
		return $this->render('/write', [
			'model'		=> $model,
			'title'		=> $model->isNewRecord ? 'Новая запись' : 'Редактирование записи'
		]);
    }

	/**
     * Saving Trader attributes to DB
     */	
	public function actionSave($id = 0)
    {
		$model = $this->getBaseModel($id);
		$model->scenario = 'default';
		
		if (count($_POST)) {
			$model->src = 0;
			$formats = Param::getFormats($model);
			$attributes = $model->safeAttributes();
			foreach ($_POST as $key=>$val)
				if (in_array($key, $attributes))
					$model->$key = Param::encode($formats[$key], $val);
					
			$model->title = $model->header;
			
			if ($model->save())
				return 1;
			else
				return \json_encode($model->firstErrors, JSON_FORCE_OBJECT);	
		}
    }

	private function getBaseModel($id = 0)
    {
		if ($id = intval($id))
			$model = News::findOne($id);
		
		if (empty($model))
			$model = new News;
			
		$model->scenario = 'admin';
	
		return $model;
    }
}
