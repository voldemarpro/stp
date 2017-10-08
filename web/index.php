<?php

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

//require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/../config/web.php');

$vrs = (int)substr($_SERVER['HTTP_HOST'], 3, 1);
define('STP_VRS', $vrs ? $vrs : 1);
define('VRS_TBL_POSTFIX', (STP_VRS == 1 ? '' : '2'));

(new yii\web\Application($config))->run();
