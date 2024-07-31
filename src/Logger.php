<?php

namespace Apolinux ;

use DateTime;

if(! defined('STDOUT')){
  define('STDOUT',1);
}

/**
 * Write lines to file using logging format
 * 
 * Uses a defined format to write lines to text file. 
 * It can be used for any module that register events into a file
 */
Class Logger{

  const DEFAULT_TAG = 'default' ;

  const DEFAULT_CONTEXT_FORMAT = '{date}|{sid}|{tag}|{context}' ;

  /**
   * @var string logger file name
   */
  private static $logname ;

  /**
  * @var string identifier of the session to write in log file
  */
  private static $session_id ;

  /**
   * @var string date format used to add date in log lines
   */
  private static $date_format ;

  /**
   * @var string filename date format
   */
  private static $file_date_format ;

  /**
   * @var string format of logging
   */
  private static $context_format = self::DEFAULT_CONTEXT_FORMAT ;


  /**
   * set log file
   * @param $filename string log filename
   */
  public static function setLogFile($filename){
    self::$logname = $filename ;
  }

  /**
   * set session ID used to identify group of lines
   * @return string
   */
  public static function setSessionId(){
    self::$session_id = uniqid() ;
  }

  /**
   * set initial configuration for logging
   * @param string $filename file name to save log data
   * @param string $context_format default '{date}|{sid}{tag}|{context}'. format of output text
   * @param string $date_format 'c' by default. Date format used by "date" function to define in each line
   * @param string $file_date_format format of date file, default "Ymd"
   * @TODO session id can be dynamic, it means new for each log, could depend that a new 
   * parameter flag like sid_dynamic or so
   */
  public static function init(
    $filename, 
    string $context_format   = self::DEFAULT_CONTEXT_FORMAT,  
    string $date_format      = 'c', 
    ?string $file_date_format = 'Ymd'
    ){
    self::setLogFile($filename) ;
    self::$context_format = $context_format ;
    self::setSessionId() ;
    self::$date_format      = $date_format ;
    self::$file_date_format = $file_date_format ;
  }

  /**
   * save data to log file
   *
   * each line is formatted like this
   * datetime SEPARATOR  sessionid  SEPARATOR logname SEPARATOR data_serialized
   * By default the data is serialized using json_encode
   * 
   * @param mixed $data_log data to be saved in log file
   * @param string $tag description of log line used to be identified in subsequent searches 
   *
   * @throws LoggerException
   */
  public static function log($data_log, $tag = self::DEFAULT_TAG){
    $log_info = self::getDataFromFormat(
      [
        'date'    => (new DateTime())->format(self::$date_format),
        'sid'     => self::$session_id,
        'tag'     => $tag ,
        'context' => self::serialize($data_log) ,
      ], 
      self::$context_format);
      
    if(defined('STDOUT') &&  self::$logname == STDOUT){
      echo($log_info .PHP_EOL );
      return ;
    }

    $logfile = self::logNameFormatted();

    if(!is_writable(dirname($logfile))){
      throw new LoggerException("The filename '$logfile' is not writable") ;
    }

    return file_put_contents(
                       $logfile ,
                       $log_info .PHP_EOL ,
                       FILE_APPEND
                     ) ;
  }

    
  /**
   * serialize context
   *
   * @param  mixed $context
   * @return string
   */
  private static function serialize($context){
    if(is_array($context)){
      return json_encode($context);
    }elseif(is_object($context)){
      return json_encode($context) ;
    }else{
      return preg_replace('/\n|\n\r|\t|\v/',' ',(string)$context) ;
    }
  }

  /*{
     // $payload = getDataFromFormat(['data' => $data, 'date' => $date, 'tag' => $tag],
     // "{date}|{sid}|{tag}|{data}")
     // options

     - classic: data sequentially

     param1 => array indexed = ['data' => $data, 'date' => $date, 'tag' => $tag]
     param2 => string, data organized : "{date}|{sid}|{tag}|{data}")
     param3 => 'json' 
    
     result: 'YYYY-MM-DD-HH:II:SS|uniqid|default|{"item1":"value1","item2,"value2",...}
    
     - all values

     param1 => array indexed = ['data' => $data, 'date' => $date, 'tag' => $tag]
     param2 => string, data organized : "{date}|{sid}|{tag}")
     param3 => 'sequence' 

     result: 'YYYY-MM-DD-HH:II:SS|uniqid|default|"value1"|"value2",...}  

     param1 => array indexed = ['data' => $data, 'date' => $date, 'tag' => $tag]
     param2 => string, data organized : "{date}|{sid}|{tag}|{data}")
     param3 => 'json' 
    
     result: 'YYYY-MM-DD-HH:II:SS|uniqid|default|{"item1":"value1","item2,"value2",...}

     - all json 
    
     param1 => array indexed ...
     param2 => null 
     param3 => 'json_full',
    
     result: '{"date":"YYYY-MM-DD-HH:II:SS","sid":uniqid,"tag":"default","data":{"item1":"value1","item2,"value2",...}}
    
     - all json as list but the data
    
     param1 => array indexed ...
     param2 => null 
     param3 => 'json_list',
    
     result: '["YYYY-MM-DD-HH:II:SS",uniqid,"default", {"item1":"value1","item2,"value2",...}}
     
     - all json as list
    
     param1 => array indexed ...
     param2 => null 
     param3 => 'json_list_full',
    
     result: '["YYYY-MM-DD-HH:II:SS",uniqid,"default", "value1", "value2",...]

    }*/
      
  /**
   * get data formatted according to format
   *
   * @param  array $text
   * @param  string $text_format
   * @return string
   */
  private static function getDataFromFormat(array $text, string $text_format){
    return str_format($text_format, $text) ;
  }

  /**
   * format log name according to predefined config
   * 
   * return the log name formatted according to parameter $alt_suffix
   * and date format if is set previously
   * 
   * @param string $alt_suffix alternative suffix for log file
   * @return string
   */
  public static function logNameFormatted($alt_suffix = null){
    if(empty(self::$logname)){
      throw new LoggerException('logname file is empty') ;
    }
    $pathinfo = pathinfo(self::$logname) ;

    $alt_suffix1 = $alt_suffix ? "-$alt_suffix" : "";

    if(self::$file_date_format){
      // replace name.log by name-dateformatted.log
      $date = date(self::$file_date_format);

      if(key_exists('basename',$pathinfo)){
        $newname = sprintf("%s%s-%s.%s", $pathinfo['filename'] ,$alt_suffix1 , $date , $pathinfo['extension']);
      }else{
        $newname = sprintf("%s%s-%s", $pathinfo['basename'], $alt_suffix1 , $date) ;
      }
    }else{
      $newname = sprintf("%s%s",$pathinfo['basename'],$alt_suffix1) ;
    }

    return $pathinfo['dirname'] .'/' . $newname ;
  }

  /**
   * delete all lines from log
   *
   * @param string $logname
   * @param bool $delete
   * @return boolean
   */
  public static function clearLog($logname, $delete=false){
      if(! file_exists($logname)){
          return false ;
      }
      if($delete){
          unlink($logname);
      }else{
          $f = @fopen($logname, "r+");
          if ($f !== false) {
              ftruncate($f, 0);
              fclose($f);
          }
      }
  }
}
