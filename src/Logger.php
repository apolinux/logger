<?php

namespace Apolinux ;

Class Logger{

  const DEFAULT_TAG = 'default' ;
  /**
   * @var string log name
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
   * @var string default tag name
   */
  private static $default_tag ;

  private static $separator ;

  /**
   * set log file
   * @param $filename string log filename
   */
  public static function setLogFile($filename){
    self::$logname = $filename ;
  }

  /**
   * set sesion id used to create log line
   */
  public static function setSessionId(){
    self::$session_id = uniqid() ;
  }

  /**
   * set initial configuration for logging
   * @param string $filename file name to save log data
   * @param string $separator '|' by default. separator between data and other fields of log
   * @param string $date_format 'c' by default. Date format used by "date" function to define in each line
   * @param string $file_date_format format of date file, default "Ymd"
   */
  public static function init($filename, $separator='|', $date_format='c', $file_date_format='Ymd', $default_tag='default'){
    self::setLogFile($filename) ;
    self::setSessionId() ;
    self::$separator = $separator ;
    self::$date_format      = $date_format ;
    self::$file_date_format = $file_date_format ;
    self::$default_tag      = $default_tag ;
  }

  /**
   * save data to log file
   *
   * each line is formatted like this
   * datetime SEPARATOR  sessionid  SEPARATOR logname SEPARATOR data_serialized
   *
   * @param mixed $data_log data to be saved in log file
   * @param string $tag description of log used to search in config or to label in log
   * @param string $alt_suffix optional. alternative suffix to add at end of filename ex:
   * if filename is info(-date).log, with alt will be: info-alt(-date).log
   *
   * @throws LoggerException
   */
  public static function log($data_log, $tag = self::DEFAULT_TAG, $alt_suffix=null){
    $data_enc = json_encode($data_log) ;
    $logfile = self::logNameFormatted($alt_suffix);

    if(!is_writable(dirname($logfile))){
      throw new LoggerException("The filename '$logfile' is not writable") ;
    }

    file_put_contents(
                       $logfile ,
                       join(
                            self::$separator ,
                            [
                              date(self::$date_format) ,
                              self::$session_id ,
                              $tag ,
                              $data_enc
                            ]
                          ) ."\n" ,
                       FILE_APPEND
                     ) ;
  }

  /**
   * format log name according to predefined config
   * @param string $alt_suffix alternative suffix
   * @return string
   */
  public static function logNameFormatted($alt_suffix = null){
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
