<?php

namespace GodsDev\Backyard;

use GodsDev\Backyard\BackyardTime;

class BackyardError {

    protected $BackyardConf = array();
    //private $PageTimestamp = null;  
    protected $BackyardTime;
    
    /**
     * 
     * @param array $backyardConfConstruct
     * @param BackyardTime $BackyardTime
     */
    public function __construct(
            array $backyardConfConstruct = array(), 
            BackyardTime $BackyardTime = null
            ) {
        $this->BackyardTime = ($BackyardTime === null)?(new BackyardTime()):$BackyardTime;        
        $backyardConfConstruct = array_merge(                
                array(//default values
                    'logging_level'             => 5,       //log up to the level set here, default=5 = debug//logovat az do urovne zde uvedene: 0=unknown/default_call 1=fatal 2=error 3=warning 4=info 5=debug/default_setting 6=speed  //aby se zalogovala alespoň missing db musí být logování nejníže defaultně na 1 //1 as default for writing the missing db at least to the standard ErrorLog
                    'logging_level_name'        => array(0 => 'unknown', 1 => 'fatal', 'error', 'warning', 'info', 'debug', 'speed'),
                    'logging_file'              => '',      //soubor, do kterého má my_error_log() zapisovat
                    'logging_level_page_speed'  => 5,       //úroveň logování, do které má být zapisována rychlost vygenerování stránky
                    'error_log_message_type'    => 0,       //parameter message_type http://cz2.php.net/manual/en/function.error-log.php for my_error_log; default is 0, i.e. to send message to PHP's system logger; recommended is however 3, i.e. append to the file destination set either in field $this->BackyardConf['logging_file or in table system
                    'die_graciously_verbose'    => true,    //show details by die_graciously() on screen (it is always in the error_log); on production it is recomended to be set to to false due security
                    'mail_for_admin_enabled'    => false,   //fatal error may just be written in log //$backyardMailForAdminEnabled = "rejthar@gods.cz";//on production, it is however recommended to set an e-mail, where to announce fatal errors
                    'log_monthly_rotation'      => true,    //true, pokud má být přípona .log.Y-m.log (výhodou je měsíční rotace); false, pokud má být jen .log (výhodou je sekvenční zápis chyb přes my_error_log a jiných PHP chyb)
                    'log_standard_output'       => false,   //true, pokud má zároveň vypisovat na obrazovku; false, pokud má vypisovat jen do logu
                    'log_profiling_step'        => false,   //110812, my_error_log neprofiluje rychlost //$PROFILING_STEP = 0.008;//110812, my_error_log profiluje čas mezi dvěma měřenými body vyšší než udaná hodnota sec
                    'error_hacked'              => true,    //ERROR_HACK parameter is reflected
                    'error_hack_from_get'       => 0,       //in this field, the value of $_GET['ERROR_HACK'] shall be set below                    
                ),
                $backyardConfConstruct);
        $this->BackyardConf = $backyardConfConstruct;
        //@todo do not use $this->BackyardConf but set the class properties right here accordingly; and also provide means to set the values otherwise later
    }
    
/**
 * Error_log() modified to log necessary debug information by application to its own log (common to the whole host by default).
 * 
 * <b>ERROR NUMBER LIST</b>
 *  0 Unspecified<br/>
 *  1-5 Reserved
 *  6 Speed<br/>
 *  7-9 Reserved<br/>
 *  10 Authentization<br/>
 *  11 MySQL<br/>
 *  12 Domain name<br/>
 *  13 Tampered URL or ID<br/>
 *  14 Improve this functionality<br/>
 *  15 Page was refreshed with the same URL therefore action imposed by URL is ignored<br/>
 *  16 Logging values<br/>
 *  17 Missing input value<br/>
 *  18 Setting of a system value<br/>
 *  19 Redirecting<br/>
 *  20 Facebook API<br/>
 *  21 HTTP communication<br/>
 *  22 E-mail<br/>
 *  23 Algorithm flow<br/>
 *  24 Third party API<br/>
 *  1001 Establish correct error_number
 * 
 * @global float $RUNNING_TIME
 * @global int $ERROR_HACK
 * 
 * @param int $level Error level
 * @param string $message Zpráva k vypsání - při použití error_number bude obsahovat doplňující info
 * @param int $error_number Číslo chyby, dle které lze chybu vyhodnotit .. bude zapsaná v admin návodu apod. - zatím nepoužito
 * @return bool
 * 
 * 
 *
    * @todo rework to be more compliant with PSR-3 http://www.php-fig.org/psr/psr-3/
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    //public function log($level, $message, array $context = array());    
public function log($level, $message, array $context = array()) {//($message, $level = 0, $error_number = 0) {
    //mozna by stalo za to prepsat i jmeno te puvodni, aby se treba i sphpblog psal tam, kde to vidim
    //mohla by být zavedena čtvrtá vstupní proměnná $line=''
    //$line - mělo by být vždy voláno jako basename(__FILE__)."#".__LINE__ , takže bude jasné, ze které řádky source souboru to bylo voláno
    // Ve výsledku do logu zapíše:
    //[Timestamp: d-M-Y H:i:s] [Logging level] [$error_number] [$_SERVER['SCRIPT_FILENAME']] [username@gethostbyaddr($_SERVER['REMOTE_ADDR'])] [sec od startu stránky] $message
    global
    //$username,                  //Placeholder for logging users along.
    $RUNNING_TIME,
    $ERROR_HACK//,
    ;
    $username = 'anonymous'; //placeholder
    
    if(!is_string($message)){
        error_log("wrong message: Backyard->log({$level},{$message})");
    }
    if($context === array()){
        $error_number = 0;
    } else {
        $error_number = reset($context);//get the value of the first element //@todo do this only if there is just one element otherwise use field named 'error_number'
    }

    $result = true; //it could eventually be reset to false after calling error_log()
    //if ($ERROR_HACK > $this->BackyardConf['logging_level']){//$ERROR_HACK may be set anytime in the code
    //    $this->BackyardConf['logging_level'] = $ERROR_HACK; //120918
    //}

    if (($level <= max(array(
                $this->BackyardConf['logging_level'],
                $this->BackyardConf['error_hack_from_get'], //set potentially as GET parameter
                $ERROR_HACK, //set as variable in the application script
            ))
            ) //to log 0=unknown/default 1=fatal 2=error 3=warning 4=info 5=debug 6=speed according to $level
            || (($error_number == "6") && ($this->BackyardConf['logging_level_page_speed'] <= $this->BackyardConf['logging_level'])) //speed logovat vždy když je ukázaná, resp. dle nastavení $logging_level_page_speed
    ) {
        $RUNNING_TIME_PREVIOUS = $RUNNING_TIME;
        if ((( ($RUNNING_TIME = round($this->BackyardTime->getmicrotime() - $this->BackyardTime->getPageTimestamp(), 4)) - $RUNNING_TIME_PREVIOUS) > $this->BackyardConf['log_profiling_step'] ) && $this->BackyardConf['log_profiling_step']) {
            $message = "SLOWSTEP " . $message; //110812, PROFILING
        }

        if ($this->BackyardConf['log_standard_output']) {
            echo ((($level <= 2) ? "<b>" : "") . "{$message} [{$RUNNING_TIME}]" . (($level <= 2) ? "</b>" : "") . "<hr/>" . PHP_EOL); //110811, if fatal or error then bold//111119, RUNNING_TIME
        }

        $message_prefix = "[" . date("d-M-Y H:i:s") . "] [" . $this->BackyardConf['logging_level_name'][$level] . "] [" . $error_number . "] [" . $_SERVER['SCRIPT_FILENAME'] . "] ["
                . $username . "@" 
                . (isset($_SERVER['REMOTE_ADDR'])?gethostbyaddr($_SERVER['REMOTE_ADDR']):'-')//phpunit test does not set REMOTE_ADDR
                . "] [" . $RUNNING_TIME . "] [" 
                . (isset($_SERVER["REQUEST_URI"])?$_SERVER["REQUEST_URI"]:'-')//phpunit test does not set REQUEST_URI
                . "] ";
        //gethostbyaddr($_SERVER['REMOTE_ADDR'])// co udělá s IP, která nelze přeložit? nebylo by lepší logovat přímo IP?
        if (($this->BackyardConf['error_log_message_type'] == 3) && !$this->BackyardConf['logging_file']) {//$logging_file not set and it should be
            $result = error_log($message_prefix . "(error: logging_file should be set!) $message"); //zapisuje do default souboru
            //zaroven by mohlo poslat mail nebo tak neco .. vypis na obrazovku je asi az krajni reseni
        } else {
            $messageType = 3;
            if ($this->BackyardConf['error_log_message_type'] == 0) {
                $messageType = $this->BackyardConf['error_log_message_type'];
            }
            if ($this->BackyardConf['log_monthly_rotation']) {
                $result = error_log($message_prefix . $message . (($messageType != 0) ? (PHP_EOL) : ('')), $messageType, "{$this->BackyardConf['logging_file']}." . date("Y-m") . ".log"); //zapisuje do souboru, který rotuje po měsíci
            } else {
                $result = error_log($message_prefix . $message . PHP_EOL, $messageType, "{$this->BackyardConf['logging_file']}"); //zapisuje do souboru
            }
        }
        if ($level == 1 && $this->BackyardConf['mail_for_admin_enabled']) {//mailto admin, 130108
            error_log($message_prefix . $message . PHP_EOL, 1, $this->BackyardConf['mail_for_admin_enabled']);
        }
    }
    return $result;
}

/* Alternative way:
  Logging levels
  Log level   Description                                                                       Set bit
  Warning     Identifies critical errors.                                                       None required
  Debug       Provides additional information for programmers and Technical Product Support.    0 (zero)
  Information Provides information on the health of the system.                                 1
  Trace       Provides detailed information on the execution of the code.                       2

  Log Mask values and logging levels
  LogMask   Bit value Messages included
  0         00000000  Warnings
  1         00000001  Warnings and Debug
  2         00000010  Warnings and Information
  3         00000011  Warnings, Debug and Information
  4         00000100  Warnings and Trace
  7         00000111  Warnings, Debug, Information and Trace
 */
    
    
    /**
     * 
     * @param string $errorNumber
     * @param string $errorString
     * @param string $feedbackButtonMarkup
     * @return void (die)
     */
    public function dieGraciously($errorNumber, $errorString, $feedbackButtonMarkup = false) {
        //global $backyardConf;
        $this->BackyardError->log(1, "Die with error {$errorNumber} - {$errorString}");
        if ($feedbackButtonMarkup) {
            echo("<html><body>" . str_replace(urlencode("%CUSTOM_VALUE%"), urlencode("Error {$errorNumber} - "
                            . (($this->BackyardConf['die_graciously_verbose']) ? " - {$errorString}" : "")
                    ), $feedbackButtonMarkup)); //<html><body> na začátku pomůže, pokud ještě výstup nezačal
        }
        die("Error {$errorNumber}" . (($this->BackyardConf['die_graciously_verbose']) ? " - {$errorString}" : ""));
    }

}
