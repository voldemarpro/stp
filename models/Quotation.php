<?php
namespace app\models;

use Yii;
use yii\db\Command;
use yii\db\ActiveRecord;

class Quotation extends ActiveRecord
{
	const USD_RUB_CBRF = 1; // usd/rub по курсу ЦБ
    const USD_RUB_MOEX = 2; // usd/rub по курсу MOEX
	
	const BRENT_ICE = 3; // нефть brent по курсу ICE
	
	public static $candles = [
		1 => [
			'title'=> '1 мин',
			'usdrub'  => 'http://informers.forexpf.ru/php/graphics.php?sym=USDRUB&vsz=500&hsz=1100&tic=0&typ=2&sz=200&pass=923443',
			//'usdrub'  => 'http://j1.forexpf.ru/delta/prochart?type=USDRUB&amount=335&chart_height=500&chart_width=1100&grtype=2&tictype=0',
			'brent'=> 'http://informers.forexpf.ru/php/graphics.php?sym=CL_Brent&vsz=500&hsz=1100&tic=0&typ=2&sz=200&pass=923443'
		],

		2 => [
			'title'=> '5 мин',
			'usdrub'  => 'http://informers.forexpf.ru/php/graphics.php?sym=USDRUB&vsz=500&hsz=1100&tic=1&typ=2&sz=200&pass=923443',
			'brent'=> 'http://informers.forexpf.ru/php/graphics.php?sym=CL_Brent&vsz=500&hsz=1100&tic=1&typ=2&sz=200&pass=923443'
		],
		
		3 => [
			'title'=> '15 мин',
			'usdrub' => 'http://informers.forexpf.ru/php/graphics.php?sym=USDRUB&vsz=500&hsz=1100&tic=2&typ=2&sz=200&pass=923443',
			'brent'=> 'http://informers.forexpf.ru/php/graphics.php?sym=CL_Brent&vsz=500&hsz=1100&tic=2&typ=2&sz=200&pass=923443'
		],
		
		4 => [
			'title'=> '1 час',
			'usdrub' => 'http://informers.forexpf.ru/php/graphics.php?sym=USDRUB&vsz=500&hsz=1100&tic=3&typ=2&sz=200&pass=923443',
			'brent'=> 'http://informers.forexpf.ru/php/graphics.php?sym=CL_Brent&vsz=500&hsz=1100&tic=3&typ=2&sz=200&pass=923443'
		],
		
		5 => [
			'title'=> '1 день',
			'usdrub' => 'http://informers.forexpf.ru/php/graphics.php?sym=USDRUB&vsz=500&hsz=1100&tic=4&typ=2&sz=200&pass=923443',
			'brent'=> 'http://informers.forexpf.ru/php/graphics.php?sym=CL_Brent&vsz=500&hsz=1100&tic=4&typ=2&sz=200&pass=923443'
		],
		
		6 => [
			'title'=> '1 нед',
			'usdrub' => 'http://informers.forexpf.ru/php/graphics.php?sym=USDRUB&vsz=500&hsz=1100&tic=5&typ=2&sz=200&pass=923443',
			'brent'=> 'http://informers.forexpf.ru/php/graphics.php?sym=CL_Brent&vsz=500&hsz=1100&tic=5&typ=2&sz=200&pass=923443'
		]
	];
	
	/**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%quotations}}';
    }
	

	public function attributeLabels() {
		return [];
	}

	public function scenarios()
    {
        return [
            'default' => ['date_time', 'bid', 'ask', 'avg', 'diff', 'ref']
        ];
    }
	
    /**
     * Обновление состояния текущих котировок USD-ЦБ/BRENT для "шапки" страницы
     *
     * @return array
     */	
    public static function getheaderquotes()
    {
		$state = [];
		
		if ($quotArr = self::find()->all()) {
			foreach ($quotArr as $q) {
				if ($q->id == self::USD_RUB_MOEX)
					continue;
				
				$state[$q->name] = ['avg'=>$q->avg, 'diff'=>$q->diff];
				if ($q->avg && $q->diff) {
					$q->ref = $q->ref ? $q->ref : $q->avg;
					$state[$q->name]['diff'] = ($q['ref']/($q['ref'] - $q['diff']) - 1) * 100;
				}
			}
		}
		
		return $state;
    }
	
    /**
     * Actual USD/RUB MOEX quotations
     *
     * @return array
     */	
	public static function getBase() {
		
		$rtDir = \Yii::getAlias('@app/runtime/');
		
		$isActive = time() >= \Yii::$app->params['open_time'];
		$isActive = $isActive && ((time() - \Yii::$app->params['close_time']) <= 4);
		
		$dt = time() - filemtime($rtDir.'usdrub.dat');
		
		/*if (!file_exists($rtDir.'usdrub.dat'))
			file_put_contents($rtDir.'usdrub.dat', '');*/

		if ($dt >= 5 && $isActive) {
			$response = file_get_contents('http://qt.atsystems.online');
			if (!empty($response)/* && is_array($response)*/) {
				if (\preg_match_all("/cs\(\'52\'\,([^\,]*)\,([^\,]*)\,/", $response, /*implode(';', $response), */$matches)) {
					$fp = fopen($rtDir.'usdrub.dat', 'w+');
					$content = fgets($fp);
					
					if ($content) {
						$arr0 = json_decode(fgets($fp));
						$ref = $arr0['ref'];
					}
					
					if (!$content || !$ref || $dt >= 3600) {
						$quot = Quotation::findOne(Quotation::USD_RUB_MOEX);
						$ref = $quot->ref;
					}
					
					$arr = [
						'bid'=>trim($matches[1][0], "'"),
						'ask'=>trim($matches[2][0], "'"),
						'date_time' => date('Y-m-d H:i:s'),
						'ref' => $ref
					];
					$arr['avg'] = ((float)$arr['bid'] + (float)$arr['ask']) / 2;

					flock($fp, LOCK_EX); // Блокирование файла для записи
					fwrite($fp, json_encode($arr));
					flock($fp, LOCK_UN); // Снятие блокировки
					fflush($fp);
					fclose($fp);
					
					return $arr;
				}
			}			
		} else {
			return json_decode(file_get_contents($rtDir.'usdrub.dat'), true);
		}
	}
}
?>