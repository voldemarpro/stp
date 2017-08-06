<?php
namespace app\models;

use Yii;
use yii\db\Command;
use yii\db\ActiveRecord;

class Quote extends ActiveRecord
{
    const USD_RUB = 1; // usd/rub по курсу MOEX
	const BRENT = 2; // нефть brent по курсу ICE
	
	public static $candles = [
		1 => [
			'title'=> '1 мин',
			'usdrub'  => 'http://informers.forexpf.ru/php/graphics.php?sym=USDRUB&vsz=500&hsz=1100&tic=0&typ=2&sz=200&pass=923443',
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
        return '{{%quotes}}';
    }
	

	public function attributeLabels() {
		return [];
	}

	public function scenarios()
    {
        // дата обновления, покупка, продажа, среднее (цб), цена закрытия
		return [
            'default' => ['date_time', 'bid', 'ask', 'avg', 'yclose']
        ];
    }
	
    /**
     * Actual quotes
     *
     * @return array
     */	
	public static function getBase() {
		
		$rtDir = \Yii::getAlias('@app/runtime/');
		$isActive = time() >= \Yii::$app->params['open_time'];
		$isActive = $isActive && ((time() - \Yii::$app->params['close_time']) <= 4);
		$file = STP_VRS == 1 ? $rtDir.'usdrub.dat' : $rtDir.'brent.dat';

		/*
		if (!file_exists($file))
			file_put_contents($file, '');
		*/
		
		$dt = time() - filemtime($file);

		if ($dt >= 5 && $isActive) {
			$response = file_get_contents('http://qt.atsystems.online');
			if (!empty($response)) {
				$pattern = "/cs\(\'52\'\,([^\,]*)\,([^\,]*)\,/"; // 104 for brent
				$match = \preg_match_all($pattern, $response, /*implode(';', $response), */$matches);
				if ($match) {
					$fp = fopen($file, 'w+');
					$content = fgets($fp);
					
					if ($content) {
						$arr0 = json_decode(fgets($fp));
						$ref = $arr0['ref'];
					}
					
					if (!$content || !$ref || $dt >= 3600) {
						$quot = self::findOne(STP_VRS);
						$ref = $quot->yclose;
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