# Logger

Register text to file. 

## Instalation

```
composer require Apolinux/logger 
```

### Description

As first step, it must be configured logging parameters with init static method:

```
Logger::init($logname);
```

Then, it's called log method whenever is required.

```
Logger::log('something to log');

...

Logger::log(['phone' => 123 ,'date' => time() ]);

```

### Init method

```
Logger::init($filename, 
    string $context_format    = self::DEFAULT_CONTEXT_FORMAT,  
    string $date_format       = 'c', 
    ?string $file_date_format = 'Ymd')
```
$filename: specifies log destination filename

$context_format : describes format of text. By default is:

{date}|{sid}|{tag}|{context}

Where:
* date : is a date in a format specified by $date_format. Can be added micro or miliseconds if it's required.
* sid  : is a random string generated each time init() is called.
* tag  : is a label to describe log used for posterior searches into file.
* context : is the information to write in the log

All the parameters are optional, you can change the format according to your needs.

$date_format : optional. Format supported by PHP date() function. Describes the format of {date} in context_format. By default is 'c'.

$file_date_format : Optional. Format supported by PHP date() function. Defines the date format in filename, by default is 'Ymd'. 


### Log method

```
Logger::log($data_log, $tag = self::DEFAULT_TAG);
```
$data_log : text,array or object to write in file. 

$tag : Optional. label to identify the log. By default is 'default'.


Example:


```
require_once 'vendor/autoload.php' ;

use Apolinux\Logger ;

Logger::init(
  'dirlogs/filetosavelogs.log' ,
  '{date}|{sid}|{tag}|{context}' ,
  'YmdHis.u',
  'Ymd'
) ;

...

Logger::log('information to be logged','info') ;

...

Logger::log(['type' => 'human', 'name' => 'Diana'],'register');

```

### TODO
* use json to format complete line
* encode file to reduce log size
* create log reader 
* search in logs