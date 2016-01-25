<?php
/*
 * Copyright 2015 Scholica V.O.F.
 * Created by Thomas Schoffelen
 */

namespace Core;


class HTTPHelper {

    public $requestCount = 0;

    protected function http($method, $postData = array(), $cookiesIn = '') {
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => "",
            CURLOPT_AUTOREFERER => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_MAXREDIRS => 3,
            CURLINFO_HEADER_OUT => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_COOKIE => $cookiesIn
        );

        if (!empty($postData)) {
            $options[CURLOPT_CUSTOMREQUEST] = 'POST';
            $options[CURLOPT_POSTFIELDS] = json_encode($postData);
            $options[CURLOPT_HTTPHEADER] = array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($options[CURLOPT_POSTFIELDS])
            );
        }

        $this->requestCount++;

        $ch = curl_init($method);
        curl_setopt_array($ch, $options);
        $rough_content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        $header = curl_getinfo($ch);
        curl_close($ch);

        $header_content = substr($rough_content, 0, $header['header_size']);
        $body_content = trim(str_replace($header_content, '', $rough_content));
        $pattern = "#Set-Cookie:\\s+(?<cookie>[^=]+=[^;]+)#m";
        preg_match_all($pattern, $header_content, $matches);
        $cookiesOut = implode("; ", $matches['cookie']);

        $header['errno'] = $err;
        $header['errmsg'] = $errmsg;
        $header['headers'] = $header_content;
        $header['content'] = $body_content;
        $header['cookies'] = $cookiesOut;
        return (object)$header;
    }

}