<?php
$target_url = 'https://catbox.moe/user/api.php';
$file_name_with_full_path = realpath('test.txt');
$cFile = curl_file_create($file_name_with_full_path);
$post = array('reqtype' => 'fileupload','fileToUpload'=> $cFile);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$target_url);
curl_setopt($ch, CURLOPT_POST,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$result=curl_exec ($ch);
curl_close ($ch);
echo $result;
