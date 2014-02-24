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

$captchaValue = antigateRecognize($captchaPath); // todo config the antigate
echo "<br>Captcha:$captchaValue img:<img src='$captchaPath'>";
$postData = translateFieldSet(
    $roomPresetName,
    $boardMappingName,
    $additionSettings = [],
    $additionMappingAsSpecifiedKey
);
$postData = compilePostData($settings, $postData, ['captchaValue'=>$captchaValue]);
sendForm($settings);