<?php
namespace app\modules\admin\controllers;

use Yii;
use app\modules\admin\components\MainController;
use app\modules\admin\models\Param;
use app\models\Trader;
use app\models\MoneyTransfer;
use yii\data\ActiveDataProvider;

/**
 * Working with money transfers to/from Trader
 */
class PaymentsController extends MainController
{
	public function actionIndex($f = -1, $for = 0)
    {
        $f = intval($f);
		
		if ($f >= 0) {
			$query = MoneyTransfer::find()->where("`rec_type` = $f");
		
		} elseif ($for = intval($for)) {
			$query = MoneyTransfer::find()->where("`user_id` = $for");
		
		} else {
			$query = MoneyTransfer::find();
		}
		
		$provider = new ActiveDataProvider([
			'query' => $query->orderBy(['id' => SORT_DESC]),
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
			'pagination'=>$provider->pagination,
			'users'=>Trader::find()->where(['id'=>array_unique($users)])->indexBy('id')->all(),
			'filter'=>$f,
			'for'=>$for
		]);
    }

	public function actionWrite($id = 0)
    {
		$model = $this->getBaseModel($id);
		
		return $this->render('/write', [
			'model'		=> $model,
			'title'		=> $model->isNewRecord ? 'Новый перевод средств' : 'Редактирование записи о платеже',
			'options'=> [
				'type' => MoneyTransfer::$types
			],
		]);
    }

	/**
     * Saving Trader attributes to DB
     */	
	public function actionSave($id = 0)
    {
		$model = $this->getBaseModel($id);
		
		if (count($_POST)) {
			$formats = Param::getFormats($model);
			$attributes = $model->safeAttributes();
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
			$model = MoneyTransfer::findOne($id);
		
		if (empty($model))
			$model = new MoneyTransfer;

		if ($model->isNewRecord || $model->type > 0)
			$model->scenario = 'default';
		else
			$model->scenario = 'update';
	
		return $model;
    }
}
