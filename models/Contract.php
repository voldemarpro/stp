<?php
namespace app\models;

use Yii;
use yii\base\Model;

class Contract extends Model
{
    /**
     * @var string Trading contract id (1 (usd) for sota-1, 2 (brent) for sota-2)
     */
	public $id;
    
	/**
     * @var string Trading contract label
     */	
	public $label;
	
	/**
     * @var float Market bid price
     */		
	public $bid;
	/**
     * @var float Market ask price
     */		
	public $ask;
	/**
     * @var float Market close price
     */		
	public $close;
	
	
	/**
     * @var string Forex symbol as candlechart param
     */		
	public $fx_sym;	
	
	public static $variants = [
		[
			'id'     => 1,
			'label'  => 'USD / RUB',
			'fx_sym' => 'USDRUB',
			'bid'    => null,
			'ask'    => null,
			'close'  => null
		],
		
		[
			'id'    => 2,
			'label' => 'BRENT / USD',
			'fx_sym'=> 'CL_Brent',
			'bid'   => null,
			'ask'   => null,
			'close' => null
		],	
	];
	
	/**
     * @var array Tick codes for candlechart
     */		
	public static $ticks = [
		0 => '1 мин',
		1 => '5 мин',
		2 => '15 мин',
		3 => '1 час',
		4 => '1 день',
		5 => '1 нед'
	];
	
	public static $candleChartSrc = 'http://informers.forexpf.ru/php/graphics.php?sym={fx_sym}&vsz={vsz}&hsz={hsz}&tic={tick}&typ=2&sz=200&pass=923443';

	public function attributeLabels() {
		return [];
	}

	public function scenarios()
    {
		return [
            'default' => array_keys($this->attributes)
        ];
    }
	
    /**
     * Actual quotes
     *
     * @return array
     */	
	public static function getQuotes() {
		$isActive = time() >= \Yii::$app->params['open_time'];
		$isActive = $isActive && ((time() - \Yii::$app->params['close_time']) <= 4);
		$storageDir = \Yii::getAlias('@app/runtime/');
		$file = $storageDir.'contracts.dat';

		if (!file_exists($file))
			file_put_contents($file, '');
		
		$dt = time() - filemtime($file);

		if ($dt >= 5 && $isActive) {
			$quotes = [
				1 => [			
					'bid'    => null,
					'ask'    => null,
					'close'  => 57.735
				],
				
				2 => [			
					'bid'    => null,
					'ask'    => null,
					'close'  => 52.325
				]			
			];
			
			$quotesOld = @unserialize( file_get_contents( $file ) );
			$fp = fopen($file, 'w+');
			if (isset($quotesOld[1]))
				$quotes[1] = array_merge($quotes[1], $quotesOld[1]);
			if (isset($quotesOld[2]))
				$quotes[2] = array_merge($quotes[2], $quotesOld[2]);
			
			\exec('lynx -dump http://j1.forexpf.ru/delta/html/reloadquotepage.jsp', $rArray);	
			$response = implode(';', $rArray);

			if (!empty($response)) {
				foreach ($quotes as $i=>$q) {
					$pattern = $i == 1 
								 ? "/cs\(\'52\'\,([^\,]*)\,([^\,]*)\,/"
								 : "/cs\(\'104\'\,([^\,]*)\,([^\,]*)\,/";
					$match = \preg_match_all($pattern, $response, /*implode(';', $response), */$matches);
					
					if ($match) {
						$quotes[$i]['bid'] = round((float)trim($matches[1][0], "'"), 2);
						$quotes[$i]['ask'] = round((float)trim($matches[2][0], "'"), 2);
						if (time() >= \Yii::$app->params['close_time'])
							$quotes[$i]['close'] = round(($quotes[$i]['bid'] + $quotes[$i]['ask']) / 2, 3);
					}
				}
				
				flock($fp, LOCK_EX); // Блокирование файла для записи
				fwrite($fp, serialize($quotes));
				flock($fp, LOCK_UN); // Снятие блокировки
				fflush($fp);
				fclose($fp);
			}	
			
			return $quotes;		
		
		} else {
			return unserialize( file_get_contents( $storageDir.'contracts.dat' ) );
		}
	}
}
?>