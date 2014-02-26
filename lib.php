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
function json_decode_file($path)
{
    return json_decode(file_get_contents($path), true);
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
function specializeMask($mask, $placeholder, $value)
{
    return str_replace('%%' . $placeholder . '%%', $value, $mask);
}

/**
 * Return "String of some text" from some "sTrIng OF some TeXT".
 * First letter of none unicode text turn to uppercase,
 * another letters turn to lowercase
 * @param $string
 * @return string
 */
function uppercaseFirstLetter($string)
{
    return ucfirst(strtolower($string));
}

/**
 * Return "Строку некоторого текста", from some "сТрокУ НЕКОТОРОГО текста"
 * First letter of unicode text turn to uppercase, another letters turn to lowercase
 * @param $string
 * @return string
 */
function mb_uppercaseFirstLetter($string)
{
    list($first_str) = explode(' ', trim($string));
    return mb_convert_case($first_str, MB_CASE_TITLE, "utf-8") . mb_strtolower(strstr($string, ' '), "utf-8");
}

//function uppercaseFirstLetterRecursive($arrayOfString)
//{
//    foreach($arrayOfString as &$item) {
//        if(is_array($item))
//            $item = uppercaseFirstLetterRecursive($item);
//        else
//            $item = mb_uppercaseFirstLetter($item);
//    }
//    return $arrayOfString;
//}
