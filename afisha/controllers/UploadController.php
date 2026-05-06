<?php
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../helpers/Response.php';

class UploadController {

    private string $baseDir = __DIR__ . '/../../frontend/uploads/';

    private function baseUrl(): string {
        $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host  = $_SERVER['HTTP_HOST'] ?? 'localhost:8888';
        return $proto . '://' . $host . '/frontend/uploads/';
    }
    private array  $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    private int    $maxSize = 5 * 1024 * 1024; // 5 MB

    // POST /api/upload/avatar
    public function avatar(): void {
        $this->upload('avatars');
    }

    // POST /api/upload/event
    public function event(): void {
        $this->upload('events');
    }

    // POST /api/upload/venue
    public function venue(): void {
        $this->upload('events'); // переиспользуем папку events для площадок
    }

    private function upload(string $folder): void {
        Auth::require();

        if (empty($_FILES['file'])) {
            Response::error('Файл не передан');
        }

        $file = $_FILES['file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            Response::error('Ошибка загрузки файла: ' . $file['error']);
        }
        if ($file['size'] > $this->maxSize) {
            Response::error('Файл слишком большой (макс. 5 МБ)');
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, $this->allowed)) {
            Response::error('Допустимые форматы: JPEG, PNG, WebP, GIF');
        }

        $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        $ext      = $extMap[$mime] ?? 'jpg';
        $filename = uniqid($folder[0] . '_', true) . '.' . $ext;
        $dir      = $this->baseDir . $folder . '/';
        $dest     = $dir . $filename;

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            Response::error('Не удалось сохранить файл');
        }

        Response::success(['url' => $this->baseUrl() . $folder . '/' . $filename], 'Файл загружен', 201);
    }
}
