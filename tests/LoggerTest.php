<?php

use Apolinux\Logger;
use Apolinux\LoggerException;
use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase ;

if(!defined('STDOUT')){
    define('STDOUT',1);
}

class LoggerTest extends PHPUnit_Framework_TestCase{
  public $logfile ;
  public $logfile_stdin ;

    public function setUp() : void{
        parent::setUp();
        $this->logfile =  __DIR__  . '/test.log' ;
        $this->logfile_stdin = STDOUT ;
    }

    public function testLogOk(){
        Logger::init($this->logfile);
        $logfile = Logger::logNameFormatted();
        Logger::log('lorem ipsum') ;
        $this->assertFileExists($logfile) ;
        $this->assertStringContainsString(date('Y-m-d'), file_get_contents($logfile)) ;
        $this->assertStringContainsString('lorem ipsum', file_get_contents($logfile)) ;
        $this->assertStringContainsString('default', file_get_contents($logfile)) ;
    }

    public function testCustomFileFormat(){
        Logger::init($this->logfile,Logger::DEFAULT_CONTEXT_FORMAT,'c','mYd');
        $logfile = Logger::logNameFormatted();
        $this->assertStringContainsString(date('mYd'),$logfile);
        Logger::log('lorem ipsum') ;
        $this->assertFileExists($logfile) ;
        $this->assertStringContainsString(date('Y-m-d'), file_get_contents($logfile)) ;
        $this->assertStringContainsString('lorem ipsum', file_get_contents($logfile)) ;
        $this->assertStringContainsString('default', file_get_contents($logfile)) ;
    }

    public function testLogFilenameWithoutDateOk(){
        Logger::init($this->logfile,'{date}|{sid}|{tag}|{context}','c',null);
        $logfile = Logger::logNameFormatted();
        $this->assertEquals($logfile, $this->logfile) ;
        Logger::log('lorem ipsum','bla') ;
        $this->assertFileExists($logfile) ;
        $this->assertStringContainsString(date('Y-m-d'), file_get_contents($logfile)) ;
        $this->assertStringContainsString('lorem ipsum', file_get_contents($logfile)) ;
        $this->assertStringContainsString('|bla|', file_get_contents($logfile)) ;
    }

    public function testLogCustomContextFormat(){
        Logger::init($this->logfile,'{date}@{tag}@{context}');
        $logfile = Logger::logNameFormatted();
        Logger::log('lorem ipsum','bla') ;
        $this->assertFileExists($logfile) ;
        $this->assertStringContainsString(date('Y-m-d'), file_get_contents($logfile)) ;
        $this->assertStringContainsString('lorem ipsum', file_get_contents($logfile)) ;
        $this->assertStringContainsString('@bla@', file_get_contents($logfile)) ;
    }

    public function testNewLinesInStringContext(){
        Logger::init($this->logfile);
        $logfile = Logger::logNameFormatted();
        Logger::log("lorem ipsum\ndolor sit amet",'bla') ;
        $this->assertFileExists($logfile) ;
        $this->assertStringContainsString(date('Y-m-d'), file_get_contents($logfile)) ;
        $this->assertStringContainsString('lorem ipsum dolor sit amet', file_get_contents($logfile)) ;
        $this->assertStringContainsString('@bla@', file_get_contents($logfile)) ;
    }

    public function testLogCustomDateFormatOk(){
        Logger::init($this->logfile,Logger::DEFAULT_CONTEXT_FORMAT, 'YmdHis');
        $this->logfile = Logger::logNameFormatted();
        Logger::log('lorem ipsum','bla') ;
        $this->assertFileExists($this->logfile) ;
        $this->assertStringContainsString(date('YmdHi'), file_get_contents($this->logfile)) ;
        $this->assertStringContainsString('lorem ipsum', file_get_contents($this->logfile)) ;
        $this->assertStringContainsString('|bla|', file_get_contents($this->logfile)) ;

    }

    public function tearDown() : void{
      if(file_exists($this->logfile)){
        unlink($this->logfile);
      }
    }

    public function testClearLog(){
      touch($this->logfile) ;
      Logger::clearLog($this->logfile);
      $this->assertEquals('',file_get_contents($this->logfile));
      Logger::clearLog($this->logfile,true);
      $this->assertFileDoesNotExist($this->logfile);
    }

    public function testFileNotWritable(){
        Logger::init('/notexistdir/test.log');
        $logfile = Logger::logNameFormatted();
        $this->expectException(LoggerException::class);
        Logger::log('lorem ipsum');
    }

    public function testLogStdout(){
        Logger::init(STDOUT);
        ob_start();
        $text = 'lorem ipsum';
        Logger::log($text);
        $output = ob_get_clean();
        $this->assertStringContainsString($text, $output);
    }
}
