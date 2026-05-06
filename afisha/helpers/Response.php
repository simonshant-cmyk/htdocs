<?php
class Response {
    public static function json(mixed $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function success(mixed $data, string $message = 'OK', int $code = 200): void {
        self::json(['success' => true, 'message' => $message, 'data' => $data], $code);
    }

    public static function error(string $message, int $code = 400): void {
        self::json(['success' => false, 'error' => $message], $code);
    }

    public static function notFound(string $message = 'Not found'): void {
        self::error($message, 404);
    }
}
