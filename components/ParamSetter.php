<?php
namespace app\components;

use Yii;
use yii\base\BootstrapInterface;

/*
 * The base class that you use to retrieve the settings from the database
 */
class ParamSetter implements BootstrapInterface {
    /**
    * Bootstrap method to be called during application bootstrap stage.
    * Loads all the settings into the Yii::$app->params array
    * @param Application $app the application currently running
    */
    public function bootstrap($app) {
		$tbl = Yii::$app->db->tablePrefix.'params';
		$sql = Yii::$app->db->createCommand("SELECT * FROM $tbl");
        $settings = $sql->queryAll();
		$timeSettings = [];
		if ($settings)
			foreach ($settings as $val) {
				Yii::$app->params[$val['name']] = \unserialize($val['value']);
				if ($val['type'] == 5) {
					$timeSettings[] = $val['name'];
					$tUnits = explode(':', Yii::$app->params[$val['name']]);
					Yii::$app->params[$val['name']] = \strtotime(date('Y-m-d')) + $tUnits[0] * 3600 + $tUnits[1] * 60 + $tUnits[2];
				}
			}
		
		// Set time frontiers to further values If current day is not traiding one
		if (!empty(Yii::$app->params['open_days']) && !Yii::$app->params['open_days'][date('w')]) {
			foreach ($timeSettings as $sName)
				Yii::$app->params[$sName] = Yii::$app->params[$sName] + 24 * 60 * 60;
			Yii::$app->params['time_offset'] = 24 * 60 * 60;
		} else
			Yii::$app->params['time_offset'] = 0;
		
		// Getting MSC timezine offset and set formatter for all views
		$dtz = new \DateTimeZone('Europe/Moscow');
		$dt = new \DateTime('now', $dtz);

		define('DTIME_OFFSET', $dt->getOffset());
   }
}
?> 