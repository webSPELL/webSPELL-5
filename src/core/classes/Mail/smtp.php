<?php
namespace Mail;
/**
 *
 * @author Philipp
 *
 */
class Smtp extends Mail\Base {

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
        if(!defined("CRLF")) {
            define("CRLF", "\r\n");
        }
        $configs = $this->mysqli->queryResults("SELECT `value`, `field` FROM ".DB_PREFIX."system_vars WHERE `field` LIKE 'mail.smtp.%'");
        foreach($configs as $config) {
            $key = str_replace("mail.smtp.", '', $config['field']);
            $value = $config['value'];
            if(isset($this->$key)) {
                $this->$key = $value;
            }
        }
        foreach($params as $key => $value) {
            if(isset($this->$key)) {
                $this->$key = $value;
            }
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
?>
