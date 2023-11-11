<?php
 
class CurlRequest
{
    private $url;
    private $ch;

    public function __construct($url)
    {
        $this->url = $url;
        $this->ch = curl_init();
    }

    public function sendRequest()
    {
        curl_setopt($this->ch, CURLOPT_URL, $this->url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($this->ch);

        if (curl_errno($this->ch)) {
            die('cURL 錯誤: ' . curl_error($this->ch));
        }

        curl_close($this->ch);

        return $response;
    }
}