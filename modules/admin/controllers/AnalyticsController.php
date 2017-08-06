<?php
namespace app\modules\admin\controllers;

use Yii;
use app\modules\admin\components\MainController;
use app\models\Traider;
use app\models\Position;
use app\models\Payout;
use yii\data\ActiveDataProvider;

/**
 * Analytics
 */
class AnalyticsController extends MainController
{
	/**
	 * @param  string  $date  Date of traiding session
	 * @param  string  $t1    Start time point (%) of traiding session
	 * @param  string  $t2    End time point (%) of traiding session
	 */	
	public function actionIndex($date = '', $t1 = false, $t2 = false)
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
			if ($t1 !== false || $t2 !== false) {
				$t1 = $t1 === false ? 0 : (int)$t1;
				$t2 = $t2 === false ? 100 : (int)$t2;
				if ($t1 >= 0 && $t1 < $t2 && $t2 <= 100) {
					$timeOrigin += round($t1 / 100 * $timeRangeMax);
					$timeRangeMax = round(($t2 - $t1) / 100 * $timeRangeMax);
				}
			}
			
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
		
		if ($t1 !== false && $t2 !== false) {		
			return $this->renderPartial('index_partial', [
				'stat' => $stat,
				'times'=>$keys,
				'date'=>$date
			]);
		} else
			return $this->render('index', [
				'stat' => $stat,
				'times'=>$keys,
				'date'=>$date,
				'sessionTimes'=>[\Yii::$app->params['open_time'] - $dtOffsetExtra, \Yii::$app->params['close_time'] - $dtOffsetExtra]
			]);
    }
	
	/**
	 * Computes total payment due to traders
	 */	
	public function actionInvoice($date = '')
    {
		$query = Payout::find()->where('`type` = 0');
		
		if ($date) {
			$query = $query->andWhere("`date` <= '".date('Y-m-d', strtotime($date))."'");
			$date = date('d.m.Y', strtotime($date));
		} else {
			$query = $query->andWhere("`date` <= '".date('Y-m-d')."'");
			$date = date('d.m.Y');
		}

		$items = $query->all();
		
		// admins and traders with risk buy-out
		$starUsers = Traider::find()->where('`opt` = 1 OR (`grade` & 1024) != 0')->indexBy('id')->all();
		
		$stat = [];
		$stat2 = [];
		
		$minCorr = 0;
		$minCorr2 = 0;
		
		if ($items) {
			foreach ($items as $it) {
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

			foreach ($stat as $uid=>$val) {
				if ($val < 0)
					unset($stat[$uid]);
				elseif ($val < \Yii::$app->params['payout_minimum'])
					$minCorr -= $val;
				
			}
			
			foreach ($stat2 as $uid=>$val) {
				if ($val < 0)
					unset($stat2[$uid]);
				elseif ($val < \Yii::$app->params['payout_minimum'])
					$minCorr2 -= $val;					
			}
		}
		
		return $this->render('invoice', [
			'date' => $date,
			'stat'=>array_map('round', [array_sum($stat), array_sum($stat2), $minCorr, $minCorr2])
		]);	
	}
}
