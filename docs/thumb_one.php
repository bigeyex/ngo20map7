<?php
    $imagePath = $argv[1];
    $imageFileName = $argv[2];
    require_once '../ThinkPHP/Extend/Library/ORG/Util/Image.class.php';
    Image::thumb2($imagePath.$imageFileName,$imagePath."th150x150_".$imageFileName,'',150,150 );
    Image::thumb2($imagePath.$imageFileName,$imagePath."th628x326_".$imageFileName,'',628,326 );
?>