<?php
class Response
{
    public static function json($status, $data)
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo "\n" . json_encode($data);
        exit;
    }
}
