<?php
/**
 * Name: Functions library 2
 * Project: LIB/Part of Library In Backyard
 * 
 ** 
 * Purpose: 
 * využíváno jen K:\Work\alfa.gods.cz\www\tools\check-pages.php
 *
 **
 * History 
 * 120215, function GetHTTPstatusCode ($URL_STRING){ .. from r.godsapps.eu/index.php   
 * 120215, function get_data ($URL_STRING, $User-agent){ .. from r.godsapps.eu/magic-link.php
 * //120427, if the result is not number, maybe the server doesn't understand HEAD, let's try GET
 * 120427,   if($address != "81.31.47.101"){//gethostbyname returns this IP address on www.alfa.gods.cz if domain name does not exist  //@TODO - zautoamtizovat správnou IP adresu  
 * 
 *
 ** TODO  
 * 
 * 
 */
   
function GetHTTPstatusCode ($URL_STRING){
   $url = parse_url($URL_STRING);
   if ($url['scheme'] == 'http') {

    //X if(!isset($url['path']))$url['path']='/';    
    $host = $url['host'];
    $port = (isset($url['port'])?$url['port']:80);
    $path = (isset($url['path'])?$url['path']:'/');
    my_error_log("url: ".print_r($url,TRUE),4,16);//debug
    //X if(!$port)
    //X    $port = 80;

    $request = "HEAD $path HTTP/1.1\r\n"
              ."Host: $host\r\n"
              ."Connection: close\r\n"
              ."\r\n";

    my_error_log("IPv4 is ".$address = gethostbyname($host),5,16);//set & log
  if($address != "81.31.47.101"){//gethostbyname returns this IP address on www.alfa.gods.cz if domain name does not exist
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if (socket_connect($socket, $address, $port)) {

        socket_write($socket, $request, strlen($request));

        $response = explode(' ', socket_read($socket, 1024));
        my_error_log("HEAD HTTP response: ".print_r($response,TRUE),4,16);//debug
        
        //120427, if the result is not number, maybe the server doesn't understand HEAD, let's try GET
        if (!is_numeric($response[1])){
          $request = "GET $path HTTP/1.1\r\n"
              ."Host: $host\r\n"
              ."Connection: close\r\n"
              ."\r\n";

          socket_write($socket, $request, strlen($request));
          $response = explode(' ', socket_read($socket, 1024));
          my_error_log("GET HTTP response: ".print_r($response,TRUE),4,16);//debug
          if (!is_numeric($response[1]))my_error_log("REQUEST = $request RETURNED RESPONSE = {$response[1]} INSTEAD OF HTTP status",3);
        }        
        
    } else {
        my_error_log("socket_connect to $host $path failed",3,13);//debug        
    }

    //print "<p>Response: ". $response[1] ."</p>\r\n";

    socket_close($socket);
    my_error_log("result=".$result = (isset($response[1])?($response[1]):'socket_error'),5,16);//set & log 
    return $result;
    }//if($address != "81.31.47.101")
    else {
      return 'DNS_error';
    }
   } else {
       my_error_log("Scheme: {$url['scheme']} not supported by GetHTTPstatusCode",4,16);//debug
       return 0;
   }
}

function GetHTTPstatusCodeByUA ($URL_STRING, $userAgent = "GetStatusCode/1"){
   $url = parse_url($URL_STRING);
   if ($url['scheme'] == 'http') {
    
    $host = $url['host'];
    $port = $url['port'];
    $path = $url['path'];
    if(!$port)
        $port = 80;

    $request = "HEAD $path HTTP/1.1\r\n"
              ."Host: $host\r\n"
              ."User-agent: $userAgent\r\n"              
              ."Connection: close\r\n"
              ."\r\n";

    $address = gethostbyname($host);
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if (socket_connect($socket, $address, $port)) {

        socket_write($socket, $request, strlen($request));

        $response = explode(' ', socket_read($socket, 1024));
        my_error_log("HTTP response: ".print_r($response,TRUE),4,16);//debug
    } else {
        my_error_log("socket_connect to $host $path failed",3,13);//debug        
    }

    //print "<p>Response: ". $response[1] ."</p>\r\n";

    socket_close($socket);
    return $response[1];
   } else {
       my_error_log("Scheme: {$url['scheme']} not supported by GetHTTPstatusCode",4,16);//debug
       return 0;
   }
}


/* gets the data from a URL */
function get_data($url,$useragent)
{
  $ch = curl_init();
  $timeout = 5;
  curl_setopt($ch,CURLOPT_URL,$url);
  curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
  curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}
