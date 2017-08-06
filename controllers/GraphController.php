<?php
namespace app\controllers;

use Yii;
use app\components\MainController;

/**
 * Контроллер для отображения графиков USD/RUB и BRENT
 */
class GraphController extends MainController
{
    public function actionIndex($id = 1)
    {
		return $this->render('index', [
			'id' => intval($id)
		]);
    }
}
