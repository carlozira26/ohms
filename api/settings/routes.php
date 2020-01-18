<?php
// Auto include all routes from routes folder
$FILES = __DIR__ . DIRECTORY_SEPARATOR . '..'.DIRECTORY_SEPARATOR.'Routes' . DIRECTORY_SEPARATOR;
foreach (glob("{$FILES}*.php") as $filename){
    include $filename;
};