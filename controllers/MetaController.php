<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Quotation;
use app\models\News;
use app\models\Trader;
use app\models\Position;
use app\models\Payout;

/**
 * Контроллер для исполнения фоновых задач
 */
class MetaController extends Controller
{

    public function actionIndex()
    {
		return $this->actionResetusdrub();
    }
	
	/**
     * Обновление котировок USD/RUB MOEX
     */
    public function actionResetusdrub()
    {
		$usdRub = Quotation::getBase();
		$quot = Quotation::findOne(Quotation::USD_RUB_MOEX);
		$quot->bid = (float)$usdRub['bid'];
		$quot->ask = (float)$usdRub['ask'];
		$quot->avg = ($quot->bid + $quot->ask)/2;
		$quot->date_time = date('Y-m-d H:i:s');
		
		$dt = \time() - Yii::$app->params['close_time'];
		if ($dt <= 5 && $dt >= 0)
			$quot->ref = $quot->avg; // реперная точка - время закрытия торгов

		$quot->diff = $quot->avg - (float)$quot->ref;
		
		echo (string)$quot->update();
    }
	
	/**
     * Удаление неактуальных новостей (давность более 7дн)
     */
    public function actionNewserase()
    {
		News::deleteAll('DATEDIFF(CURDATE(), `pub_date`) > 7');
	}
	
	/**
     * Обновление новостей (ограничение кол-ва новых записей за 1 запуск)
     */
    public function actionNewsupdate()
    {
		// Удаление неактуальных новостей (давность более 7дн)
		// News::deleteAll('DATEDIFF(CURDATE(), `pub_date`) > 7');
		
		$sources = [
			'http://www.forexpf.ru/fond.xml',
			'http://www.forexpf.ru/econom.xml'
		];
		foreach ($sources as $key=>$src) {
			
			$response = [];
			\exec("wget -O - $src", $response);
			
			if (!empty($response[0]) && \mb_strpos($response[0], '<xml', 0, 'utf-8') !== null) {
				$xml = new \SimpleXMLElement(\implode("\n", $response));
				$i = 0;
				if (!empty($xml->channel)) {
					foreach ($xml->channel->item as $it) {
						if ($event = News::find()->where(['hash' => \md5($it->link)])->one())
							continue;
						
						// Если новость не найдена, организуем ее запись в БД
						$event = new News;
						$event->pub_date = \date('Y-m-d', strtotime($it->pubDate));
						$event->hash = \md5($it->link);
						$event->link = $it->link;
						$event->title = $it->title;
						
						$matches = [];

						$preview = preg_replace('|<p>(.*)</p>|', '$1', $it->description);
						$preview = preg_replace('|<span(.*)</span>|', '', $preview);
						$preview = \strip_tags($preview);
						
						$mx_len = $key ? 300 : 200;
						$event->preview = (\mb_strlen($preview, 'utf-8') > $mx_len) ? \mb_substr($preview, 0, $mx_len-1, 'utf-8').'...' : $preview;						
	
						$arr = [];
						\exec('wget -O - '.$it->link, $arr);

						if ($content = implode("\n", $arr)) {
							\libxml_use_internal_errors(true);
							//$content = preg_replace('|<span(.*?)</span>|', '', $content);
							$dom = new \DOMDocument;
							$dom->loadHTML($content);
							$h2 = $dom->getElementsByTagName('h2');
							$header = $h2->item(0)->nodeValue;

							foreach($dom->getElementsByTagName('script') as $sc)
								$sc->parentNode->removeChild($sc);
									
							$divs = $dom->getElementsByTagName('div');
							for ($j = 0; $j < $divs->length; $j++)
								if ($divs->item($j)->hasAttribute('class'))
									if ($divs->item($j)->getAttribute('class') == 'news') {
										$content = $divs->item($j);
									}
							
							if ($content) {
								$_html = [];
								foreach ($content->getElementsByTagName('span') as $span)
									if (mb_strpos($span->textContent, 'По теме', 0, 'utf-8') === false)
										$span->parentNode->removeChild($span);
								
								foreach ($content->getElementsByTagName('a') as $a)
									if ($a->hasAttribute('class') && $a->getAttribute('class') == 'news')
										$a->parentNode->removeChild($a);									
								
								foreach ($content->getElementsByTagName('p') as $j=>$p) {
									if ($p->textContent && strlen($p->textContent) > 10 && mb_strpos($p->textContent, 'По теме', 0, 'utf-8') === false)
										$_html[] = $p->textContent;
									
									if ($j) {
										foreach ($p->getElementsByTagName('img') as $img)
											$_html[] = '
											<img src="http://www.forexpf.ru'.$img->getAttribute('src').'" class="pull-left clearfix" />';
									}
									
									if ($p->nextSibling) {
										$nS = $p->nextSibling;
										if ($nS->nodeName == '#text')
											$nS = $p->nextSibling->nextSibling;
										if ($nS && $nS->nodeName == 'ul') {
											foreach ($nS->childNodes as $i=>$ch)
												if (\trim($ch->textContent)) {
													$_html[count($_html) - 1] .=  ' '.$ch->textContent.',';
												}
											$_html[count($_html) - 1] = \rtrim($_html[count($_html) - 1], ',').'.';
										}
									}									
								}
								
								if ($p->parentNode)
									foreach ($p->parentNode->childNodes as $n)
										if ($n->nodeName == 'img') {
											$_html[] = '
											<img src="http://www.forexpf.ru'.$n->getAttribute('src').'" class="pull-left clearfix" />';								
											break;
										}

								$html = '<p>'.implode('</p><p>', $_html).'</p>';
							}
							
							$event->header = $header;
							$event->content = $html;
							$event->src = $key;
							
							//echo("$html<br><br>");
							
							echo (string)$event->save();
							
							$i++;
						}	
						
						if ($i > 2) break;
					}
				}
			}
		}
    }
	
	
	/**
	 * Автоматическое закрытие сделок
	 */
    public function actionClearing()
    {
		if ((time() - Yii::$app->params['close_time']) >= 5)
			return false;

		//открытые активные позиции
		$positions = Position::find()
			->where('DATE(`open_time`) = CURDATE()')
			->andWhere('`disabled` = 0')
			->andWhere('`close_time` IS NULL')
			->orderBy('`id`')
			->all();
		
		if ($positions) {
			
			$i = 0;
			$posGrouped = [];
			foreach ($positions as $pos)
				$posGrouped[$pos->user_id] = $pos;
			
			// трейдеры, имеющие незакрытые позиции
			$Traders = Trader::find()
				->where(['id'=>array_keys($posGrouped)])
				->indexBy('id')
				->all();
			
			// котировки при закрытии	
			$quot = Quotation::findOne(Quotation::USD_RUB_MOEX);
			
			$sms = new \app\components\Sms;
				
			foreach ($posGrouped as $pos) {
				$payout = new Payout;
				
				$user = $Traders[$pos->user_id];

				$pos->close_quot = $pos->type < 0 ? $quot['ask'] : $quot['bid'];
				$pos->close_time = date('Y-m-d H:i:s', Yii::$app->params['close_time']);

				if ($pos->type > 0)
					$pos->result = ($quot['bid'] - $pos->open_quot) * $pos->open_sum;
				else
					$pos->result = -($quot['ask'] - $pos->open_quot) * $pos->open_sum;
					
				if ($pos->result) {	
					$payout->user_id = $user->id;
					$payout->type = 0;					
					$payout->{'date'} = date('Y-m-d');					
					if ($pos->result > 0) {
						$user->debit += ((1 - $user->fee/100) * $pos->result);
						$payout->sum = (1 - $user->fee/100) * $pos->result;
					
					// If buy-out is off and the result is negative we charge user's sotacard 
					} elseif (!$user->opt) {
						$user->debit += $pos->result;
						$payout->sum = $pos->result;
					
					} else
						$payout->sum = 0;
				}
				
				$user->balance = $user->credit;	
				
				if ($pos->result < 0 && $user->opt)
					$pos->comment = 'sota-1';
				else
					$pos->comment = 'авто';
				
				$conn = \Yii::$app->db;
				$dbt = $conn->beginTransaction();
				
				try {
					$pos->save();
					$user->save();
					if ($payout->sum)
						$payout->save();
					
					$dbt->commit();
					
					if ($payout->sum) {
						$msg = $payout->sum > 0 ? 'Зачисление ' : 'Списание ';
						$sms->send('8'.$user->phone, $msg . number_format($payout->sum, 2, '.', ' ')." RUB\nSOTACARD", \Yii::$app->params['sms_sender']);
					}
					
					$i++;
				} catch (\Exception $e) {
					$dbt->rollBack();
					$pos->comment = "Ошибка $e->statusCode";
					$pos->save();
				}				
				
			}
			
			return $i;
		}
    }
	
	/**
	 * Автоматический перевод средств на торговый счет
	 */
    public function actionDebit_to_credit()
    {
		//открытые заявки на перевод средств с SOTACARD на торговый счет
		$items = \app\models\Request::find()
			->where('`aux` = 1')
			->andWhere('`status` = 0')
			->all();
		
		if ($items) {
			
			$i = 0;
			$users = [];
			foreach ($items as $it)
				$users[] = $it->user_id;
			
			// трейдеры
			$Traders = Trader::find()
				->where(['id'=>array_unique($users)])
				->indexBy('id')
				->all();

			$sms = new \app\components\Sms;
				
			foreach ($items as $it) {
				
				$user = $Traders[$it->user_id];
				
				if ($user->balance == $user->credit)
					$user->balance += $it->amount;
				
				$user->credit = $user->credit + $it->amount;
				$user->debit -= $it->amount;

				$payout = new Payout;
				$payout->sum = $it->amount;
				$payout->type = 1;
				$payout->user_id = $user->id;
				$payout->{'date'} = date('Y-m-d');

				$payout2 = new Payout;
				$payout2->sum = -1 * $it->amount;
				$payout2->type = 0;
				$payout2->user_id = $user->id;
				$payout2->{'date'} = date('Y-m-d');
				
				$it->status = 1;

				$conn = \Yii::$app->db;
				$dbt = $conn->beginTransaction();
				
				try {
					$it->save();
					$user->save();
					$payout->save();
					$payout2->save();
					
					$dbt->commit();

					$sms->send('8'.$user->phone, 'Перевод средств. Торговый счет. Одобрено', \Yii::$app->params['sms_sender']);
					
					$i++;
				} catch (\Exception $e) {
					$dbt->rollBack();
					$it->comment = "Ошибка $e->statusCode";
					$it->save();
				}				
				
			}
			
			return $i;
		}
    }
	
	/**
	 * Автоматический выкуп убытка на счете SOTACARD
	 */
    public function actionVoid()
    {
		// трейдеры
		$Traders = Trader::find()
			->where('`debit` < 0')
			->indexBy('id')
			->all();
		
		if ($Traders) {
			
			$sms = new \app\components\Sms;
			$phones = [];
			
			foreach ($Traders as $t) {
				$payout = new Payout;
				$payout->sum = abs($t->debit);
				$payout->type = 0;
				$payout->user_id = $t->id;
				$payout->{'date'} = date('Y-m-d');
				$t->debit = 0;				

				$dbt = \Yii::$app->db->beginTransaction();
				
				try {
					$t->save();
					$payout->save();
					
					$dbt->commit();
					
					$phones[] = '8'.$t->phone;

				} catch (\Exception $e) {
					$dbt->rollBack();
				}				
			}
			
			if ($phones) {
				$sms->send($phones, "Выкуп убытка\nSOTACARD", \Yii::$app->params['sms_sender']);
				return count($phones);
			}
		}
    }
	
	/**
	 * Статистика по сделкам на конец каждого торгового дня
	 */
    public function actionSummarize()
    {
		// трейдеры
		$Traders = Trader::find()
			->indexBy('id')
			->all();
		
		if ($Traders) {
			
			// сделки
			$positions = Position::find()
				->where("`open_time` >= '".date('Y-m-01')."' AND `disabled` = 0")
				->all();
			
			$posStat = [];
			foreach ($positions as $pos) {
				if (empty($posStat[$pos->user_id]))
					$posStat[$pos->user_id] = [1, ($pos->result > 0 ? 1 : 0)];
				else {
					$posStat[$pos->user_id][0] = $posStat[$pos->user_id][0] + 1;
					if ($pos->result > 0)
						$posStat[$pos->user_id][1] = $posStat[$pos->user_id][1] + 1;
				}
			}
			
			foreach ($Traders as $t) {
				$t->stat = isset($posStat[$t->id]) ? implode(',', $posStat[$t->id]) : '0,0';
				$t->save();				
			}
		}
    }
}
