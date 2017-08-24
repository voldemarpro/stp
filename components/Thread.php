<?php
namespace app\components;

use yii\db\Command;
use yii\db\ActiveRecord;

class Thread extends ActiveRecord
{
	/**
     * @var array Массив разделов/сервисов, сгруппированных по родителям
     */	
	public $tree = array();
	
	/**
     * @var array Массив разделов/сервисов верхнего уровня (language-dependent)
     */	
	public $items = array();
	

	/**
     * @var self Раздел по умолчанию
     */
	public $defaultItem;
	
	/**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%threads}}';
    }
	
	public function scenarios()
    {
        return [
            'default' => \array_keys($this->tableSchema->columns),
        ];
    }
	
	public function __construct($config = array())
	{
		parent::__construct($config);
			
		$this->tree = $this->getTree();
		
		if (($key = key($this->tree)) !== null)
			$this->items = $this->tree[$key][0];
		
		if (!\Yii::$app->user->isGuest) {
			foreach ($this->items as $_id=>$val) {
				if ($val['vname']) {
					$this->defaultItem = array_merge(['id'=>$_id, 'p_id'=>0], $val);
					break;
				}
			}
			if (\Yii::$app->requestedRoute && \mb_strpos(\Yii::$app->requestedRoute, 'thread', 0, 'utf-8') ===  false) {
				$arr = explode('/', \Yii::$app->requestedRoute);
				$modules = \Yii::$app->getModules(true);
				$module = reset($modules);
				
				// учитываем возможность работы под модулем
				if ($module && $arr[0] == $module->id) {
					if (count($arr) == 1) {
						$this->attributes = $this->defaultItem;
						$vname = '';
					} else
						$vname = $arr[1];
				} else			
					$vname = $arr[0];
				
				// считаем разделы одноуровнеными
				if ($vname)
					if ($item = $this->getListByKey('vname', $vname))
						$this->attributes = $item;		
			
			} else
				$this->attributes = $this->defaultItem;
		}
	}

	/**
     * Получение списка разделов, сгруппированных по родителям
     * 
     * @param	$p_id 			родитель
     * @param	$onlyActive		только активные
	 * //@param	$services	 	включая сервисы
	 *
	 * @return	Массив разделов, сгруппированных по родителям
     */
	public function getTree($p_id = 0, $onlyActive = true/*, $services = true*/)
	{
		$cond = array();
		//if ($services == false) $cond['services'] =  '`is_service` = 0';		
		if ($onlyActive) $cond['active'] = '`active` = 1';
		$condSql = implode($cond, ' AND ');
		$condSql = $condSql ? " WHERE $condSql" : '';

		if (!$this->tree) {
			$sqlStmt  = "SELECT * FROM {$this->tableSchema->name}$condSql ORDER BY `p_id`, `lang`, `precedence` < 0, `precedence`, `name`";
			$items = \Yii::$app->db->createCommand($sqlStmt)->queryAll();
			foreach ($items as $row)
				$this->tree[$row['lang']][$row['p_id']][$row['id']] = $row;
		}

		if ($p_id) {
			foreach ($this->tree as $lang=>$t)
				if (isset($t[$p_id]))
					return $this->tree[$lang][$p_id];
		} else
			return $this->tree;
	}
	
	/**
	 * Поиск разделов по занчению какого-либо атрибута
	 * 
	 * @param	$key			string          имя атрибута
	 * @param	$values			scalar|array	значение
	 * @param	$return_first	bool		 	возврат первого найденного раздела
	 * @param	$return_first	string		 	язык раздела
	 *
	 * @return	array	Массив элементов [id, p_id]
	 */
	public function getListByKey($key, $values, $return_first = true, $lang = 'ru')
	{
		$this->getTree();
		$values = is_array($values) ? $values : array($values);
		$values = array_flip($values);
		$list = array();
		$tree = isset($this->tree[$lang]) ? $this->tree[$lang] : $list;
		$p_id_arr = array();
		$p_id = 0;
		while (!empty($tree[$p_id]) || $p_id_arr) {
			$id = key($tree[$p_id]);
			if ($id !== null) {
				$page = $tree[$p_id][$id]; unset($tree[$p_id][$id]);
				if (isset($page[$key]) && isset($values[$page[$key]]))
					$list[$page[$key]][] = array(
						'id'        => $id,
						'p_id'      => $p_id,
						'redirect'  => $page['redirect'],
						'precedence'=> $page['precedence'],
						'title'		=> $page['title'],
						'active'    => $page['active'],						
						'name'      => $page['name'],
						'vname'     => $page['vname'],
						'icon'      => $page['icon']
					);
				if (isset($tree[$id])) {
					$p_id_arr[] = $p_id;
					$p_id = $id;
				}					
			} else
				$p_id = array_pop($p_id_arr);
		}

		if ($list) {
			if ($return_first)
				foreach ($list as &$pages)
					$pages = reset($pages);
			
			return (sizeof($values) > 1) ? $list : reset($list);
		
		} else
			return array();
	}
	
	
    /**
     * Sending email to Trader/admin
     */		
    public static function sendmail($to, $from, $subject, $body)
    {
		$transport = \Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
		  ->setUsername( Yii::$app->params['admin_email'] )
		  ->setPassword( base64_decode( file_get_contents(\Yii::getAlias('@app/config/mkey.dat')) ) );

		$mailer = new \yii\swiftmailer\Mailer;
		$mailer->useFileTransport = false;
		$mailer->setTransport($transport);

		$message = $mailer->compose()
			//->setEncoder(\Swift_Encoding::getBase64Encoding())
			->setFrom($from)
			->setTo( ( $to ? $to : Yii::$app->params['admin_email'] ) )
			->setSubject($subject);
		
		$contentEncoderBase64 = new \Swift_Mime_ContentEncoder_Base64ContentEncoder();	
		$headerEncoderBase64 = new \Swift_Mime_HeaderEncoder_Base64HeaderEncoder();
		
		$headers = $message->getSwiftMessage()->getHeaders();
		foreach (['subject', 'from', 'to'] as $name) {
			$header = $headers->get($name);
			$header->setCharset('utf-8');
			//$header->setContentType('text/plain');
			$header->setEncoder($headerEncoderBase64);
		}			

		$html = \Swift_MimePart::newInstance();
		$html->setCharset('utf-8');
		$html->setEncoder($contentEncoderBase64);
		$html->setContentType('text/html');
		$html->setBody($body);
		$message->getSwiftMessage()->attach($html);
		
		return $message->send();
    }
}
