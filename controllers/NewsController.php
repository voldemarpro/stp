<?php
namespace app\controllers;

use Yii;
use app\components\MainController;
use app\models\News;
use yii\data\ActiveDataProvider;

/**
 * Контроллер для отображения новостей
 */
class NewsController extends MainController
{
    public function actionIndex($id = 0)
    {
        if ($id = intval($id)) {
			if ($item = News::findOne($id)) {
				return $this->render('index', [
					'item' => $item
				]);				
			}
			
		} else {
			$provider = new ActiveDataProvider([
				'query' => News::find()->orderBy(['pub_date'=>SORT_DESC, 'src' => SORT_ASC]),
				'totalCount' => 50,
				'pagination' => [
					'pageSize' => 10
				]
			]);
			return $this->render('index', [
				'items' => $provider->getModels(),
				'pagination'=>$provider->pagination
			]);
		}
    }
}
