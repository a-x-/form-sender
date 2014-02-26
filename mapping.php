<?php
/**
 * @file form-sender / mapping.php
 * Created: 18.02.14 / 17:32
 */

/**
 * @param $roomName
 * @param $boardMappingName
 * @param $specificFieldsMapsAdd [ 'specifiedFieldKey' => [ 'commonFieldValue' => 'specifiedFieldValue', ... ], ... ]
 * @return array
 */
function translateFieldSet($roomName, $boardMappingName, $specificFieldsMapsAdd)
{
    // $specificFieldsMaps = [    'specificFieldKey'=>[  'comKey'=>['commonValue'=>'specificValue']  ]    ]
    $outputFields = [];
    $currComValue = null;

    $rooms = json_decode_file("roomPresets.json");
    $room = $rooms[$roomName];
    $comRoom = $rooms['common'];
    $room = array_merge_recursive($room,$comRoom);
    unset($comRoom);
    $specificFieldsMaps = json_decode_file("boardMapping.json")[$boardMappingName];
    if ($specificFieldsMaps['_STASH']) {
        $stashes = $specificFieldsMaps['_STASH'];
        unset($specificFieldsMaps['_STASH']);
    } else {
        $stashes = [];
    }
    var_dump($stashes);
    foreach ($specificFieldsMaps as $specificFieldKey => $valueMaps) {
        // Iterate over addition map too
        $valueMapsAdd = (isset($specificFieldsMapsAdd[$specificFieldKey])) ? $specificFieldsMapsAdd[$specificFieldKey] : null;

        if (isset($valueMaps['@'])) {
            $partMask = $valueMaps['@'];
            unset($valueMaps['@']);
            $specVal = '';
            foreach ($valueMaps as $comKey => $domainMap) { // будет выполнена 1 раз, т.к. указывается только 1 comKey
                $specVal = getSpecVal($valueMapsAdd, $room, $comKey, $domainMap);
                break;
            }
            preg_match('!' . $partMask . '!', $specVal, $parts);
            $specValResult = $parts[1];
        } else {
            $complexMask = getMaskFromMapJsonDescription($valueMaps); // Get complexMask from JSON file description of map
            $specValResult = getSpecificComplexFieldValue($valueMaps, $valueMapsAdd, $room, $complexMask);
        }

        //
        // Add found specific complex value into output fields collection
        $outputFields[$specificFieldKey] = $specValResult;
    }

    return $outputFields;
}

/**
 * Get mask from JSON file description of map
 * @param $valueMaps array of array|string
 * @return string
 */
function getMaskFromMapJsonDescription(&$valueMaps)
{
    $mask = ''; // sought-for (искомое) value
    if (isset($valueMaps['*'])) { // mask described by JSON conf
        $mask = $valueMaps['*'];
        unset($valueMaps['*']);
    } elseif (count($valueMaps) === 1) { // there is one2one mapping and mask isn't described
        foreach ($valueMaps as $comKey => $domainMap) // будет выполнена 1 раз, чтобы получить маску
            $mask = '%%' . $comKey . '%%';
    } else {
        // todo add error handler ( одному specific полю дано в соотв-е несколько common полей, но mask не задана )
        die ("Одному specific полю дано в соотв-е несколько common полей, но mask не задана");
    }
    return $mask;
}

function getSpecificComplexFieldValue($valueMaps, $valueMapsAdd, $room, $mask)
{
    //
    // Iterate over common fields mapped to current specific field
    // Rem: map may have one common field
    $specValComplex = $mask; // sought-for (искомое) value
    foreach ($valueMaps as $comKey => $domainMap) {
        $specVal = getSpecVal($valueMapsAdd, $room, $comKey, $domainMap);
        //
        // Add found specific value into mask
        $specValComplex = specializeMask($specValComplex, $comKey, $specVal);
    }

    return $specValComplex;
}

/**
 * Get specific field value
 * @param $valueMapsAdd
 * @param $room
 * @param $comKey
 * @param $domainMap
 * @internal param $comRoom
 * @return bool|mixed
 */
function getSpecVal($valueMapsAdd, $room, $comKey, $domainMap)
{
//    $domainMapAdd = ($valueMapsAdd && isset($valueMapsAdd[$comKey])) ? $valueMapsAdd[$comKey] : null;
    $domainMapAdd = $valueMapsAdd;
    // Rem: $domainMap = {{"Значение общего поля": "Значение поля доски"},,,}

    //
    // Find common value for current room
    if ($currComValue = evalDeepArrayPath($comKey, $room)) {
//    } elseif ($currComValue = evalDeepArrayPath($comKey, $comRoom)) {
    } else {
        // todo write error handler ( common key not found )
    }

    //
    // Find specific value for current room and current board
    $specVal = // find 1th approximation of specific value
        ($domainMap && isset($domainMap[$currComValue])) ? $domainMap[$currComValue] : $currComValue;

    $specVal = // find specific value by addition map if possible
        ($domainMapAdd && isset($domainMapAdd[$specVal])) ? $domainMapAdd[$specVal] : $specVal;
    return $specVal;
}