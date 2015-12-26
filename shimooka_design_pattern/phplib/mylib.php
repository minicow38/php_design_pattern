<?php
function printLine($str, $lineBreakAsHtml = false){
    if($lineBreakAsHtml){
        echo nl2br($str . PHP_EOL);
    }else{
        echo $str . PHP_EOL;  
    }
}

function convert($str){
    return mb_convert_encoding($str, mb_internal_encoding(), "auto");
}
