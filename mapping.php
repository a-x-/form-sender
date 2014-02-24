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
        $commonFieldName = $tmp['commonFieldName']; // Ключ общего поля
        $domainMapping = $tmp['domainMapping']; // {"Значение общего поля": "Значение поля доски"}
        $additionBoardMapping = (isset($additionMappingAsSpecifiedKey[$boardSpecificFieldKey])) ?
            $additionMappingAsSpecifiedKey[$boardSpecificFieldKey] : null;
        // {"Значение общего поля": "Значение поля доски"}
        // Сграблено из формы

        if (isset($roomPreset[$commonFieldName])) $commonFieldValue = $roomPreset[$commonFieldName];
        elseif (isset($commonRoomPresent[$commonFieldName])) $commonFieldValue = $commonRoomPresent[$commonFieldName];

        $specificFieldValue = $domainMapping[$commonFieldValue];
        $noBaseMappingValue = mappingDecide($specificFieldValue, $additionBoardMapping, null, function ($e) {
            // todo write error handler
        });
        if ($noBaseMappingValue !== null) {
            $outputFields[$boardSpecificFieldKey] = $noBaseMappingValue;
        } else {
            $outputFields[$boardSpecificFieldKey] = mappingDecide($commonFieldValue, $domainMapping, $commonFieldValue, function ($e) {
                // todo write error handler
            });
        }
    }

    return $outputFields;
}


function mappingDecide($commonFieldValue, $domainMapping, $noMappingValue, $errorFunction)
{
    if ($commonFieldValue)
        if (isset($domainMapping[$commonFieldValue])) { // Если в данных комнаты или общих данных найдено такое поле    }
            return $domainMapping[$commonFieldValue];
        } elseif (false) { // Если  в данных комнаты или общих данных наёдено поле совпадающее по маске
            return null; // todo add mapping
        } else { // маппинг отсутствует
            return $noMappingValue;
        }
    else { // Если поле не имеет совпадений ==> Оишбка
        // todo добавить логгирование ошибок
        $e = [];
        $errorFunction($e);
        return false;
    }
}


function evalDeepArrayPath ($path, $root){
    $dirs = preg_split('/\./',$path);
    for($i=0,$l = count($dirs);$i<$l;++$i){
        $dir = $dirs[$i];
        $root = $root[$dir];
    }
}
