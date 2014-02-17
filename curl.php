<?php
/**
 * @file form-sender / curl.php
 * Created: 17.02.14 / 17:16
 */

$url = "https://site.ru";
$useragent = $_SERVER['HTTP_USER_AGENT'];

$cookiesFile = "cookiesFile.txt";
$post = $_POST;

/**
 * @param $url
 * @param $useragent
 * @param $captchaUrl
 * @param $cookiesFile
 */
function sendForm($url, $useragent, $captchaUrl, $cookiesFile = "cookiesFile.txt")
{
    //
    // download page
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Charset: windows-1251,utf-8,q=0.7,*;q=0.7'));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiesFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, file($cookiesFile));
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);

    //
    // download captcha
    curl_setopt($ch, CURLOPT_URL, $captchaUrl);
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Charset: windows-1251,utf-8,q=0.7,*;q=0.7'));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiesFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, file($cookiesFile));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $res = curl_exec($ch);
    $fp = fopen("captcha.jpg", "wb");
    fwrite($fp, $res);
    fclose($fp);
    $replace = str_replace("sign.aspx", "testes.php", $result);
    echo $replace;
    curl_close($ch);
//
//    //
//    // send form
//    $ch = curl_init('https://' . $url . '/sign.aspx');
//    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Charset: windows-1251,utf-8,q=0.7,*;q=0.7'));
//    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
//    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiesFile);
//    curl_setopt($ch, CURLOPT_COOKIEFILE, file($cookiesFile));
//    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
//    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
//    curl_setopt($ch, CURLOPT_POST, 1);
//    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
//    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//    $result = curl_exec($ch);
//    echo $result;

}