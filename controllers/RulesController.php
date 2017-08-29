<?php
namespace app\controllers;

use Yii;
use app\components\MainController;
use app\models\Tariff;

/**
 * Контроллер для рендера страницы с правилами торговли
 */
class RulesController extends MainController
{
    public function actionIndex($id = 1)
    {
		return $this->render('index', [
			'tariff' => Tariff::findOne( Yii::$app->user->identity->tariff_id )
		]);
    }
}
