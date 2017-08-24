<?php
namespace app\controllers;

use Yii;
use app\components\MainController;
use app\models\Trader;
use app\models\Position;
use app\models\Quotation;
use app\models\MoneyTransfer;
use app\models\News;

/**
 * Торговая площадка. Открытие/закрытие сделок
 */
class TradingController extends MainController
{
    public function actionIndex()
    {
        return $this->render('index', [
            'state' => Trader::getState(),
			'news' => News::find()->orderBy(['pub_date'=>SORT_DESC, 'src' => SORT_ASC])->limit(4)->all()
        ]);
    }
	
    public function actionGetstate()
    {
        return \json_encode(Trader::getState(), JSON_FORCE_OBJECT);
    }
	
    public function actionUpdate()
    {
        return $this->renderPartial('index', [
            'state' => Trader::getState(),
			'news' => News::find()->orderBy(['pub_date'=>SORT_DESC, 'src' => SORT_ASC])->limit(4)->all()
        ]);
    }
	
	// Покупка
	public function actionBuy() {
		return $this->actionSetposition(1);
	}
	
	// Продажа
	public function actionSell() {
		return $this->actionSetposition(-1);
	}
	
	// Открытие/закрытие позиции
	public function actionSetposition($type)
    {
		if (!isset(Position::$types[$type]))
			return false;
		
		$state = Trader::getState();

		if ($state['allowOpen'])
			return $this->actionOpen($state, $type);
		elseif ($state['allowClose'])
			return $this->actionClose($state);
		else
			return false;
    }

	// Открытие позиции
    private function actionOpen($state, $type)
    {
		if (!empty($state)) {
			if ((time() - \strtotime($state['quot']['date_time'])) > 4) {
				Yii::$app->runAction('meta/resetusdrub');			
				$state['quot'] = Quotation::findOne(Quotation::USD_RUB_MOEX);
			}
			header('Content-type: text/html');

			$user = Yii::$app->user->identity;
			
			$pos = new Position();
			
			$pos->user_id = $user->id;
			$pos->type = $type;
			$pos->open_quot = $type == 1 ? $state['quot']['ask'] : $state['quot']['bid'];
			
			// Открываем позицию на всю сумму
			$rawSum = $state['balance']/($pos->open_quot);
			
			$user->balance = $state['balance'] - floor($rawSum) * $pos->open_quot;
			
			$pos->open_sum = floor($rawSum);
			$pos->open_time = date('Y-m-d H:i:s');
			
			$conn = \Yii::$app->db;
			$dbt = $conn->beginTransaction(); 

			try {
				$pos->save();
				$user->save();
				$dbt->commit();
				
				return $this->renderPartial('index', [
					'state' => Trader::getState(),
					'news' => News::find()->orderBy(['pub_date'=>SORT_DESC, 'src' => SORT_ASC])->limit(4)->all()
				]);
			
			} catch (\Exception $e) {
				$dbt->rollBack();
				throw $e;
			}			
		}
    }
	
	// Закрытие позиции
    private function actionClose($state)
    {
		if (!empty($state)) {
			$user = Yii::$app->user->identity;
			$type = $state['currentPosition']['type'] > 0 ? -1 : 1;
					
			$quot = Quotation::getBase();
			
			$pos = Position::findOne($state['currentPosition']['id']);
			$pos->close_quot = $type == 1 ? $quot['ask'] : $quot['bid'];
			$pos->close_time = date('Y-m-d H:i:s');
			
			if ($state['currentPosition']['type'] > 0)
				$pos->result = ($quot['bid'] - $pos->open_quot) * $pos->open_sum;
			else
				$pos->result = -($quot['ask'] - $pos->open_quot) * $pos->open_sum;
				
			if ($pos->result) {
				$MoneyTransfer = new MoneyTransfer;
				$MoneyTransfer->user_id = $user->id;
				$MoneyTransfer->rec_type = 0;
				$MoneyTransfer->{'date_time'} = date('Y-m-d H:i:s');	
				
				if ($pos->result > 0) {
					$user->debit += (1 - $user->fee/100) * $pos->result;
					$MoneyTransfer->sum = (1 - $user->fee/100) * $pos->result;
				} else {
					$user->debit += $pos->result;
					$MoneyTransfer->sum = $pos->result;
				}
			}
			
			$user->balance = $user->credit;
			
			$conn = \Yii::$app->db;
			$dbt = $conn->beginTransaction();

			try {
				$pos->save();
				$user->save();
				if (!empty($MoneyTransfer))
					$MoneyTransfer->save();
				
				$dbt->commit();
				
				if (!empty($MoneyTransfer)) {
					$msg = $MoneyTransfer->sum > 0 ? 'Зачисление ' : 'Списание ';
					$sms = new \app\components\Sms;
					$sms->send('8'.$user->phone, $msg.(number_format(abs($MoneyTransfer->sum), 2, '.', ' '))." RUB\nSOTACARD", \Yii::$app->params['sms_sender']);
				}
				
				return $this->renderPartial('index', [
					'state' => Trader::getState(),
					'news' => News::find()->orderBy(['pub_date'=>SORT_DESC, 'src' => SORT_ASC])->limit(4)->all()
				]);
			
			} catch (\Exception $e) {
				$dbt->rollBack();
				throw $e;
			}			
		}
    }
	
	// Текущие котировки
	public function actionGetquotes() {
		return json_encode(Quotation::getbase());
	}
}