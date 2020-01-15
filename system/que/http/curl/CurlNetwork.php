<?php
/**
 * Created by PhpStorm.
 * User: tolujimoh
 * Date: 30/11/2017
 * Time: 8:24 AM
 */

namespace que\http\curl;

abstract class CurlNetwork {

    /**
     * @param string $url
     * @param null $post
     * @param array $headers
     * @param int $timeout
     * @return array
     */
    protected function fetch(string $url, $post = null,
                          array $headers = [], int $timeout = 60): array {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if(count($headers) > 0){
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
        }

        if(!empty($post)){
            curl_setopt($ch,CURLOPT_POST,true);
            curl_setopt($ch,CURLOPT_POSTFIELDS, $post);
        }

        curl_setopt($ch,CURLOPT_TIMEOUT, $timeout);

        $content = curl_exec($ch);

        if ($content === false) {
            curl_close($ch);
            return [
                'status' => false,
                'response' => curl_error($ch)
            ];
        }

        // Initiate Retry
        $retry = 0;

        // Try again if it fails/times out
        while(curl_errno($ch) == CURLE_OPERATION_TIMEDOUT && $retry < 3) {
            $content = curl_exec($ch);
            $retry++;
            sleep($timeout);
        }

        if (curl_errno($ch)) {
            curl_close($ch);
            return [
                'status' => false,
                'response' => curl_error($ch)
            ];
        }

        curl_close($ch);
        return [
            'status' => true,
            'response' => $content
        ];
    }

}

