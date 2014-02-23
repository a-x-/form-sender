<?php
/**
 * @file php-form-builder / antiGate.php
 * Created: 17.02.14 / 16:51
 */
/**
 * @param $filename - file path to captcha. MUST be local file. URLs not working
 * @param $apiKey   - account's API key
 * @param $is_verbose - false(commenting OFF),  true(commenting ON)
 * @param $domain
 * @param $rTimeout - delay between captcha status checks
 * @param $mTimeout - captcha recognition timeout
 *
 * additional custom parameters for each captcha:
 * @param $is_phrase - 0 OR 1 - captcha has 2 or more words
 * @param $is_sense - 0 OR 1 - captcha is case sensitive
 * @param $is_numeric -  0 OR 1 - captcha has digits only
 * @param $min_len    -  0 is no limit, an integer sets minimum text length
 * @param $max_len    -  0 is no limit, an integer sets maximum text length
 * @param $is_russian -  0 OR 1 - with flag = 1 captcha will be given to a Russian-speaking worker
 *
 * usage examples:
 * $text=recognize("/path/to/file/captcha.jpg","YOUR_KEY_HERE",true, "antigate.com");
 * $text=recognize("/path/to/file/captcha.jpg","YOUR_KEY_HERE",false, "antigate.com");
 * $text=recognize("/path/to/file/captcha.jpg","YOUR_KEY_HERE",false, "antigate.com",1,0,0,5);

 * @return bool|string - captcha or error status
 */
function antigateRecognize( $filename,
                            $apiKey = '69c79b7ced79a79eadc3e0801782e76b', // todo add our antigate api key
                            $is_verbose = true,
                            $domain = "antigate.com",
                            $rTimeout = 5,
                            $mTimeout = 120,
                            $is_phrase = 0,
                            $is_sense = 0,
                            $is_numeric = 0,
                            $min_len = 0,
                            $max_len = 0,
                            $is_russian = 0
)
{
    if (!file_exists($filename)) {
        if ($is_verbose) echo "file $filename not found\n";
        return false;
    }
    $postData = array(
        'method' => 'post',
        'key' => $apiKey,
        'file' => '@' . $filename,
        'phrase' => $is_phrase,
        'regsense' => $is_sense,
        'numeric' => $is_numeric,
        'min_len' => $min_len,
        'max_len' => $max_len,
        'is_russian' => $is_russian
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://$domain/in.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        if ($is_verbose) echo "CURL returned error: " . curl_error($ch) . "\n";
        return false;
    }
    curl_close($ch);
    if (strpos($result, "ERROR") !== false) // error occurrence
    {
        if ($is_verbose) echo "server returned error: $result\n";
        return false;
    } else {
        $ex = explode("|", $result);
        $captcha_id = $ex[1];
        if ($is_verbose) echo "captcha sent, got captcha ID $captcha_id\n";
        $waittime = 0;
        if ($is_verbose) echo "waiting for $rTimeout seconds\n";
        sleep($rTimeout);
        while (true) {
            $result = file_get_contents("http://$domain/res.php?key=" . $apiKey . '&action=get&id=' . $captcha_id);
            if (strpos($result, 'ERROR') !== false) {
                if ($is_verbose) echo "server returned error: $result\n";
                return false;
            }
            if ($result == "CAPCHA_NOT_READY") {
                if ($is_verbose) echo "captcha is not ready yet\n";
                $waittime += $rTimeout;
                if ($waittime > $mTimeout) {
                    if ($is_verbose) echo "timelimit ($mTimeout) hit\n";
                    break;
                }
                if ($is_verbose) echo "waiting for $rTimeout seconds\n";
                sleep($rTimeout);
            } else {
                $ex = explode('|', $result);
                if (trim($ex[0]) == 'OK') return trim($ex[1]);
            }
        }

        return false;
    }
}
