<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends ApiController
{
    private const MAX_SIZE    = 5 * 1024 * 1024;
    private const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    // Магические байты для проверки реального формата файла
    private const MAGIC = [
        'image/jpeg' => "\xFF\xD8\xFF",
        'image/png'  => "\x89PNG\r\n\x1a\n",
        'image/gif'  => 'GIF',
        'image/webp' => 'RIFF',
    ];

    public function avatar(Request $request): JsonResponse { return $this->upload($request, 'avatars'); }
    public function event(Request $request): JsonResponse  { return $this->upload($request, 'events'); }
    public function venue(Request $request): JsonResponse  { return $this->upload($request, 'venues'); }

    private function upload(Request $request, string $folder): JsonResponse
    {
        $file = $request->file('file');
        if (!$file || !$file->isValid()) return $this->error('Файл не передан или повреждён');
        if ($file->getSize() > self::MAX_SIZE) return $this->error('Файл слишком большой (макс. 5 МБ)');

        $mime = $file->getMimeType();
        if (!in_array($mime, self::ALLOWED_MIME)) {
            return $this->error('Допустимые форматы: JPEG, PNG, WebP, GIF');
        }

        // Проверяем реальный формат по магическим байтам (защита от подмены расширения)
        $handle = fopen($file->getRealPath(), 'rb');
        $header = fread($handle, 12);
        fclose($handle);

        $magic  = self::MAGIC[$mime];
        if (strncmp($header, $magic, strlen($magic)) !== 0) {
            return $this->error('Содержимое файла не соответствует его типу');
        }

        $path = $file->store($folder, 'public_frontend');
        $url  = Storage::disk('public_frontend')->url($path);

        return $this->success(['url' => $url], 201, 'Файл загружен');
    }
}
