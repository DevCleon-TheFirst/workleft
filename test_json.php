<?php
$json = file_get_contents('test.json');
$decoded = json_decode($json, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON Error: " . json_last_error_msg() . "\n";
} else {
    echo "JSON IS VALID\n";
}
