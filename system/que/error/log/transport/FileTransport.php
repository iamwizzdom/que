<?php
/**
 * Created by PhpStorm.
 * User: wisdom Emenike
 * Date: 10/02/2022
 * Time: 12:25 PM
 */

namespace que\error\log\transport;

use que\common\exception\QueException;
use que\error\log\LoggerTransport;
use que\http\request\Request;
use que\utility\client\IP;

class FileTransport extends LoggerTransport
{

    public function log(): bool|int
    {
        // TODO: Implement log() method.

        $error = [
            'label' => $this->getLabel(),
            'message' => $this->getMessage(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'type' => $this->getType(),
            'level' => $this->getLevel(),
            'trace' => $this->getTrace(),
            'time' => [
                'int' => $this->getTimestamp(),
                'readable' => $this->getTime()
            ],
            'request' => [
                'url' => current_url(),
                'method' => Request::getMethod(),
                'ip' => IP::real()
            ]
        ];

        if (empty($this->getDestination())) {
            $this->setDestination(config('log.error.path') ?: (QUE_PATH . "/cache/files/error"));
        }

        $destination = rtrim($this->getDestination(), '/');

        if (!is_dir($destination)) {
            try {
                mk_dir($destination);
                sleep(1);
            } catch (QueException $e) {
                return false;
            }
        }

        $filename = (config("log.error.filename") ?:  "que-log") . "-" . date("Y-m-d") . ".json";

        $previous_errors = [];

        if (is_file("$destination/$filename")) {
            $logFile = file_get_contents("$destination/$filename");
            if ($logFile) {
                $previous_errors = json_decode($logFile, true);
            }
        }

        array_unshift($previous_errors, $error);

        $jsonError = json_encode($previous_errors, JSON_PRETTY_PRINT);

        return @file_put_contents("$destination/$filename", $jsonError);
    }
}