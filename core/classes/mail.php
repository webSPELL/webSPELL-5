<?php
class Mail {
private $mysqli;
	private $registry;
	public function __construct(){
		$this->registry = Regstry::single();
		$this->mysqli = $this->registry->get('db');
		$this->type = $this->mysqli->querySingleValue("SELECT `value` FROM ".DB_PREFIX."system_vars WHERE `field`='mail.type'");
		if(class_exists($this->type)){
			$this->handle = new $this->type();
		}
	}
	public function __call($name, $arguments) {
		if(method_exists($this->handle,$name)){
			return call_user_func_array(array($this->handle,$name),$arguments);
		}
		else{
			throw new Exception("MAIL ERROR: Unknown Method "+$name);
		}
	}
}
abstract class Mail_Core {
	
  /**
   * Holds an array of all receivers
   * @var array
   */
  protected $receivers = array ();
	
  /**
   * Holds a string with the email of the sender
   * @var string
   */
  protected $from = '';
	
  /**
   * Holds a string with the email body
   * @var string
   */
  protected $body = null;
	
  /**
   * Holds a string with the email subject
   * @var string
   */
  protected $subject = null;
	
  /**
   * Holds an array of all additional headers
   * @var array
   */
  protected $headers = array ();
	
  /**
   * Activates debug mode
   * @var boolean
   */
  protected $debug = false;
	
  /**
   * Checks wheater address is valid and puts it to the receivers array
   * @return boolean
   * @param string $mail
   */
  public function addReceiver($mail) {
    if ($this->validateEmail($mail)) {
      $this->receivers[] = $mail;
      return true;
    }
    return false;
  }
	
  /**
   * Handles an array of addresses
   * @return
   * @param array $array
   */
  public function addReceivers($array) {
    if (is_array($array)) {
      foreach ($array as $user) {
        if (!$this->addReceiver($user[0])) return false;
      }
      return true;
    }
    return false;
  }
	
  /**
   * Clear the Receivers array
   * @return boolean
   */
  public function resetReceivers() {
    $this->receivers = array ();
    return true;
  }
	
  /**
   * Sets the content of the message
   * @return boolean
   * @param string $string
   */
  public function setBody($string) {
    if (! empty($string) && mb_strlen($string)) {
      $this->body = $string;
      return true;
    }
    return false;
  }
	
  /**
   * Sets the subject of the message
   * @return boolean
   * @param string $string
   */
  public function setSubject($string) {
    if (! empty($string) && mb_strlen($string)) {
      $this->subject = $string;
      return true;
    }
    return false;
  }
	
  /**
   * Adds a additional header to the mail
   * @return boolean
   * @param string $string
   */
  public function addHeader($string) {
    $this->headers[] = $string;
    return true;
  }
	
  /**
   * Handles an array of additional headers
   * @return boolean
   * @param array $array
   */
  public function addHeaders($array) {
    if (is_array($array)) {
      foreach ($array as $string) {
        $this->addHeader($string);
      }
      return true;
    }
    return true;
  }
	
  /**
   * Clear the Headers array
   * @return boolean
   */
  public function resetHeaders() {
    $this->headers = array ();
    return true;
  }
	
  /**
   * Static function to validate email address
   * @return boolean
   * @param string $email
   */
  public function validateEmail($email) {
    return preg_match("/^[a-z0-9_\.-]+@[a-z0-9_-]+\.[a-z0-9_\.-]+$/si", $email);
  }
	
  abstract function send();
	
}
class MailSmtp extends Mail_Core {
	
  /**
   * Holds the socket to the server
   * @var object
   */
  private $_connection = null;
	
  /**
   * Holds the smtp server host
   * @var string
   */
  private $host = 'localhost';
	
  /**
   * Holds the port of the smtp server
   * @var integer
   */
  private $port = 25;
	
  /**
   * @var boolean
   */
  private $auth = false;
	
  /**
   * Holds a string with the auth name
   * @var string
   */
  private $username = '';
	
  /**
   * Holds a string with the auth password
   * @var string
   */
  private $password = '';
	
  /**
   * Holds a string with the helo command
   * @var string
   */
  private $helo = 'localhost';
	
  /**
   * Holds an array of all messages from the servers
   * @var array
   */
  private $lines = array ();
	
  /**
   * Constructor Method
   * @param array $params
   */
  public function __construct($params) {
    if (!defined("CRLF"))define("CRLF", "\r\n");
   	$configs = $this->mysqli->queryResults("SELECT `value`, `field` FROM ".DB_PREFIX."system_vars WHERE `field` LIKE 'mail.smtp.%'");
    foreach($configs as $config){
    	$key = str_replace("mail.smtp.",'',$config['field']);
    	$value = $config['value'];
    	if ( isset($this->$key)) $this->$key = $value;
    }
    foreach ($params as $key => $value) {
      if ( isset($this->$key)) $this->$key = $value;
    }
  }
	
  /**
   * Destuctor Method
   * @return
   */
  public function __destruct() {
    if (is_resource($this->_connection)) {
      $this->writeLine("QUIT");
      fclose($this->_connection);
      $this->_connection = null;
    }
  }
	
  /**
   * Authentication at the mail server
   * @return
   */
  private function auth() {
    $this->writeLine("AUTH LOGIN");
    if ($this->checkStatus("334")) {
      $this->writeLine(base64_encode($this->username));
      if ($this->checkStatus("334")) {
        $this->writeLine(base64_encode($this->password));
      }
      else {
        throw new Exception("MAIL ERROR: Auth failed");
      }
    }
    else {
      throw new Exception("MAIL ERROR: Auth failed");
    }
  }
	
  /**
   * Sends the email
   * @return boolean
   */
  public function send() {
    if ($this->subject != null && $this->body != null) {
      if (count($this->receivers) > 0) {
        if (!is_resource($this->_connection)) {
          $this->_connection = @fsockopen($this->host, $this->port, $errno, $errstr, 3);
          $new = true;
        }
        else {
          $new = false;
        }
        if (is_resource($this->_connection)) {
          if ($new) {
            stream_set_timeout($this->_connection, 1, 0);
            $this->checkStatus("220");
            if ($this->auth == true) {
              $this->writeLine("EHLO ".$this->helo);
              $this->readLine(6);
              $this->auth();
              $need = "235";
            }
            else {
              $this->writeLine("HELO ".$this->helo);
              $need = "250";
            }
          }
          else {
            $need = "250";
          }
          if ($this->checkStatus($need)) {
            $this->writeLine("MAIL FROM:<".$this->from.">");
            if ($this->checkStatus("250")) {
              $to_string = array ();
              foreach ($this->receivers as $receiver) {
                $this->writeLine("RCPT TO:<".$receiver.">");
                $to_string[] = "<".$receiver.">";
                $this->readLine();
              }
              if ($this->checkStatus("250", false)) {
                $this->writeLine("DATA");
                $this->writeLine("From: <".$this->from.">");
                $this->writeLine("To: ".implode(",", $to_string)."");
                if (count($this->headers)) {
                  foreach ($this->headers as $header) {
                    $this->writeLine($header);
                  }
                }
                $this->writeLine("Subject: ".$this->subject);
                $this->writeLine("");
                $this->writeLine($this->body);
                $this->writeLine(".");
                if ($this->checkStatus("354")) {
                  return true;
                }
                else {
                  throw new Exception("MAIL ERROR: ".end($this->lines));
                }
              }
              else {
                throw new Exception("MAIL ERROR: ".end($this->lines));
              }
            }
            else {
              throw new Exception("MAIL ERROR: ".end($this->lines));
            }
          }
          else {
            throw new Exception("MAIL ERROR: ".end($this->lines));
          }
        }
        else {
          throw new Exception("MAIL ERROR: ".$errno." ".$errstr);
        }
      }
      else {
        throw new Exception("MAIL ERROR: No Receiver set");
      }
    }
    else {
      throw new Exception("MAIL ERROR: No Subject/Body set");
    }
  }
	
  /**
   * Read response from the server
   * @return string
   */
  private function readLine($num = 1) {
    for ($i = 0; $i < $num; $i++) {
      $line_index = count($this->lines);
      $line = '';
      while ($str = @fgets($this->_connection, 515)) {
        $line .= $str;
        if (substr($line, 3, 1) == " ") {
          break;
        }
      }
      $line = trim($line);
      if ($line != "") {
        $this->lines[$line_index] = $line;
      }
    }
    return $line;
  }
	
  /**
   * Sends a message to the server
   * @return boolean
   * @param string $data
   */
  private function writeLine($data) {
    return fwrite($this->_connection, $data.CRLF, strlen($data)+2);
  }
	
  /**
   * Checks the response code from the last message
   * @return boolean
   * @param string $needed The expected Code
   * @param boolean $new[optional] Read new message from server, or took the last one
   */
  private function checkStatus($needed, $new = true) {
    $string = ($new == true)
				    ? $this->readLine()
				    : end($this->lines);
      return (substr($string, 0, 3) === $needed);
  }
}
class MailSendmail extends Mail_Core {
	
  /**
   * Constructor Method
   * @param array $params
   */
  public function __construct($params) {
    if (!defined("CRLF"))define("CRLF", "\r\n");
    foreach ($params as $key => $value) {
      if ( isset($this->$key)) $this->$key = $value;
    }
  }
	
  /**
   * Sends the email
   * @return boolean
   */
  public function send() {
    if ($this->subject != null && $this->body != null) {
      if (count($this->receivers) > 0) {
        $header = 'From: <'.$this->from.'>'.CRLF;
        foreach ($this->headers as $header_s) {
          $header .= $header_s.CRLF;
        }
        $to_data = array ();
        foreach ($this->receivers as $receiver) {
          $to_data[] = "<".$receiver.">";
        }
        return mail(implode(",", $to_data), $this->subject, $this->body, $header);
      }
      else {
        throw new Exception("MAIL ERROR: No Receiver set");
      }
    }
    else {
      throw new Exception("MAIL ERROR: No Subject/Body set");
    }
  }
}
?>