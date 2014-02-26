<?php
/**
 * @file form-sender / lib.php
 * Created: 19.02.14 / 17:04
 */

/**
 * Decode JSON file as associative array by its path
 * @param $path
 * @return mixed
 */
function json_decode_file($path) {
    return json_decode(file_get_contents($path),true);
}

/**
 * Вычислить значение многомерного массива, ключ которого задан строкой key1.key2.key3. ... keyN
 * @param $path
 * @param $root
 * @return mixed|bool
 */
function evalDeepArrayPath($path, $root)
{
    $dirs = preg_split('/\./', $path);
    for ($i = 0, $l = count($dirs); $i < $l; ++$i) {
        $dir = $dirs[$i];
        if (isset($root[$dir])) $root = $root[$dir];
        else return false;
    }
    return $root;
}

/**
 * Специализировать маску (подставить одно из значений вместо указанного плейсхолдера)
 * @param $mask string
 * @param $placeholder string
 * @param $value string
 * @return string
 */
function specializeMask ($mask, $placeholder, $value) {
    return str_replace('%%'.$placeholder.'%%',$value,$mask);
}