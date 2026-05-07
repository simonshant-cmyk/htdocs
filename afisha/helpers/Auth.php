<?php
class Auth {
    private static string $secret = 'CHANGE_THIS_SECRET_KEY_32_CHARS!!';
    private static int $ttl = 86400; // 24 часа

    // Генерация JWT (без библиотек — вручную)
    public static function generateToken(array $payload): string {
        $header  = self::base64url(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload['exp'] = time() + self::$ttl;
        $payload['iat'] = time();
        $body    = self::base64url(json_encode($payload));
        $sig     = self::base64url(hash_hmac('sha256', "$header.$body", self::$secret, true));
        return "$header.$body.$sig";
    }

    public static function verifyToken(string $token): ?array {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        [$header, $body, $sig] = $parts;
        $expected = self::base64url(hash_hmac('sha256', "$header.$body", self::$secret, true));
        if (!hash_equals($expected, $sig)) return null;

        $payload = json_decode(self::base64urlDecode($body), true);
        if (!$payload || $payload['exp'] < time()) return null;

        return $payload;
    }

    // Получить payload из заголовка Authorization
    public static function fromRequest(): ?array {
        // Apache/FastCGI может класть заголовок в разные переменные
        $allHeaders = function_exists('getallheaders') ? (getallheaders() ?: []) : [];
        $header = $_SERVER['HTTP_AUTHORIZATION']
               ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
               ?? ($allHeaders['Authorization'] ?? '');
        if (!str_starts_with($header, 'Bearer ')) return null;
        return self::verifyToken(substr($header, 7));
    }

    // Middleware — прерывает выполнение если не авторизован
    public static function require(): array {
        $payload = self::fromRequest();
        if (!$payload) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        return $payload;
    }

    // Проверка роли
    public static function requireRole(array $payload, int $roleId): void {
        if (($payload['role_id'] ?? 0) !== $roleId) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }
    }

    private static function base64url(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64urlDecode(string $data): string {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
