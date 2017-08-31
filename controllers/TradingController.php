<?php
namespace app\controllers;

use Yii;
use app\components\MainController;
use app\models\Position;
use app\models\News;

/**
 * Торговая площадка. Открытие/закрытие сделок
 */
class TradingController extends MainController
{
    public function actionIndex()
    {
		return $this->render('index', [
			'news' => News::find()
						->orderBy(['pub_date'=>SORT_DESC, 'src' => SORT_ASC])
						->limit(4)
						->all()
        ]);
    }

    public function actionNews()
    {
        return $this->renderPartial('news', [
			'items' => News::find()
						->orderBy(['pub_date'=>SORT_DESC, 'src' => SORT_ASC])
						->limit(4)
						->all()
        ]);
    }
	
	// Покупка
	public function actionBuy() {
		if (Position::validateOpen()) {
			return (int)((new Position())->open(Position::BUY_ID));
		
		} elseif ($p = Position::validateClose()) {
			if ($p->type == -1)
				return (int)$p->close();
			else
				return false;
		} else
			return false;
	}
	
	// Продажа
	public function actionSell() {
		if (Position::validateOpen()) {
			return (int)((new Position())->open(Position::SELL_ID));
		
		} elseif ($p = Position::validateClose()) {
			if ($p->type == 1)
				return (int)$p->close();
			else
				return false;
		} else
			return false;
	}
}