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

if(! function_exists('str_format')){
  function str_format(string $msg, array $vars)
  {
      $msg = preg_replace_callback('#\{\}#', function($r){
          static $i = 0;
          return '{'.($i++).'}';
      }, $msg);

      return str_replace(
          array_map(function($k) {
              return '{'.$k.'}';
          }, array_keys($vars)),

          array_values($vars),

          $msg
      );
  }
}