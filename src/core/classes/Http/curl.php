<?php
class HttpRequestCURL extends HttpRequestInterface {
    /**
     * @see HttpRequest_Core::init();
     */
    public function init() {
        if(function_exists('curl_init') == false || extension_loaded('curl') == false) {
            throw new WebspellException("HttpRequest Error: CURL Modul not loaded");
        }
        if(in_array(@ini_get('allow_url_fopen'), array("On", 1)) == false) {
            throw new WebspellException("HttpRequest Error: URL Open not allowed");
        }
    }
    /**
     * prepare a curl connection with needed
     * settings
     */
    private function prepareConnection() {
        $this->connection = curl_init();
        curl_setopt($this->connection, CURLOPT_HEADER, 0);
        curl_setopt($this->connection, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->connection, CURLOPT_URL, $this->options->protocol."://".$this->options->host.":".$this->options->port."/".$this->options->path);
        curl_setopt($this->connection, CURLOPT_USERAGENT, $this->options->useragent);
        curl_setopt($this->connection, CURLOPT_FOLLOWLOCATION, 1);
        if(!empty($this->headers)) {
            curl_setopt($this->connection, CURLOPT_HTTPHEADER, $this->options->headers);
        }
    }
    /**
     * @see HttpRequest::get();
     * @return mixed
     */
    public function get() {
        $this->prepareConnection();
        curl_setopt($this->connection, CURLOPT_POST, 0);
        $return = curl_exec($this->connection);
        curl_close($this->connection);
        return $return;
    }
    /**
     * @see HttpRequest::post();
     * @return mixed
     */
    public function post() {
        $this->prepareConnection();
        curl_setopt($this->connection, CURLOPT_POST, 1);
        curl_setopt($this->connection, CURLOPT_POSTFIELDS, $this->options->data);
        $return = curl_exec($this->connection);
        curl_close($this->connection);
        return $return;
    }
}
?>
