<?php
namespace app\modules\admin\controllers;

use Yii;
use app\modules\admin\components\MainController;
use app\modules\admin\models\Param;
use app\models\Traider;
use yii\data\ActiveDataProvider;

/**
 * Работа с учетными записями
 */
class UsersController extends MainController
{
	public function actionIndex($f = 0)
    {	

		//test mailer
		//echo (string) \app\modules\admin\components\AdmThread::mailTo('ramedlov87@yandex.ru', \Yii::$app->params['admin_email'], 'Вопрос смммум  миукпукп акцацуа', 'yii message 2yrtu');
		//die();
		
		if ($f_i = intval($f)) {
			$f = $f_i;
			$sqlWhere = [
				1 => '`deposit` = 0',
				2 => '`deposit` != 0',
				3 => '`blocked` != 0',
				4 => '(`grade` & 2) = 0',
				5 => '(`grade` & 1024) != 0'
			];
			$query = Traider::find()->where($sqlWhere[$f_i])->orderBy(['id' => SORT_ASC]);
		
		} elseif ($f && mb_strlen($f, 'utf-8') == 1) {
			$query = Traider::find()->where("LEFT(`last_name`, 1) = '$f'")->orderBy(['grade'=>SORT_ASC, 'id' => SORT_ASC]);
		
		} else {
			$f = 0;
			$query = Traider::find()->orderBy(['grade'=>SORT_ASC, 'id' => SORT_ASC]);
		}
		
		$provider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'pageSize' => 40
			]
		]);
		
		return $this->render('index', [
			'items' => $provider->getModels(),
			'pagination'=>$provider->pagination,
			'filter'=>$f
		]);
    }

	public function actionWrite($id = 0)
    {
		$model = $this->getBaseModel($id);
		
		return $this->render('/write', [
			'model'		=> $model,
			'title'		=> $model->isNewRecord ? 'Новый пользователь' : ($model->last_name.' '.$model->first_name.' '.$model->mid_name),
			'attributes'=> ['pwd' => $model->pwd ? Traider::myAESdecrypt($model->pwd) : ''],
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
			$model->oldLogin = $model->login;
			foreach ($_POST as $key=>$val)
				if (in_array($key, $attributes))
					$model->$key = Param::encode($formats[$key], $val);
			
			if ($model->balance == 0)
				$model->balance == (float)$model->credit;
			elseif ($model->credit == 0)
				$model->credit == (int)$model->balance;
			
			$model->pwd = Traider::myAESencrypt($model->pwd);

			if ($model->save())
				return 1;
			else
				return \json_encode($model->firstErrors, JSON_FORCE_OBJECT);	
		}
    }
	
	public function actionDelete($id = 0)
    {
		$model = $this->getBaseModel($id);
		if ($model->id) {
			$model->delete();
			
			\Yii::$app->db->createCommand()->delete(
				'{{%user_notices}}',
				"`user_id` = {$model->id}"
			)->execute();		
		}
		
		return $this->goBack();
    }
	
	private function getBaseModel($id = 0)
    {
		if ($id = intval($id))
			$model = Traider::findOne($id);
		
		if (empty($model)) {
			$model = new Traider;
			$model->scenario = 'insert';
			$model->start_date = date('Y-m-d');
			$model->end_date = date('Y-m-d', time() + 90*24*60*60);
			$model->contract =  '7' . rand(3,9) . substr((string)abs(crc32(time())), 0, 3);
			$model->login = $model->contract;
			$model->sotacard = '627649001040' . $model->contract;
			$model->balance = 100000;
			$model->credit = 100000;
			$model->fee = 5;
		
		} else
			$model->scenario = 'admin';
			
		return $model;
    }
	
    /**
     * Search users by name for autocomplete form field
     * 
     * @param string $term Search "sid"
     */
	public function actionSearch($term = '')
	{
		if ($term) {
			$results = array();
			$term = htmlspecialchars($term);
			if ($items = Traider::find()->where("`last_name` LIKE '%$term%' OR `first_name` LIKE '%$term%'")->limit(20)->all()) {
				foreach ($items as $it) {
					$mid_name = $it->mid_name ? " $it->mid_name" : '';
					$results[] = array('label'=>"{$it->last_name} {$it->first_name}$mid_name", 'id'=>$it->id);
				}
			}
			
			return json_encode($results);
		}
	}

	public function actionGetone($id = 0)
	{
		if ($item = Traider::findOne($id))
			return $this->renderPartial('item', ['item'=>$item]);
	}
}
