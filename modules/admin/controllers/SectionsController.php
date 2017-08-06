<?php
namespace app\modules\admin\controllers;

use Yii;
use app\modules\admin\components\MainController;
use app\modules\admin\models\Param;
use app\components\Thread;

/**
 * Managament of app sections(threads)
 */
class SectionsController extends MainController
{
	
	public function actionIndex()
    {
        return $this->render('index', [
			'items' =>Thread::find()->orderBy(['precedence'=>SORT_ASC])->all()
		]);
    }
	
	/**
     * Writing sections params to DB
     */	
	public function actionSave()
    {
		if (count($_POST)) {
			$saved = 0;
			$sections = Thread::find()->indexBy('vname')->all();
			foreach ($_POST as $key=>$val)
				if (isset($sections[$key])) {
					$val = array_map('intval', $val);
					$sections[$key]->attributes = $val;
					if ($sections[$key]->save())
						$saved++;
				}

			if ($saved == (count($_POST) - 1))
				\Yii::$app->getSession()->setFlash('result', 'Измения сохранены');
				
			return $this->redirect(['index']);
		}
    }
}
