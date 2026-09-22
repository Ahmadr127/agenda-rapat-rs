<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SignatureStorageService
{
    public function storeUploadedFile(UploadedFile $file, string $directory = 'signatures'): string
    {
        $filename = $directory.'/'.Str::uuid().'.'.$file->getClientOriginalExtension();
        Storage::disk('public')->putFileAs($directory, $file, basename($filename));

        return $filename;
    }
}
