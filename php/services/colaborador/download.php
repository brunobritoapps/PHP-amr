<?php
$FILENAME = filter_input(INPUT_GET,"file", FILTER_DEFAULT);
$FILEDIR = filter_input(INPUT_GET,"dir", FILTER_DEFAULT);

$FILEPATH =$FILEDIR.$FILENAME;
header('Content-Description: File Transfer');
header("Content-Disposition: attachment; filename={$FILENAME}");
header('Content-Type: application/octet-stream');
readfile($FILEPATH);
