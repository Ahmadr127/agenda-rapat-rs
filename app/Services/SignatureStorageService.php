<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SignatureStorageService
{
    public function storeBase64(string $base64Data, string $directory = 'signatures'): string
    {
        $base64Data = preg_replace('#^data:image/\w+;base64,#i', '', $base64Data);
        $imageData = base64_decode($base64Data);

        $filename = $directory . '/' . Str::uuid() . '.png';
        Storage::disk('public')->put($filename, $imageData);

        return $filename;
    }
}
