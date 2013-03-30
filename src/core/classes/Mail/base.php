<?php
namespace Mail;
abstract class Base {

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
                if (!$this->addReceiver($user[0])) {
                    return false;
                }
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
?>
