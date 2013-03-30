<?php
class HttpRequestFOPEN extends HttpRequestInterface {
    /**
     * @see HttpRequest_Core::init();
     */
    public function init() {
        if(in_array(@ini_get('allow_url_fopen'), array("On", 1)) == false) {
            throw new WebspellException("HttpRequest Error: URL Open not allowed");
        }
        if(in_array(@ini_get('disabled_functions'), array("fsockopen", 1))) {
            throw new WebspellException("HttpRequest Error: fsockopen disabled");
        }
    }
    /**
     * Prepages a socket connection to the host
     * with settings
     */
    private function prepareConnection() {
        $this->connection = fsockopen($this->options->host, $this->options->port, $errno, $errstr, $this->options->timeout);
        if($this->connection) {
            stream_set_blocking($this->connection, 0);
            stream_set_timeout($this->connection, $this->options->timeout);
        }
        else {
            throw new WebspellException("HttpRequest Error: Can't connect to server");
        }
    }
    /**
     * @see HttpRequest::get();
     * @return mixed
     */
    public function get() {
        $this->prepareConnection();
        fwrite($this->connection, "GET ".$this->options->path." HTTP/1.0\r\n");
        fwrite($this->connection, "Host: ".$this->options->host."\r\n");
        fwrite($this->connection, "User-Agent: ".$this->options->useragent."\r\n");
        foreach($this->options->headers as $header) {
            fwrite($this->connection, $header."\r\n");
        }
        fwrite($this->connection, "Connection: Close\r\n\r\n");
        $return = "";
        while(!feof($this->connection)) {
            $return .= fgets($this->connection, 1024);
        }
        fclose($this->connection);
        return $return;
    }
    /**
     * @see HttpRequest::post();
     * @return mixed
     */
    public function post() {
        $this->prepareConnection();
        fwrite($this->connection, "POST ".$this->options->path." HTTP/1.0\r\n");
        fwrite($this->connection, "Host: ".$this->options->host."\r\n");
        fwrite($this->connection, "Content-Type: application/x-www-form-urlencoded;\r\n");
        fwrite($this->connection, "Content-Length: ".strlen($this->options->data)."\r\n");
        foreach($this->options->headers as $header) {
            fwrite($this->connection, $header."\r\n");
        }
        fwrite($this->connection, "User-Agent: ".$this->options->useragent."\r\n\r\n");
        fwrite($this->connection, $this->options->data);
        $return = "";
        while(!feof($this->connection)) {
            $return .= fgets($this->connection, 1024);
        }
        fclose($this->connection);
        $return = explode("\r\n\r\n", $return, 2);
        return $return[1];
    }
}
?>
