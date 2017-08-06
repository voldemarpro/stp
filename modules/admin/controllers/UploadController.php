<?php
namespace app\modules\admin\controllers;

use Yii;
use app\modules\admin\components\MainController;
// use app\modules\admin\models\Param;

/**
 * Загрузка файла
 */
class UploadController extends MainController
{
	public $enableCsrfValidation = false;
	
	public function actionIndex()
    {
		if (!empty($_FILES['file']))
		{
			$file_name_units = explode('.', $_FILES['file']['name']);
			$file_ext = array_pop($file_name_units);
			$file_ext = strtolower($file_ext);
			
			$extArr = array(
				'txt', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar', 'pdf',
				'jpg', 'jpeg', 'gif', 'png', 'swf', 'mp4'
			);
			
			// Проверка типа файла, если он загружается как изображение
			if (in_array($file_ext, $extArr))
				$true_type = ($_FILES['file']['type'] == 'image/png' ||
					$_FILES['file']['type'] == 'image/jpg' ||
					$_FILES['file']['type'] == 'image/gif' ||
					$_FILES['file']['type'] == 'image/jpeg' ||
					$_FILES['file']['type'] == 'image/pjpeg'
				);
			else
				$true_type = false;
			
			// Переименование и загрузка файла с допустимым расширением
			if ($true_type)
			{
				$file_name = abs(crc32(implode('_', $file_name_units))) . ".$file_ext";
				$file = '/res/'.$file_name;
				if (copy($_FILES['file']['tmp_name'], \Yii::getAlias('@app/web') . $file)) {
					
					// generate a thumbnail image
					//\yii\imagine\Image::resize(\Yii::getAlias('@app/web') . $file, 640, 480)
					//	->save(\Yii::getAlias('@app/web') . $file, ['quality' => 99]);

					return json_encode(array(
						'filelink' => self::imgResize($file, ['mode'=>2, 'size'=>480]),
						'filename' => $file_name
					));				
				}
			}
			
			return json_encode(array(
				'filelink' => '//:0',
				'filename' => 'Ошибка: недопустимый тип файла'
			));	
		}
	}

	/**
	 *	Изменение размера изображения
	 *
	 *	@param		string	$img		имя файла с картинкой
	 *	@param		array	$params		настройки 
										[mode] :
											0 : WIDTH
											1 : HEIGHT
											2 : BY_LARGER_DIM
											3 : BY_SMALLER_DIM
											4 : LIMITS
	 *	@return		string 				URI изображения
	 */	
	private static function imgResize($src, $params)
	{
		$file = \Yii::getAlias('@app/web') . $src;
		if (!is_file($file)) 
			return 'no such file: '.$file;

		$img = explode('.', $src);
		$ext = strtolower(end($img));
		
		$mode = isset($params['mode']) ? $params['mode'] : 0;
		list($width, $height) = getimagesize($file);
		
		switch ($mode) 
		{
			case 0 : 
				$new_width = $params['size'];
				$new_height = round($height * ($params['size']/$width));
				
				break;
			
			case 1 :
				$new_height = $params['size'];
				$new_width = round($width * ($params['size']/$height));	
				
				break;
			
			case 2 :
				$isHorizontal = ($width > $height) ? TRUE : FALSE;
				if ($isHorizontal) {
					$k = $width / $params['size'];
					$new_width = $params['size'];
					$new_height = round($height/$k);
				} else {
					$k = $height / $params['size'];
					$new_width = round($width/$k);
					$new_height = $params['size'];				
				}
				break;
				
			case 3 :
				$isHorizontal = ($width > $height) ? TRUE : FALSE;
				if (!$isHorizontal) {
					$k = $width / $params['size'];
					$new_width = $params['size'];
					$new_height = round($height/$k);
				} else {
					$k = $height / $params['size'];
					$new_width = round($width/$k);
					$new_height = $params['size'];				
				}
				break;
			
			case 4 :
				$isHorizontal = ($width > $height) ? TRUE : FALSE;
				
				if ($isHorizontal) {
					$k = $width / $params['width'];
					$new_height = round($height/$k);
					if (($k_h = $params['height'] / $new_height) < 1) {
						$new_height = round($new_height * $k_h);
						$params['width'] = $params['width'] * $k_h; 
					}
					$new_width = $params['width'];
				} else {
					$k = $height / $params['height'];
					$new_width = round($width/$k);
					if (($k_w = $params['width'] / $new_width) < 1) {
						$new_width = round($new_width * $k_w);
						$params['height'] = $params['height'] * $k_w; 
					}
					$new_height = $params['height'];			
				}
				
				break;		
		}
		
		if ($width <= $new_width && $height <= $new_height)
			return $src;		
		
		$src = str_replace('.'.$ext, '', $src).'_'.$new_width.'x'.$new_height.'.'.$ext;
		$new_file = \Yii::getAlias('@app/web') . $src;

		if (!is_file($new_file)) {
			
			$image_resized = imagecreatetruecolor($new_width, $new_height);
			
			if($ext == 'jpeg' || $ext == 'jpg') {
				$image = imagecreatefromjpeg($file);
				imagecopyresampled($image_resized, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
				imagejpeg($image_resized, $new_file, 96);
			}
			elseif($ext == 'png') {
				$image = imagecreatefrompng($file);
				imagecopyresampled($image_resized, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
				imagepng($image_resized, $new_file);
			}
			elseif($ext == 'gif')	{
				$image = imagecreatefromgif($file);
				imagecopyresampled($image_resized, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
				imagegif($image_resized, $new_file);
			}

			imagedestroy($image_resized);
		}
		
		return $src;
	}
}
?>