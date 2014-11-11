<?php

function LS($str, $args){
    echo vsprintf(L($str), $args);
}