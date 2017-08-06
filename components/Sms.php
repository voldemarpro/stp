<?php
namespace app\components;

/**
 * SMS sender via SMS Pilot
 */
class Sms
{
	public $api = 'http://smspilot.ru/api.php';
	public $apikey = "64APK394VGO7T1IRV389HD9DE28GG9556G88MK52844KBHWZ7D379IQFD3I309MM";//'XXXXXXXXXXXXYYYYYYYYYYYYZZZZZZZZXXXXXXXXXXXXYYYYYYYYYYYYZZZZZZZZ';
	public $charset = 'UTF-8';
	public $to;
	public $text;
	public $from = 'SBip';
	public $error;
	public $success;
	public $status; // new in 1.7
	public $cost; // new in 1.8
	public $balance; // new in 1.8
	
	public function __construct($apikey = false,  $charset = false) {
		if ($apikey)
			$this->apikey = $apikey;
			
		if ($charset)
			$this->charset = $charset;

		$this->from = \Yii::$app->params['sms_sender'];
	}
	
	// send sms via smspilot.ru
	public function send($to = false, $text = false, $from = '') {
		
		if ($to)
			$this->to = $to;
	
		if ($text)
			$this->text = $text;

		if ($from != '')
			$this->from = $from;
			
		$this->error = false;
		$this->success = false;
		$this->status = array();
		
		$text = ($this->charset != 'UTF-8') ? mb_convert_encoding($this->text, 'utf-8', $this->charset) : $this->text;	
		
		$result = $this->http_post($this->api, array(
			'send' => $text,
			'to' => ((is_array($this->to)) ? implode(',', $this->to) : $this->to),
			'from' => $this->from,
			'apikey' => $this->apikey
		));
		
		if ($result) {
			if (substr($result,0,6) == 'ERROR=') {
				$this->error = substr($result,6);
				return false;
			} elseif (substr($result,0,8) == 'SUCCESS=') {
				
				$this->success = substr($result,8,($p = strpos($result,"\n"))-8);
				
				if (preg_match('~(\d+)/(\d+)~', $this->success, $matches )) {
					$this->cost = $matches[1]; // new in 1.8
					$this->balance = $matches[2]; // new in 1.8
				}

				$status_csv = substr( $result, $p+1 );
				//status
				$status_csv = explode( "\n", $status_csv );
				foreach( $status_csv as $line ) {
					$s = explode(',', $line);
					$this->status[] = array(
						'id' => $s[0],
						'phone' => $s[1],
						'zone' => $s[2],
						'status' => $s[3]
					);
				}				
				return $this->status;
			} else {
				$this->error = 'UNKNOWN RESPONSE';
				return false;
			}
		} else {
			$this->error = 'CONNECTION ERROR';
			return false;
		}
	}
	
	// sockets version HTTP/POST
	public function http_post( $url, $data ) {
		
		$eol = "\r\n";
		
		$post = '';
	
		if (is_array($data)) {
			foreach( $data as $k => $v)
				$post .= $k.'='.urlencode($v).'&';
			$post = substr($post,0,-1);
			$content_type = 'application/x-www-form-urlencoded';
		} else {
			$post = $data;
			if (strpos($post, '<?xml') === 0)
				$content_type = 'text/xml';
			else if (strpos($post, '{') === 0)
				$content_type = 'application/json';
			else
				$content_type = 'text/html';
		}
		if ((($u = parse_url($url)) === false) || !isset($u['host'])) return false;
		
		if (!isset($u['scheme'])) $u['scheme'] = 'http';
				
		$request = 'POST '.(isset($u['path']) ? $u['path'] : '/').((isset($u['query'])) ? '?'.$u['query'] : '' ).' HTTP/1.1'.$eol
			.'Host: '.$u['host'].$eol
			.'Content-Type: '.$content_type.$eol
			.'Content-Length: '.mb_strlen($post, 'latin1').$eol
			.'Connection: close'.$eol.$eol
			.$post;
		
		$host = ($u['scheme'] == 'https') ? 'ssl://'.$u['host'] : $u['host'];
		
		if (isset($u['port']))
			$port = $u['port'];
		else
			$port = ($u['scheme'] == 'https') ? 443 : 80;
		
		$fp = @fsockopen($host, $port, $errno, $errstr, 10);
		
		if ($fp) {
			
			$content = '';
			$content_length = false;
			$chunked = false;
			
			fwrite($fp, $request);
			
			// read headers				
			while ($line = fgets($fp)) {
				
				if (preg_match('~Content-Length: (\d+)~i', $line, $matches)) {	
					$content_length = (int) $matches[1];
				} else if (preg_match('~Transfer-Encoding: chunked~i', $line)) {
					$chunked = true;
				} else if ($line == "\r\n") {
					break;
				}

			}
			// read content		
			if ($content_length !== false) {
				
				$_size = 4096;
				do {
					$_data = fread($fp, $_size );
					$content .= $_data;
					$_size = min($content_length-strlen($content), 4096);
				} while( $_size > 0 );
				
//				$content = fread($fp, $content_length);
				
			} else if ($chunked) {
		
				while ( $chunk_length = hexdec(trim(fgets($fp))) ) {
					
					$chunk = '';
					$read_length = 0;

					while ( $read_length < $chunk_length ) {

						$chunk .= fread($fp, $chunk_length - $read_length);
						$read_length = strlen($chunk);

					}				
					$content .= $chunk;

					fgets($fp);

				}
			} else {
				while(!feof($fp)) $content .= fread($fp, 4096);
			}
			
			fclose($fp);
			
			return $content;
			
		} else {
			return false;
		}
	}
}
?> 