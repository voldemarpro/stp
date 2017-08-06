<?php
namespace app\modules\admin\controllers;

use Yii;
use app\modules\admin\components\MainController;
use app\modules\admin\models\Param;
use app\models\Traider;
use app\models\Request;
use app\models\Payout;
use yii\data\ActiveDataProvider;

/**
 * Working with requests from traiders
 */
class RequestsController extends MainController
{
	public function actionIndex($f = 0, $for = 0)
    {
        // filter
		if ($f = intval($f)) {
			$sqlWhere = [
				1 => '`aux` IS NOT NULL',
				2 => '`aux` IS NULL'
			];
			$query = Request::find()->where($sqlWhere[$f]);
		
		} elseif($for = intval($for)) {
			$query = Request::find()->where("`user_id` = $for");
		
		} else {
			$query = Request::find();
		}
		
		$query = $query->orderBy('`status` <> 0, `id` DESC');
		
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
			'users' => Traider::find()->where(['id'=>array_unique($users)])->indexBy('id')->all(),
			'pagination'=>$provider->pagination,
			'filter'=>$for ? "u$for" : $f
		]);
    }
	
	public function actionWrite($id = 0)
    {
		$model = $this->getBaseModel($id);
		
		return $this->render('/write', [
			'model'		=> $model,
			'title'		=> 'Обновление заявки',
			'options'   => [
							'status' => !$model->status ? Request::$statusArray : [$model->status => Request::$statusArray[$model->status]]
						],
			'attributes'=> []
		]);
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
			$statusBefore = (int)$model->status;
			foreach ($_POST as $key=>$val)
				if (in_array($key, $attributes))
					$model->$key = Param::encode($formats[$key], $val);
			
			if ($model->save()) {
				if ($model->status != $statusBefore) {
					$t = Traider::findOne($model->user_id);
					
					if ($model->status > 0) {
						if (!$model->aux || $model->aux == 1) {
							if ($t->balance == $t->credit)
								$t->balance += $model->amount;

							$t->credit += $model->amount;
							
							if ($model->aux == 1) {
								$t->debit -= $model->amount;				
								
								$payout = new Payout;
								$payout->sum = $model->amount;
								$payout->type = 1;
								$payout->user_id = $t->id;
								$payout->{'date'} = date('Y-m-d');

								$payout2 = new Payout;
								$payout2->sum = -1 * $model->amount;
								$payout2->type = 0;
								$payout2->user_id = $t->id;
								$payout2->{'date'} = date('Y-m-d');
							}
				
							if ($t->save(false)) {
								if ($model->aux == 1) {
									$payout->save();
									$payout2->save();
								}
							}
				
						} elseif ($model->aux == 2) {
							$t->debit -= $model->amount;
							if ($t->save(false)) {
								$payout = new Payout;
								$payout->sum = -1 * $model->amount;
								$payout->type = 0;
								$payout->user_id = $t->id;
								$payout->{'date'} = date('Y-m-d');							
								$payout->save();
							}
						}
					}
					
					if ($model->status > 0)
						$comment = ", одобрено";
					elseif ($model->comment)
						$comment = ", отклонено (".$model->comment.")";
					else
						$comment = ", отклонено";
					
					$sms = new \app\components\Sms;
					$sms->send('8'.$t->phone, (Request::$types[($model->aux ? 1 : 0)]).$comment);
				}			
				return 1;
			} else
				return \json_encode($model->firstErrors, JSON_FORCE_OBJECT);	
		}
    }
	
	private function getBaseModel($id = 0)
    {
		if ($id = intval($id))
			$model = Request::findOne($id);
		
		if (empty($model))
			$model = new Request;
			
		$model->scenario = 'update';
	
		return $model;
    }
}
