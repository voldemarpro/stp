<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Contract;
use app\models\News;
use app\models\Trader;
use app\models\Position;
use app\models\MoneyTransfer;

/**
 * Контроллер для исполнения фоновых задач
 */
class MetaController extends Controller
{

    public function actionIndex()
    {
		return true;
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
	 * Автоматическое закрытие сделок и сальдирование
	 */
    public function actionClearing()
    {
		if (abs(time() - Yii::$app->params['close_time']) > 9)
			return false;

		//открытые активные позиции
		$positions = Position::find()
			->where('DATE(`open_time`) = CURDATE()')
			->andWhere('`close_time` IS NULL')
			->orderBy('`id`')
			->all();
		if ($positions) {
			$i = 0;
			$posGrouped = [];
			foreach ($positions as $pos)
				$posGrouped[$pos->user_id] = $pos;
			
			// трейдеры, имеющие незакрытые позиции
			$traders = Trader::find()
				->where(['id'=>array_keys($posGrouped)])
				->indexBy('id')
				->all();
			
			// котировки при закрытии	
			$quotes = Contract::getQuotes();
			$quot = $quotes[STP_VRS];
			
			if (!$quot)
				return false;

			foreach ($posGrouped as $pos) {
				$u = $traders[$pos->user_id];
				$u->balance = $u->credit;
				$pos->comment = 'sota-'.STP_VRS;
				$pos->close_quot = $pos->type < 0 ? $quot['ask'] : $quot['bid'];
				$pos->close_time = date('Y-m-d H:i:s');
				if ($pos->type > 0)
					$pos->result = ($quot['bid'] - $pos->open_quot) * $pos->volume;
				else
					$pos->result = -($quot['ask'] - $pos->open_quot) * $pos->volume;
				
				$conn = \Yii::$app->db;
				$dbt = $conn->beginTransaction();
				
				try {
					$pos->save();
					$u->save();
					$dbt->commit();
					$i++;
				} catch (\Exception $e) {
					$dbt->rollBack();
					$pos->comment = "Ошибка $e->statusCode";
					$pos->save();
				}				
			}
			
			print "$i position(s) closed";
		}
		
		
		// трейдеры с тарифами для начисления прибыли
		$traders = Trader::find()
			->where([ 'tariff_id'=>[1,2] ])
			->indexBy('id')
			->all();
		if ($traders) {
			$j = 0;
			$positions = Position::find()
				->where(['user_id'=>array_keys($traders)])
				->andWhere('DATE(`close_time`) = CURDATE()')
				->all();
			$moneyTransfers = MoneyTransfer::find()
				->where(['user_id'=>array_keys($traders)])
				->andWhere('DATE(`date_time`) = CURDATE()')
				->andWhere('rec_type = 0')
				->indexBy('user_id')
				->all();				
			$sms = new \app\components\Sms;			
			
			foreach ($positions as $pos) {
				if (isset($moneyTransfers[$pos->user_id]))
					continue;
				
				$moneyTransfer = new MoneyTransfer;		
				$u = $traders[$pos->user_id];
	
				if ($pos->result) {
					$moneyTransfer->user_id = $u->id;
					$moneyTransfer->rec_type = 0;					
					$moneyTransfer->{'date_time'} = date('Y-m-d H:i:s');					
					if ($pos->result > 0) {
						$u->debit += ((1 - $u->fee/100) * $pos->result);
						$moneyTransfer->sum = (1 - $u->fee/100) * $pos->result;
					
					// If buy-out is off and the result is negative we charge user's sotacard 
					} elseif ($u->tariff_id == 1) {
						$u->debit += $pos->result;
						$moneyTransfer->sum = $pos->result;
					
					} else
						$moneyTransfer->sum = 0;
				}

				$conn = \Yii::$app->db;
				$dbt = $conn->beginTransaction();
				
				try {
					$u->save();
					if ($moneyTransfer->sum)
						$moneyTransfer->save();
					
					$dbt->commit();
					
					/*if ($moneyTransfer->sum) {
						$msg = $moneyTransfer->sum > 0 ? 'Зачисление ' : 'Списание ';
						$sms->send('8'.$u->phone, $msg . number_format($moneyTransfer->sum, 2, '.', ' ')." RUB\nSOTACARD", \Yii::$app->params['sms_sender']);
					}*/
					
					$j++;
				
				} catch (\Exception $e) {
					$dbt->rollBack();
				}					
			}
			
			print "$j transfer(s) completed";
		}
    }
	
	/**
	 * Перевод средств на торговый счет
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

				$MoneyTransfer = new MoneyTransfer;
				$MoneyTransfer->sum = $it->amount;
				$MoneyTransfer->rec_type = 1;
				$MoneyTransfer->user_id = $user->id;
				$MoneyTransfer->{'date_time'} = date('Y-m-d H:i:s');

				$MoneyTransfer2 = new MoneyTransfer;
				$MoneyTransfer2->sum = -1 * $it->amount;
				$MoneyTransfer2->rec_type = 0;
				$MoneyTransfer2->user_id = $user->id;
				$MoneyTransfer2->{'date_time'} = date('Y-m-d H:i:s');
				
				$it->status = 1;

				$conn = \Yii::$app->db;
				$dbt = $conn->beginTransaction();
				
				try {
					$it->save();
					$user->save();
					$MoneyTransfer->save();
					$MoneyTransfer2->save();
					
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
	 * Выкуп убытка на счете SOTACARD
	 */
    public function actionVoid_debit()
    {
		// трейдеры
		$traders = Trader::find()
			->where('`debit` < 0')
			->indexBy('id')
			->all();
		
		if ($traders) {
			
			$sms = new \app\components\Sms;
			$phones = [];
			
			foreach ($traders as $t) {
				$moneyTransfer = new MoneyTransfer;
				$moneyTransfer->sum = abs($t->debit);
				$moneyTransfer->rec_type = 2;
				$moneyTransfer->user_id = $t->id;
				$moneyTransfer->{'date_time'} = date('Y-m-d H:i:s');
				$t->debit = 0;				

				$dbt = \Yii::$app->db->beginTransaction();
				
				try {
					$t->save();
					$moneyTransfer->save();
					
					$dbt->commit();
					
					$phones[] = '8'.$t->phone;

				} catch (\Exception $e) {
					$dbt->rollBack();
				}				
			}
			
			if ($phones) {
				//$sms->send($phones, "Выкуп убытка\nSOTACARD", \Yii::$app->params['sms_sender']);
				return count($phones);
			}
		}
    }
}
