<?php

namespace App\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;

trait UploadFileFromBase64
{
    protected function uploadFile(string $base64)
    {
        $file = Arr::last(explode(',', $base64));
        $decodedFile = base64_decode($file);

        $tempFile = tmpfile();
        $tempFilePath = stream_get_meta_data($tempFile)['uri'];

        file_put_contents($tempFilePath, $decodedFile);

        $file = new File($tempFilePath);
        $fileExtension = Arr::last(explode('/', mime_content_type($base64)));

        $uploadedFile = new UploadedFile($file->getPathname(), $file->getFilename() . ".{$fileExtension}", mime_content_type($base64), 0, true);

        app()->terminating(function () use ($tempFile) {
            fclose($tempFile);
        });

        return $uploadedFile;
    }
}
