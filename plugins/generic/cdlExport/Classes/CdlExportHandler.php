<?php

import('classes.handler.Handler');

class CdlExportHandler extends Handler {
    /**
     *
     */
    public function api() {
        $this->handleRequest(\CdlExportPlugin\Command\Api::class);
    }

    /**
     *
     */
    public function journals() {
        $this->handleRequest(\CdlExportPlugin\Command\Journals::class);
    }

    /**
     * @param $class
     */
    protected function handleRequest($class) {
        $requestUri = '/' . implode('/', array_slice(explode('/', $_SERVER['REQUEST_URI']), 5));
        $api = new $class([$requestUri]);
        $out = $api->execute();

        // This is the start of a what might be a response object. Not sure we need it yet. Only using in
        // Api/Journals/Articles/Digest/Emails currently
        if(is_object($out) && property_exists($out, '__format__')) {
            $format = in_array($out->__format__, ['json', 'txt']) ? $out->__format__ : 'json';
            $data = $out->data;
        } else {
            $format = 'json';
            $data = $out;
        }
        if($format == 'txt') header('Content-Type: text/plain');
        if($format == 'json') header('Content-Type: text/json');

        echo $format == 'json' ? json_encode($data) : $data;
    }
}