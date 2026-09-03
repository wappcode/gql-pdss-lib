<?php

declare(strict_types=1);

namespace GPDCore\Shared;

class ImageB64
{
    public static function encode(string $path): string
    {
        $data = base64_encode(file_get_contents($path));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $path);
        finfo_close($finfo);

        return sprintf('data:%s;base64,%s', $mime, $data);
    }

    public static function decode(string $b64, string $targetPath): void
    {
        if (preg_match('/^data:(.*);base64,(.*)$/', $b64, $matches)) {
            $data = base64_decode($matches[2]);
            file_put_contents($targetPath, $data);
        }
    }
}
