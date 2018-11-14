<?php

namespace SuperSimpleResponseEmitter;

function headers_sent()
{
    return false;
}

function header ($string, $replace = true) {
    $replace = $replace ? "true" : "false";
    echo "$string $replace\n";
}