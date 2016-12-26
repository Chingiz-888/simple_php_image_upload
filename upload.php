<?php

/**
 *   Поменняйте PROJECT на нужную вам директорию на вашем хосте  (или уберите
 *   эту константу, если скрипт будет лежать в коре)
 *   UPLOAD_DIR - на ту директорию куда будут выигружаться картинки
 *
 *   Внимание! Не забудьте создать директорию, указанную в UPLOAD_DIR на сервере 
 *
 */


define('UPLOAD_DIR', 'uploads/');
define('PROJECT', '/test_images_upload/');
define('RESTRICT_IMAGE_SIZE', 800);

//на случай тестирования на MAMP'е/XAMP'e и им подобных локальных веб-серверах
//мы еще смотрим порт (эти локальные сервера запускаются на 3000 или 8888 портах, а не на 80)
$port = $_SERVER['SERVER_PORT'];
$port_ext = ($port != NULL && $port != '80') ?  ':'.$port : '';

//получаем абсолютный адрес с хостом, портом, для точных ссылок на загруженные рисунки
$address =   "http://".$_SERVER["SERVER_NAME"].$port_ext.PROJECT; 

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");


$uploaded      = array();
$success       = false;



//как минимум хотя бы один файл должен быть отправлен
if(!empty($_FILES['file']['name'][0]))  {
	//перебираем
	foreach ($_FILES['file']['name'] as $position => $name) {

		//меняем размеры
		$fn = $_FILES['file']['tmp_name'][$position];
		$size = getimagesize($fn);
		$ratio = $size[0]/$size[1]; // width/height
		if( $ratio > 1) {
		    $width = RESTRICT_IMAGE_SIZE;
		    $height = RESTRICT_IMAGE_SIZE/$ratio;
		} else {
		    $width = RESTRICT_IMAGE_SIZE*$ratio;
		    $height = RESTRICT_IMAGE_SIZE;
		}

		$src = imagecreatefromstring(file_get_contents($fn));
		$dst = imagecreatetruecolor($width, $height);
		imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
		imagedestroy($src);
		$target_filename_here = UPLOAD_DIR.$_FILES['file']['name'][$position];
		imagepng($dst, $target_filename_here); // adjust format as needed
		imagedestroy($dst);

		$uploaded[] = array(
			"name" => $name,
			"url" =>  $address.UPLOAD_DIR.$name
		);
		$success = true;
		}//end of foreach cycle	
} 

//готтовим json ответа, используем переменную-флаг $success
if($success) {
	$json_response = array(
			"result" => "1",
			"message" => "All images was successfully uploaded on server",
			"uploaded_images" => $uploaded
		);
} else {
	$json_response = array(
			"result" => "0",
			"message" => "There were some errors while images uploading on server"
		);
}


echo json_encode($json_response);

?>