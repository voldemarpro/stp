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
     * @var array Массив с id всех родительских разделов/сервисов
     */
	//public $root = array();
	
	// Директории для хранения контента приложения (сайта)
	public $tmpPath; // для временных файлов
	public $imgPath; // для хранения картинок
	public $docPath; // для хранения документов
	public $mediaPath; // для хранения медиафайлов
	
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

		if (!empty($config['tmpPath']))
			$this->tmpPath = $config['tmpPath'];
		
		if (!empty($config['imgPath']))
			$this->imgPath = $config['imgPath'];
		
		if (!empty($config['docPath']))
			$this->docPath = $config['docPath'];			
		
		if (!empty($config['mediaPath']))
			$this->mediaPath = $config['mediaPath'];
			
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
				
				// считаем разделы/сервисы одноуровнеными
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
	 * Вся цепочка родительских id (до корневого раздела)
	 * 
	 * @param  $id          	id раздела, для которого получаем
	 * @param  $return_self 	включая собственный id
	 * @param  $return_zero 	включая корневой id (0)
	 */
	public function getParentArray($id, $return_self = false, $return_zero = false)
	{
		if (isset($this->tree[LANG])) {
			$last_branch = end($this->tree[LANG]);
			$p_list = $return_self ? array($id): array();
			while ($id)
				foreach ($this->tree[LANG] as $p_id => $sections)
					if (isset($sections[$id]) || $sections == $last_branch) {
						$p_list[] = $p_id;
						$id = $p_id;
						break;			
					}
			unset($p_list[count($p_list)-1]);
			if (count($p_list) < 1) $p_list[0] = $id;
			if ($return_zero) $p_list[] = 0;			
			return array_values($p_list);
		
		} else
			return $return_self ? array($id) : array();
	}

	/**
	 * Полный виртуальный путь к разделу (/vname1/vname2/../)
	 * 
	 * @see	self::getParentArray()
	 
	public function getVPath($id = false)
	{
		$res = '/';
		$id = $id ? $id : $this->id;
		$arr = array_reverse($this->getParentArray($id, true, false));
		
		if ($this->tree) {
			$p_id = 0;
			foreach ($arr as $k => $id) {
				$res .= $this->tree[LANG][$p_id][$id]['vname'].'/';
				$p_id = $id;
			}
		}

		return (LANG == DEF_LANG) ? $res : LANG.'/'.$res;
	}
	*/
	
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
						'vname'     => $page['vname']
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
     * Email to traider/admin
     */		
    public static function mailTo($email, $from, $subject, $body)
    {
		$pwdFile = \Yii::getAlias('@app/runtime/'.md5('mail@sotabank.com').'.dat');
		if (!file_exists($pwdFile))
			return false;
		/*
		require \Yii::getAlias('@app/vendor/swiftmailer/lib/swift_required.php');

		$mailer = new \yii\swiftmailer\Mailer;
		$mailer->useFileTransport = false;
		$mailer->setTransport(
			\Yii::createObject([
				//'class' => 'Swift_MailTransport',
				'class' => 'Swift_SmtpTransport',
				//'extraParams' => null
				'host' => 'smtp.gmail.com',
				'username' => 'clients@sotabank.com',
				'password' => base64_decode( file_get_contents($pwdFile) ),
				'port' => 465,
				'encryption' => 'ssl'
			])
		);
		
		return $mailer->compose()
			->setEncoder(\Swift_Encoding::getBase64Encoding())
			->setFrom($from)
			->setTo($email)
			->setSubject($title)
			->setHtmlBody($body)
			->send();
		*/
		
		require \Yii::getAlias('@app/vendor/phpmailer/class.phpmailer.php');
		
		$mailer = new \PHPMailer;
		$mailer->IsSMTP();
		$mailer->Host = 'smtp.gmail.com';
		$mailer->SMTPAuth = true;
		$mailer->Username = 'mail@sotabank.com';
		$mailer->Password = base64_decode( trim(file_get_contents($pwdFile)) );
		$mailer->Port = 465;
		$mailer->SMTPSecure = 'ssl';

		$mailer->From = 'mail@sotabank.com';
		$mailer->FromName = 'SOTA-1';
		$mailer->AddAddress($email);

		$mailer->IsHTML(true);

		$mailer->Subject = $subject;
		$mailer->Body    = $body;
		
		$res = $mailer->Send();
		
		$mailer->ClearAddresses();

		return $res;
    }
}
