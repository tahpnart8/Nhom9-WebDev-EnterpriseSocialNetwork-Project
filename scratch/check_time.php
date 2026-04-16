<?php
echo "PHP date.timezone: " . ini_get('date.timezone') . "\n";
echo "PHP Current Time: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Timezone Name: " . date_default_timezone_get() . "\n";
?>
