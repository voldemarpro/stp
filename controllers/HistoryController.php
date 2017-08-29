<?php
namespace app\controllers;

use Yii;
use app\components\MainController;
use app\models\Position;

/**
 * История сделок
 */
class HistoryController extends MainController
{
    public function actionIndex()
    {
        return $this->render('index', [
            'items' => Position::find()
						->where('`user_id`= '.\Yii::$app->user->id)
						->orderBy('`id` DESC')
						->limit(24)
						->all()
        ]);
    }
}
