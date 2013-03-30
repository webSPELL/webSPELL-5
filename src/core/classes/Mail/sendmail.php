<?php
/**
 *
 * @author Philipp
 *
 */
class MailSendmail extends MailInterface {

    /**
     * Constructor Method
     * @param array $params
     */
    public function __construct($params) {
        if (!defined("CRLF")) {
            define("CRLF", "\r\n");
        }
        foreach ($params as $key => $value) {
            if (isset($this->$key)) {
                $this->$key = $value;
            }
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
