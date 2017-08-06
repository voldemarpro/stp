<?php
namespace app\modules\admin\controllers;

use Yii;
use app\modules\admin\components\MainController;
use app\modules\admin\models\Param;
use app\models\Traider;
use app\models\Position;
use app\models\Payout;
use yii\data\ActiveDataProvider;

/**
 * Management of traders positions
 */
class PositionsController extends MainController
{	
	public function actionIndex($for = 0, $date = false)
    {
        if ($f = intval($for)) {
			if ($f < 0) {
				$sqlWhere = [
					-1 => 't.deposit = 0',
					-2 => 't.deposit != 0',
					-3 => '`disabled` != 0'
				];
				if ($f != -3)
					$query = Position::find()->innerJoin(Traider::tableName().' t', 'user_id = t.id')->where($sqlWhere[$f]);
				else
					$query = Position::find()->where($sqlWhere[$f]);
			} else {
				$query = Position::find()->where("`user_id` = $f");
			}

		} else {
			$query = Position::find();
		}
		
		if ($date)
			$query = $query->andWhere("DATE(`open_time`) = '".date('Y-m-d', strtotime($date))."'");
		elseif ($f < 0)
			$query = $query->andWhere("DATE(`open_time`) >= '".date('Y-m-d', time() - 30*24*60*60)."'");
		
		$query = $query->orderBy(['id' => SORT_DESC]);
		
		$provider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'pageSize' => 40
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
			'filter'=>$f,
			'date'=>$date
		]);
    }
	
	public function actionCancel()
    {
		if (isset($_POST['items']) && isset($_POST['comment']))
			Position::updateAll(['disabled' => 1, 'comment'=>$_POST['comment']], ['id'=>array_map('intval', $_POST['items'])]);
		
		if (isset($_POST['_referrer']))
			return $this->redirect($_POST['_referrer']);
    }


	/**
	 * Summary (analytics)
	 *
	 * @param  string  $date  Date of traiding session
	 */	
	public function actionSummary($date = '')
	{
		$query = Position::find();

		if ($date)
			$query = $query->where("DATE(`open_time`) = '".date('Y-m-d', strtotime($date))."'");
		else {
			$query = $query->where("DATE(`open_time`) = '".date('Y-m-d')."'");
			$date = date('d.m.Y');
		}
		
		$items = $query->all();
		
		// резюме по кол-ву, типу котировкам сделок
		// position summary as to qty, type and quotations
		$stat = [
			'COUNT'=>0,
			'OPEN'=>0,
			
			'LONG'=>0,
			'LONG_RATE'=>0,
			'LONG_QUOT' => [0=>[], 1=>[]],

			'SHORT'=>0,			
			'SHORT_RATE'=>0,
			'SHORT_QUOT' => [0=>[], 1=>[]],
			
			'LONG_FAILURE'=>0,
			'SHORT_FAILURE'=>0
		];
		
		// массив значений времени с интервалом 1 мин
		// для построения диаграммы
		$keys = [];
		
		// искуственный сдвиг по времени для нерабочих дней
		$dtOffsetExtra = Yii::$app->params['time_offset'];			
		
		if (count($items)) {

			// учет временной зоны (пересчет на мск)
			$dtOffset = strtotime($date) - \Yii::$app->params['dto'];
			
			// traiding day start unix-time in sec (date subtracted)
			$timeOrigin = \Yii::$app->params['open_time'] - $dtOffsetExtra - (strtotime(date('Y-m-d')) - \Yii::$app->params['dto']);
			
			// trading session duration in sec
			$timeRangeMax = \Yii::$app->params['close_time'] - \Yii::$app->params['open_time'];
			
			foreach ($items as $it) {

				if ((strtotime($it->open_time) - $dtOffset) < $timeOrigin)
					continue;
				
				if ((strtotime($it->open_time) - $dtOffset) > ($timeOrigin + $timeRangeMax))
					continue;
					
				/*if ((strtotime($it->close_time) - $dtOffset) > ($timeOrigin + $timeRangeMax))
					continue;*/
				
				$stat['COUNT']++;
					
				$openTime = date('G:i', strtotime($it->open_time) + \Yii::$app->params['dto']);
				$closeTime = date('G:i', strtotime($it->close_time) + \Yii::$app->params['dto']);
								
				if ($it->type > 0) {
					$stat['LONG']++;
					$stat['LONG_QUOT'][0][$openTime] = $it->open_quot;
					
					if ($it->close_time) {
						$stat['LONG_QUOT'][1][$closeTime] = $it->close_quot;
						if ($it->result < 0)
							$stat['LONG_FAILURE']++;
					}
						
				} else {
					$stat['SHORT']++;
					$stat['SHORT_QUOT'][0][$openTime] = $it->open_quot;
					
					if ($it->close_time) {
						$stat['SHORT_QUOT'][1][$closeTime] = $it->close_quot;
						if ($it->result < 0)
							$stat['SHORT_FAILURE']++;
					}
				}
				
				if (!$it->close_time)
					$stat['OPEN']++;
			}
			
			for ($i = 0; $i <= ceil($timeRangeMax/60); $i++) {				
				$keys[] = date('G:i', $timeOrigin + $i*60);			
			}
			
			foreach ($keys as $k) {
				if (empty($stat['LONG_QUOT'][0][$k]))
					$stat['LONG_QUOT'][0][$k] = 'null';
				if (empty($stat['SHORT_QUOT'][0][$k]))
					$stat['SHORT_QUOT'][0][$k] = 'null';
				if (empty($stat['LONG_QUOT'][1][$k]))
					$stat['LONG_QUOT'][1][$k] = 'null';
				if (empty($stat['SHORT_QUOT'][1][$k]))
					$stat['SHORT_QUOT'][1][$k] = 'null';						
			}

			ksort($stat['LONG_QUOT'][0], SORT_NATURAL);
			ksort($stat['LONG_QUOT'][1], SORT_NATURAL);
			ksort($stat['SHORT_QUOT'][0], SORT_NATURAL);
			ksort($stat['SHORT_QUOT'][1], SORT_NATURAL);
			
			if ($stat['COUNT']) {
				$stat['LONG_RATE'] = round($stat['LONG'] / $stat['COUNT'] * 100);
				$stat['SHORT_RATE'] = round($stat['SHORT'] / $stat['COUNT'] * 100);
			}
		}
		
		return $this->render('index', [
			'stat' => $stat,
			'times'=>$keys,
			'date'=>$date,
			'sessionTimes'=>[\Yii::$app->params['open_time'] - $dtOffsetExtra, \Yii::$app->params['close_time'] - $dtOffsetExtra]
		]);
	}

	/**
	 * Computes total payment due to trader(s)
	 */	
	public function actionInvoice($date = '', $uid = 0)
	{
		$query = Payout::find()->where('`type` = 0');
		$posByDay = [];
		
		if ($date) {
			$dt = strtotime($date);
			$query = $query->andWhere("`date` <= '".date('Y-m-d', $dt)."' AND `date` >= '".date('Y-m-01', $dt)."'");
			$date = date('d.m.Y', $dt);
			$m = date('m', $dt);
		} else {
			$query = $query->andWhere("`date` <= '".date('Y-m-d')."' AND `date` >= '".date('Y-m-01')."'");
			$date = date('d.m.Y');
			$dt = time();
			$m = date('m');
		}
		
		if ($uid) {
			$query = $query->andWhere("`user_id` = '".htmlspecialchars($uid)."'");
			$user = Traider::findOne($uid);
			$starUsers = [];
			if ($user) {
				if ($user->opt == 1 || ($user->grade & 1024))
					$starUsers[$user->id] = $user;
				$positions = Position::find()->where("user_id = {$user->id} && `open_time` >= ".date('Y-m-01', $dt)." && month(`open_time`) = ".intval($m))->all();
				$countItems = count($positions);
			}
			
		} else {
			$user = false;
			// admins and traders with risk buy-out
			$starUsers = Traider::find()->where('`opt` = 1 OR (`grade` & 1024) != 0')->indexBy('id')->all();			
			$positions = Position::find()->where("`open_time` >= ".date('Y-m-01', $dt)." && month(`open_time`) = ".intval($m))->all();		
		}
		
		foreach ($positions as $p) {
			$ct = explode('-', $p['open_time']);
			$posByDay[(int)$ct[2]] = 1;
		}		

		$items = $query->all();

		$stat = [];
		$stat2 = [];
		
		$minCorr = 0;
		$minCorr2 = 0;
		
		$countGain = 0;

		if ($items) {
			foreach ($items as $it) {
				
				// Check if the transfer originates from position
				$itDate = explode('-', $it->{'date'});
				if (!isset($posByDay[(int)$itDate[2]]))
					continue;
				
				// If some trader is selected
				if ($user) {
					if ($itDate[1] == $m) {
						if ($it->sum > 0)
							$countGain++;
					} else
						continue;
				}
				
				if (isset($starUsers[$it->user_id])) {
					if ($starUsers[$it->user_id]['grade'] >= 1024)
						continue;
					
					if (!isset($stat2[$it->user_id]))
						$stat2[$it->user_id] = $it->sum;
					else
						$stat2[$it->user_id] += $it->sum;					
				
				} else {
					if (!isset($stat[$it->user_id]))
						$stat[$it->user_id] = $it->sum;
					else
						$stat[$it->user_id] += $it->sum;
				}
			}
			

			foreach ($stat as $u_id=>$val) {
				if ($val < 0)
					unset($stat[$u_id]);
				elseif ($val < \Yii::$app->params['payout_minimum'])
					$minCorr -= $val;
			}
			
			foreach ($stat2 as $u_id=>$val) {
				if ($val < 0)
					unset($stat2[$u_id]);
				elseif ($val < \Yii::$app->params['payout_minimum'])
					$minCorr2 -= $val;					
			}
			
			if ($user) {
				$user->stat = "{$countGain},$countItems";
			}
		}
		
		return $this->render('invoice', [
			'date' => $date,
			'user' => $user,
			'stat'=>array_map('round', [array_sum($stat), array_sum($stat2), $minCorr, $minCorr2])
		]);
	}
}