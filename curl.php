<?php
/**
 *
 *
 * @file form-sender / curl.php
 * Created: 17.02.14 / 17:16
 */

require_once('../lib/phpQuery/phpQuery.php');

// [[ http://forum.php.su/topic.php?forum=73&topic=1553 ]]
// [[ http://forum.php.su/topic.php?forum=83&topic=1899# ]]

require_once('lib.php');

function compileCascadeSettings()
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
 * @param $boardMappingName
 * @param $boardDynamicData ['commonKey'=>'specifiedValue']
 * @return array
 */
function compilePostData($settings, $postData, $boardMappingName, $boardDynamicData)
{
    $postData = array_merge($postData, $settings['postData']);
    $postData = array_merge($postData, $_GET);
    $boardSettings = json_decode_file('boardSettings.json')[$boardMappingName];

    //
    // Add dynamic post data
   if(!isset($boardSettings['dynamicPostDataMapping'])) {
       $dynamicPostDataMap = $boardSettings['dynamicPostDataMapping'];
       foreach ($boardDynamicData as $commonKey => $specValue) {
           if(!isset($dynamicPostDataMap) || !isset($dynamicPostDataMap[$commonKey]))
               continue;
           $specKey_specKeys = $dynamicPostDataMap[$commonKey];
           if (!is_array($specKey_specKeys))
               $postData[$specKey_specKeys] = $specValue;
           else foreach ($specKey_specKeys as $pattern => $specKey) {
               preg_match('!'.$pattern.'!',$specValue,$parts);
               $specValPart = $parts[1];
               $postData[$specKey] = $specValPart;
           }
       }
   }

    //
    // Add static "postData"
    $staticPostData = $boardSettings["postData"];
    $postData = array_merge($postData, $staticPostData);

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
    curl_setopt($ch, CURLOPT_COOKIEFILE, ($settings['cookiesFile']));
    curl_setopt($ch, CURLOPT_USERAGENT, $settings['useragent']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);

    $domObj = phpQuery::newDocument($result);
    $selects = $domObj['select'];
    $additionMappingAsSpecifiedKey = [];

    foreach ($selects as $select) {
        $specificFieldKey = $select->attributes->getNamedItem('name')->value;
        echo $specificFieldKey;
        $additionMappingAsSpecifiedKey[$specificFieldKey] = [];
        foreach ($select->childNodes as $child) {
            if ($child->nodeName != 'option') continue;
            $specificFieldValue = $child->attributes->getNamedItem('value')->value;
            $commonFieldValue = mb_uppercaseFirstLetter($child->textContent);
            echo "$specificFieldValue; $commonFieldValue; <br >";
            $additionMappingAsSpecifiedKey[$specificFieldKey][$commonFieldValue] = $specificFieldValue;
        }
    }

    //
    // Забрать ссылку на капчу
    if ($settings['captcha']['uri'])
        $captchaLink = $settings['captcha']['uri'];
    else {
        $captchaLinkObj = $domObj[$settings['captcha']['query']]->elements[0];
        $captchaLink = $captchaLinkObj->attributes->getNamedItem('src')->value;
    }


    //
    // download captcha
    curl_setopt($ch, CURLOPT_URL, $captchaLink);
    curl_setopt($ch, CURLOPT_USERAGENT, $settings['useragent']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Charset: windows-1251,utf-8,q=0.7,*;q=0.7'));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $settings['cookiesFile']);
    curl_setopt($ch, CURLOPT_COOKIEFILE, ($settings['cookiesFile']));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//    curl_setopt($ch, CURLOPT_HEADER, 1);
    $captchaResult = curl_exec($ch);

    //
    // Определить тип файла капчи

    $captchaExt = ($settings['captcha']['ext']) ? $settings['captcha']['ext'] : preg_replace('!^.*?/!','',getimagesize($captchaLink)['mime']);

    $fp = fopen($captchaPath = 'captcha.' . $captchaExt, "wb"); // Открыть поток для записи капчи
    fwrite($fp, $captchaResult); // Записать капчу
    fclose($fp);
//    $replace = str_replace("sign.aspx", "testes.php", $result);
    echo iconv($settings['siteEncoding'], "utf-8", $result); //
    curl_close($ch);

    return ['captchaPath' => $captchaPath, 'additionMappingAsSpecifiedKey' => $additionMappingAsSpecifiedKey];
}

function sendForm($settings)
{
    //
    // send form
    $ch = curl_init($settings['formAction']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Charset: windows-1251,utf-8,q=0.7,*;q=0.7'));
    curl_setopt($ch, CURLOPT_USERAGENT, $settings['useragent']);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $settings['cookiesFile']);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $settings['cookiesFile']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $settings['postData']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    echo iconv($settings['siteEncoding'], "utf-8", $result); //
}

