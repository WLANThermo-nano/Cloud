<?php
/* @author Florian Riedl */
error_reporting(E_ALL);

// sendTelegram.php?serial=54gfdgf&token=344407734:AAGEdm9gxoFDfuXKUL6HynxDopYrdIYkMPc&chatID=399660681&ch=0&msg=up&lang=de
if (isset($_GET['serial']) AND !empty($_GET['serial']) AND isset($_GET['token']) AND !empty($_GET['token']) AND isset($_GET['chatID']) AND !empty($_GET['chatID']) AND isset($_GET['ch']) AND isset($_GET['msg']) AND !empty($_GET['msg']) AND isset($_GET['lang']) AND !empty($_GET['lang'])){
	sendTelegram($_GET['token'],$_GET['chatID'],getMsg($_GET['ch'],$_GET['msg'],$_GET['lang']));
}else{
	die('false');
}


//sendTelegram('344407734:AAGEdm9gxoFDfuXKUL6HynxDopYrdIYkMPc','399660681',getMsg('0','up','de'));
//echo getMsg('0','up','de');

function getMsg($ch,$msg,$lang){
$de = array("msg0" => "ACHTUNG!","msg1" => "Kanal","msg2" => "hat","up" => "Übertemperatur","down" => "Untertemperatur");
$en = array("msg0" => "ATTENTION!","msg1" => "Channel","msg2" => "has","up" => "overtemperature","down" => "undertemperature");
	
	switch ($lang) {
    case de:
		$message = ''.$de["msg0"].' '.$de["msg1"].''.$ch.' '.$de["msg2"].' '.$de["".$msg.""].'.';
        return $message;
        break;
    case en:
		$message = ''.$en["msg0"].' '.$en["msg1"].''.$ch.' '.$en["msg2"].' '.$en["".$msg.""].'.';
        return $message;
        break;
    default:
		$message = ''.$en["msg0"].' '.$en["msg1"].''.$ch.' '.$en["msg2"].' '.$en["".$msg.""].'.';
		return $message;
	}
}

function sendTelegram($token,$chatID,$msg){
	$url = 'https://api.telegram.org/bot' . $token . '/sendMessage?text="'.$msg.'"&chat_id='.$chatID.'';
	$result = json_decode(file_get_contents($url));
	//var_dump($result);
	if($result->ok === true){
		SimpleLogger::info("Message has been sent! - device(".$_GET['serial'].") \n");
	}else{
		SimpleLogger::error("Message could not be sent! - device(".$_GET['serial'].") \n");
		SimpleLogger::debug(json_encode($result) . "\n");		
	}
}



// Needed by strftime() => error message otherwise
date_default_timezone_set ( 'Europe/Berlin' );
 
/**
 * A simple logging class
 *
 * - uses file access to log any message such as debug, info, error,
 * fatal or an exception including stacktrace
 *
 * @author saftmeister
 */
class SimpleLogger
{
  const DEBUG = 1;
  const INFO = 2;
  const NOTICE = 4;
  const WARNING = 8;
  const ERROR = 16;
  const CRITICAL = 32;
  const ALERT = 64;
  const EMERGENCY = 128;
 
  /**
   * Path to log file
   *
   * @var string
   */
  private static $filePath = 'logs/sendTelegram.log';
  //private static $filePath = 'html/logs/strftime'.("%Y%m%d").'searchUpdate.log';	 
  /**
   * Log file size in MB
   *
   * @var number
   */
  private static $maxLogSize = 2;
 
  /**
   * Logs a particular message using a given log level
   *
   * @param number $level
   *          The level of error the message is
   * @param string $message
   *          Either a format or a constant
   *          string which represents the message to log.
   */
  public static function log($level, $message /*,...*/)
  {
	clearstatcache ();
	$filePath = 'logs/'.strftime("%Y-%m-%d").'_sendTelegram.log';
	if (! is_int ( $level ))
	{
	  $message = $level;
	  $level = self::DEBUG;
	}
	else if ($level != self::DEBUG && $level != self::INFO && $level != self::NOTICE &&
		$level != self::WARNING && $level != self::ERROR && $level != self::CRITICAL &&
		$level != self::ALERT && $level != self::EMERGENCY)
	{
	  $level = self::ERROR;
	}
	$mode = "a";
	if (! file_exists ( $filePath ))
	{
	  $mode = "w";
	}
	else
	{
	  $attributes = stat ( $filePath );
	  if ($attributes == false || $attributes ['size'] >= self::$maxLogSize * 1024 * 1024)
	  {
		$mode = "w";
	  }
	}
   
	$levelStr = "FATAL";
	switch ($level)
	{
	  case self::DEBUG:    $levelStr = "DEBUG"; break;
	  case self::INFO:     $levelStr = "INFO "; break;
	  case self::NOTICE:   $levelStr = "NOTIC"; break;
	  case self::WARNING:  $levelStr = "WARN "; break;
	  case self::ERROR:    $levelStr = "ERROR"; break;
	  case self::CRITICAL: $levelStr = "CRIT "; break;
	  case self::ALERT:    $levelStr = "ALERT"; break;
	  case self::EMERGENCY:$levelStr = "EMERG"; break;
	}
	$filePath = 'logs/'.strftime("%Y-%m-%d").'_sendTelegram.log';
	$fd = fopen ( $filePath, $mode );
	if ($fd)
	{
	  $arguments = func_get_args ();
	  if (count ( $arguments ) > 2)
	  {
		$format = $arguments [1];
		array_shift ( $arguments ); // Do not need the level
		array_shift ( $arguments ); // Do not need the format as argument
		$message = vsprintf ( $format, $arguments );
	  }
	  $time = strftime ( "%Y-%m-%d %H:%M:%S", time () );
	  fprintf ( $fd, "%s\t[%s]: %s", $time, $levelStr, $message );
	  fflush ( $fd );
	  fclose ( $fd );
	}
  }
 
  /**
   * Simple wrapper arround log method for alert level
   *
   * @param string $message          
   * @see SimpleLogger::log()
   */
  public static function alert($message)
  {
	self::log ( self::ALERT, $message );
  }
 
  /**
   * Simple wrapper arround log method for critical level
   *
   * @param string $message
   * @see SimpleLogger::log()
   */
  public static function crit($message)
  {
	self::log ( self::CRITICAL, $message );
  }
 
  /**
   * Simple wrapper arround log method for debug level
   *
   * @param string $message          
   * @see SimpleLogger::log()
   */
  public static function debug($message)
  {
	self::log ( self::DEBUG, $message );
  }
 
  /**
   * Simple wrapper arround log method for emergency level
   *
   * @param string $message          
   * @see SimpleLogger::log()
   */
  public static function emerg($message)
  {
	self::log ( self::EMERGENCY, $message );
  }
 
  /**
   * Simple wrapper arround log method for info level
   *
   * @param string $message          
   * @see SimpleLogger::log()
   */
  public static function info($message)
  {
	self::log ( self::INFO, $message );
  }
 
  /**
   * Simple wrapper arround log method for notice level
   *
   * @param string $message          
   * @see SimpleLogger::log()
   */
  public static function notice($message)
  {
	self::log ( self::NOTICE, $message );
  }
 
  /**
   * Simple wrapper arround log method for error level
   *
   * @param string $message          
   * @see SimpleLogger::log()
   */
  public static function error($message)
  {
	self::log ( self::ERROR, $message );
  }
 
  /**
   * Simple wrapper arround log method for notice level
   *
   * @param string $message
   * @see SimpleLogger::log()
   */
  public static function warn($message)
  {
	self::log ( self::WARNING, $message );
  }
 
 
  /**
   * Log a particular exception
   *
   * @param Exception $ex
   *          The exception to log
   */
  public static function logException(Exception $ex)
  {
	$level = self::ERROR;
	if ($ex instanceof RuntimeException)
	{
	  $level = self::ALERT;
	}
   
	self::log ( $level, "Exception %s occured: %s\n%s\n", get_class ( $ex ), $ex->getMessage (), $ex->getTraceAsString () );
   
	if ($ex->getPrevious () && $ex->getPrevious () instanceof Exception)
	{
	  self::log ( $level, "Caused by:\n" );
	  self::logException ( $ex->getPrevious () );
	}
  }
 
  /**
   * Dump a particular object and write it to log file
   *
   * @param mixed $o
   */
  public static function dump($o)
  {
	$out = var_export ( $o, true );
	self::debug ( sprintf ( "Contents of %s\n%s\n", gettype ( $o ), $out ) );
  }
}
// $bot_id = "344407734:AAGEdm9gxoFDfuXKUL6HynxDopYrdIYkMPc";

// if (isset($_GET['token'])){
 	
 # Note: you want to change the offset based on the last update_id you received
// $ch = curl_init();
// // Disable SSL verification
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// // Will return the response, if false it print the response
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// // Set the url
// curl_setopt($ch, CURLOPT_URL, $url);
// //curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
// //curl_setopt($ch, CURLOPT_USERPWD, "$usernamearray[$usernamekey]:$passwd");
// curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: ESP8285'));
// // Execute
// $result = curl_exec($ch);
// echo $result;
// }else{
	// echo 'false';
	
// }



//$result = json_decode($result, true);

// foreach ($result['result'] as $message) {
    // var_dump($message);
// }


// # The chat_id variable will be provided in the getUpdates result

// $result = json_decode($result, true);

// var_dump($result['result']);