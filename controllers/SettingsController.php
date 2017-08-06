<?php
namespace app\controllers;

use Yii;
use app\components\MainController;
use app\models\Traider;

/**
 * Настройки учетной записи
 */
class SettingsController extends MainController
{
	/**
     * @inheritdoc
     */
    public function behaviors()
    {
        $arr = parent::behaviors();
		$arr['access']['rules'][0]['verbs'] = ['GET', 'POST'];
		
		return $arr;
    }  
	
	public function actionIndex()
    {
		return $this->render('index');
    }

	public function actionSave()
    {
		if (count($_POST)) {
			
			$params = Yii::$app->request->post();
			$model = Yii::$app->user->identity;
			
			$model->scenario = 'update';
			
			if (!empty($params['passport']) && is_array($params['passport']) && count($params['passport']) == 4) {
				$params['passport'] = implode('|', $params['passport']);
				if (!trim($params['passport'], '|'))
					unset($params['passport']);
				else
					$params['passport'] = Traider::myAESencrypt($params['passport']);
			} else
				unset($params['passport']);
				
			if (!empty($params['crc']) && !empty($params['phone'])) {
				
				if (is_array($params['phone']))
					$params['phone'] = array_shift($params['phone']);
					
				$phone = preg_replace('/[^\d]/', '', $params['phone']);
				if ($phone[0] == '7')
					$phone[0] = '8';
				elseif ($phone[0] == '9')
					$phone = '8'.$phone;
				
				if ($phone)	{
					$crc = strrev( (date('w') + 1) * crc32($phone) );
					$crc = substr($crc, 0, 5);
					if ($params['crc'] != $crc)
						unset($params['phone']);
					else {
						$params['phone'] = $phone;
						$params['grade'] = ($params['grade'] & 8) ? ($params['grade'] + 8) : $params['grade'];
					}

				} else {
					unset($params['phone']);	
				}
				
				unset($params['crc']);
			}
			
			foreach($params as $key=>&$p) {
				if (\is_array($p)) {

					if (!($p = implode('', $p))) {
						unset($params[$key]);
						continue;
					}

				} elseif (\substr($key, -4) == 'date') {
					$p = explode('/', $p);
					$p = implode('-', array_reverse($p));
				}
			}
			
			if (empty($params['pay_bank']))
				unset($params['pay_bank']);
			
			if (!empty($params['pwd']) && trim($params['pwd']))
				$params['pwd'] = Traider::myAESencrypt(trim($params['pwd']));
			else
				$params['pwd'] = $model->pwd;
			
			$params['login'] = $model->login;
	
			$model->oldLogin = $model->login;
			$model->setAttributes($params);
			
			if (!($model->grade & 4))
				$model->grade += 4;

			if ($model->save())
				return '1';
			else
				return \json_encode($model->firstErrors, JSON_FORCE_OBJECT);

		}
    }
	
	/**
     * Send sms to check new cellphone number
     */	
	public function actionConfirm($phone = '')
    {
		if ($phone) {
			$phone = preg_replace('/[^\d]/', '', $phone);
			if ($phone) {
				if ($phone[0] == '7')
					$phone[0] = '8';
				elseif ($phone[0] == '9')
					$phone = '8'.$phone;				
			}
			
			if ($phone) {
				if (empty($_SESSION['sms']) || empty($_SESSION['sms'][$phone]))
					$_SESSION['sms'][$phone] = 1;
				elseif ($_SESSION['sms'][$phone] == 2)
					return 'Слишком много SMS с одного номера';
				else
					$_SESSION['sms'][$phone] = 2;			
				
				$crc = strrev( (date('w') + 1) * crc32($phone) );
				$crc = substr($crc, 0, 5);
				$sms = new \app\components\Sms;
				$sms->send($phone, "Код подтверждения $crc");

				return $crc;				
			}
		}
			
    }
}
