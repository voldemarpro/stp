<?php
namespace app\modules\admin\controllers;

use Yii;
use app\modules\admin\components\MainController;
use app\modules\admin\models\Param;

/**
 * Managament of app globals
 */
class ParamsController extends MainController
{	
	public function actionIndex()
    {
        return $this->render('index', [
			'items'=>Param::find()->all()
		]);
    }
	
	/**
     * Writing application params to DB
     */	
	public function actionSave()
    {
		if (count($_POST)) {
			$saved = 0;
			$params = Param::find()->indexBy('name')->all();
			foreach ($_POST as $key=>$val)
				if (isset($params[$key])) {
					$_val = serialize(Param::encode($params[$key]->type, $val));
					if ($params[$key]->value != $_val) {
						$params[$key]->value = $_val;
						if ($params[$key]->save())
							$saved++;
					} else
						$saved++;
				}
			
			if ($saved == count($params))
				\Yii::$app->getSession()->setFlash('result', 'Измения сохранены');
				
			return $this->goBack();
		}
    }
}
