<?php
header('Content-Type: text/plain');
echo "Headers sent by PHP:\n";
print_r(headers_list());
?>
