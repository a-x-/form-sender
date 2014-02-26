<?php
/**
 *
 *
 * @file form-sender / curl.php
 * Created: 17.02.14 / 17:16
 */

require_once('phpQuery/phpQuery.php');

// [[ http://forum.php.su/topic.php?forum=73&topic=1553 ]]
// [[ http://forum.php.su/topic.php?forum=83&topic=1899# ]]
$postData = [
    "MAX_FILE_SIZE" => "300000",

    "type" => "1",

    "size" => "1",

    "town_id" => "1",

    "station_id" => "128",

    "street" => "",

    "house" => "",

    "time_to_station" => "",

    "time_to_station_type" => "-1",

    "floor" => "",

    "floorn" => "",

    "sroom" => "",

    "sall" => "",

    "skitchen" => "",

    "mebel" => "-1",

    "phone" => "-1",

    "tv" => "-1",

    "inet" => "-1",

    "wm" => "-1",

    "rfgr" => "-1",

    "balkon" => "-1",

    "gender" => "-1",

    "kids" => "-1",

    "pets" => "-1",

    "lease_term" => "0",

    "lease_start_day" => "18",

    "lease_start_month" => "02",

    "lease_start_year" => "2014",

    "rent" => "",

    "rent_type" => "0",

    "prepay" => "0",

    "name" => "",

    "phone_code_1" => "",

    "phone_number_1" => "",

    "call_time_from_1" => "9",

    "call_time_to_1" => "19",

    "phone_code_2" => "",

    "phone_number_2" => "",

    "call_time_from_2" => "9",

    "call_time_to_2" => "19",

    "email" => "",

    "work_with_agents" => "1",
    "additional_info" => "",

    "check_code" => "",

    "submit_new_post" => "Подать объявление!",


];


//$captchaExt = $settings['captchaExt'];
//$localCaptchaPath = "captcha.$captchaExt";
//$siteEncoding = $settings['siteEncoding'];
//$formAction = $settings['formAction'];

require_once('lib.php');

function compileCascadeSettings ()
{
    $boardsSettings = json_decode_file('boardSettings.json');
    $settings = $boardsSettings[$_GET['boardMappingName']];
    $settings['useragent'] = $_SERVER['HTTP_USER_AGENT'];
    $settings['cookiesFile'] = "cookiesFile.txt";

    return $settings;
}

/**
 * @param $settings
 * @param $postData
 * @param $boardDynamicData ['commonKey'=>'specifiedValue']
 * @return array
 */
function compilePostData ($settings, $postData, $boardDynamicData)
{
    $postData = array_merge($postData, $settings['postData']);
    $postData = array_merge($postData, $_GET);
    $boardSettings = json_decode_file('boardSettings.json');

    foreach($boardDynamicData as $commonKey => $specifiedValue) {
        $specifiedKey = $boardSettings['dynamicPostDataMapping'][$commonKey];
        $postData[$specifiedKey] = $specifiedValue;
    }

    return $postData;
}

/**
 * @param $settings
 * @return string captcha path
 */
function downloadFormAndCaptcha($settings)
{

    //
    // download page
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $settings['formUri']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Charset: windows-1251,utf-8,q=0.7,*;q=0.7'));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $settings['cookiesFile']);
    curl_setopt($ch, CURLOPT_COOKIEFILE, file($settings['cookiesFile']));
    curl_setopt($ch, CURLOPT_USERAGENT, $settings['useragent']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);

    $domObj = phpQuery::newDocument($result);
    $selects = $domObj['select'];
    $additionMappingAsSpecifiedKey = [];

    foreach($selects as $select) {
        $specificFieldKey = $select->attributes->getNamedItem('name')->value;
        echo $specificFieldKey;
        $additionMappingAsSpecifiedKey[$specificFieldKey] = [];
        foreach($select->childNodes as $child) {
            $specificFieldValue = $child->attributes->getNamedItem('value')->value;
            $commonFieldValue = $child->textContent;
            echo "$specificFieldValue; $commonFieldValue; <br >";
            $additionMappingAsSpecifiedKey[$specificFieldKey][$commonFieldValue] = $specificFieldValue;
        }
    }

    //
    // download captcha
    curl_setopt($ch, CURLOPT_URL, $settings['captchaGroup']['uri']);
    curl_setopt($ch, CURLOPT_USERAGENT, $settings['useragent']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Charset: windows-1251,utf-8,q=0.7,*;q=0.7'));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $settings['cookiesFile']);
    curl_setopt($ch, CURLOPT_COOKIEFILE, file($settings['cookiesFile']));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//    curl_setopt($ch, CURLOPT_HEADER, 1);
    $captchaResult = curl_exec($ch);
    $fp = fopen($captchaPath = 'captcha.'.$settings['captchaGroup']['ext'], "wb");
    fwrite($fp, $captchaResult);
    fclose($fp);
//    $replace = str_replace("sign.aspx", "testes.php", $result);
    echo iconv($settings['siteEncoding'], "utf-8", $result); //
    curl_close($ch);

    return ['captchaPath'=>$captchaPath, 'additionMappingAsSpecifiedKey'=>$additionMappingAsSpecifiedKey];
}

function sendForm($settings)
{
    //
    // send form
    $ch = curl_init($settings['formAction']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Charset: windows-1251,utf-8,q=0.7,*;q=0.7'));
    curl_setopt($ch, CURLOPT_USERAGENT, $settings['useragent']);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $settings['cookiesFile']);
    curl_setopt($ch, CURLOPT_COOKIEFILE, file($settings['cookiesFile']));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $settings['postData']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    echo iconv($settings['siteEncoding'], "utf-8", $result); //
}

