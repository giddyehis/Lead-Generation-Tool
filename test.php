<?php
// Test file to check system configuration
echo "PHP Version: " . phpversion() . "\n";
echo "Extensions loaded:\n";
print_r(get_loaded_extensions());

echo "\nTesting cURL:\n";
if (function_exists('curl_init')) {
    echo "cURL is available\n";
} else {
    echo "cURL is NOT available\n";
}

echo "\nTesting DOM:\n";
if (class_exists('DOMDocument')) {
    echo "DOM is available\n";
} else {
    echo "DOM is NOT available\n";
}

echo "\nTesting JSON:\n";
if (function_exists('json_encode')) {
    echo "JSON is available\n";
} else {
    echo "JSON is NOT available\n";
}

echo "\nTesting file operations:\n";
if (is_writable('.')) {
    echo "Directory is writable\n";
} else {
    echo "Directory is NOT writable\n";
}
?> 