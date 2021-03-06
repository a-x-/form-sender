<?php
/**
 * @file form-sender / start.php
 * Created: 19.02.14 / 10:53
 */
require_once('lib.php');
require_once('curl.php');
require_once('antiGate.php');
require_once('mapping.php');
$boardsSettings = json_decode_file('boardSettings.json');

$roomPresetName = $_GET['roomPresetName'];
$boardMappingName = $_GET['boardMappingName'];

$settings = compileCascadeSettings();

$tmpDownloadResult = downloadFormAndCaptcha($settings);
$captchaPath = $tmpDownloadResult['captchaPath'];
$additionMappingAsSpecifiedKey = $tmpDownloadResult['additionMappingAsSpecifiedKey'];

$captchaValue = antigateRecognize($captchaPath);
//$captchaValue = 'TMP CAPTCHA VALUE'; // todo turn back captcha
//echo "<br>Captcha:$captchaValue img:<img src='$captchaPath'>";
$postData = translateFieldSet(
    $roomPresetName,
    $boardMappingName,
    $additionMappingAsSpecifiedKey,
    $settings
);

// todo turn back send form
$settings['postData'] = compilePostData($settings, $postData, $boardMappingName,
    [
        'captchaValue'=>$captchaValue,
        "currentDate"=>date('d.m.Y')
    ]
);
var_dump($settings);
//sendForm($settings);
