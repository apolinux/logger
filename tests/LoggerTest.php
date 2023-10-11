<?php

use Apolinux\Logger;
use Apolinux\LoggerException;
use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase ;

class LoggerTest extends PHPUnit_Framework_TestCase{
  public $logfile ;

    public function setUp() : void{
        parent::setUp();
        $this->logfile =  __DIR__  . '/test.log' ;
    }

    public function testLogOk(){
        Logger::init($this->logfile);
        $this->logfile = Logger::logNameFormatted();
        Logger::log('lorem ipsum') ;
        $this->assertFileExists($this->logfile) ;
        $this->assertStringContainsString(date('Y-m-d'), file_get_contents($this->logfile)) ;
        $this->assertStringContainsString('lorem ipsum', file_get_contents($this->logfile)) ;
        $this->assertStringContainsString('default', file_get_contents($this->logfile)) ;
    }

    public function testLogAlternativeSuffixOk(){
        Logger::init($this->logfile);
        $this->logfile = Logger::logNameFormatted('debug');
        Logger::log('lorem ipsum',Logger::DEFAULT_TAG,"debug") ;
        $this->assertFileExists($this->logfile) ;
        $this->assertStringContainsString('test-debug',$this->logfile);
        $this->assertStringContainsString(date('Y-m-d'), file_get_contents($this->logfile)) ;
        $this->assertStringContainsString('lorem ipsum', file_get_contents($this->logfile)) ;
        $this->assertStringContainsString('default', file_get_contents($this->logfile)) ;
    }

    public function testLogFunctionOk(){
        logger_init($this->logfile);
        $this->logfile = Logger::logNameFormatted();
        logger('lorem ipsum') ;
        $this->assertFileExists($this->logfile) ;
        $this->assertStringContainsString(date('Y-m-d'), file_get_contents($this->logfile)) ;
        $this->assertStringContainsString('lorem ipsum', file_get_contents($this->logfile)) ;
        $this->assertStringContainsString('default', file_get_contents($this->logfile)) ;
    }

    public function testLogFilenameWithoutDateOk(){
        Logger::init($this->logfile,'|','c',null);
        $this->logfile = Logger::logNameFormatted();
        $this->assertEquals($this->logfile, $this->logfile) ;
        Logger::log('lorem ipsum','bla') ;
        $this->assertFileExists($this->logfile) ;
        $this->assertStringContainsString(date('Y-m-d'), file_get_contents($this->logfile)) ;
        $this->assertStringContainsString('lorem ipsum', file_get_contents($this->logfile)) ;
        $this->assertStringContainsString('|bla|', file_get_contents($this->logfile)) ;
    }

    public function testLogCustomSeparatorOk(){
        Logger::init($this->logfile,'@');
        $this->logfile = Logger::logNameFormatted();
        Logger::log('lorem ipsum','bla') ;
        $this->assertFileExists($this->logfile) ;
        $this->assertStringContainsString(date('Y-m-d'), file_get_contents($this->logfile)) ;
        $this->assertStringContainsString('lorem ipsum', file_get_contents($this->logfile)) ;
        $this->assertStringContainsString('@bla@', file_get_contents($this->logfile)) ;
    }

    public function testLogCustomDateFormatOk(){
        Logger::init($this->logfile,'|','YmdHis');
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
}
