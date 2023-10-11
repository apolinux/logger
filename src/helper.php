<?php
use Apolinux\Logger;

// define generic functions

if(! function_exists('logger_init')){
  function logger_init(...$args){
    call_user_func_array([Logger::class, 'init'],$args);
  }
}

if(! function_exists('logger')){
  function logger(...$args){
    call_user_func_array([Logger::class, 'log'],$args);
  }
}