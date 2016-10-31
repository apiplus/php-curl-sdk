<?php
/**
 * 更多请登录 http://www.wanpucs.com/user.html 查看API文档
 */

$appkey = '92SfjpsHW970fxT5';//应用key
$secretKey = 'pwl3HVum0eJ0GToW63sObEOKihRHcdsl';//密钥
$gateway = NULL;

require_once 'library/Curl.php';

$apidao = new Apidao_Curl($appkey, $secretKey);

var_dump($apidao->execute('runtime'));
var_dump($apidao->runtime());