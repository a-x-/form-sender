<?php
/**
 * @file form-sender / lib.php
 * Created: 19.02.14 / 17:04
 */
function json_decode_file($path) {
    return json_decode(file_get_contents($path),true);
}