<?php
/**
 * @file form-sender / mapping.php
 * Created: 18.02.14 / 17:32
 */

/**
 * @param $roomPresetName
 * @param $boardMappingName
 * @param $additionDataAsCommon
 * @param $additionMappingAsSpecifiedKey [ 'specifiedFieldKey' => [ 'commonFieldValue' => 'specifiedFieldValue', ... ], ... ]
 * @return array
 */
function translateFieldSet($roomPresetName, $boardMappingName, $additionDataAsCommon, $additionMappingAsSpecifiedKey)
{
    $roomPresets = json_decode_file("roomPresets.json");
    $roomPreset = $roomPresets[$roomPresetName];
    $commonRoomPresent = $roomPresets['common'];
    $commonRoomPresent = array_merge($commonRoomPresent, $additionDataAsCommon);
    $outputFields = [];
    $commonFieldValue = null;
    $boardSpecificFields = json_decode_file("boardMapping.json")[$boardMappingName];
    foreach ($boardSpecificFields as $boardSpecificFieldKey => $tmp) {
        $commonFieldKey = $tmp['#']; // Ключ общего поля
        $domainMapping = $tmp['->']; // {"Значение общего поля": "Значение поля доски"}
        $additionBoardMapping = (isset($additionMappingAsSpecifiedKey[$boardSpecificFieldKey])) ?
            $additionMappingAsSpecifiedKey[$boardSpecificFieldKey] : null;
        // {"Значение общего поля": "Значение поля доски"}
        // Сграблено из формы

        if ($commonFieldValue = evalDeepArrayPath($commonFieldKey, $roomPreset)) {
        } elseif ($commonFieldValue = evalDeepArrayPath($commonFieldKey, $commonRoomPresent)) {
        }

        $specificFieldValue =
            ($domainMapping && isset($domainMapping[$commonFieldValue])) ?
                $domainMapping[$commonFieldValue] : $specificFieldValue = $commonFieldValue;

        $commonMappingValue = mappingValueDecide($commonFieldValue, $domainMapping, $commonFieldValue, function ($e) {
            // todo write error handler
        });
        $additionMappingValue = mappingValueDecide($specificFieldValue, $additionBoardMapping, null, function ($e) {
            // todo write error handler
        });
        $outputFields[$boardSpecificFieldKey]
            = ($additionMappingValue !== null) ? $additionMappingValue : $commonMappingValue;
    }

    return $outputFields;
}


function mappingValueDecide($commonFieldValue, $domainMapping, $noMappingValue, $errorFunction)
{
    if ($commonFieldValue) {
        if (isset($domainMapping[$commonFieldValue])) {
            if ($domainMapping[$commonFieldValue]['#']) { // Если в данных комнаты или общих данных найдено такое поле    }
                return $domainMapping[$commonFieldValue];
            } else { // Если  в данных комнаты или общих данных наёдено поле совпадающее по маске
                $mask = $domainMapping[$commonFieldValue]['*'];
                foreach ($domainMapping[$commonFieldValue] as $placeholder => $commonFieldKey) {
//                    $mask = str_replace("%$placeholder%",)
                }
//                return null; // todo add regexp mapping
            }
        } else { // маппинг отсутствует
            return $noMappingValue;
        }
    } else { // Если поле не имеет совпадений ==> Оишбка
        // todo добавить логгирование ошибок
        $e = [];
        $errorFunction($e);
        return false;
    }
}


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
