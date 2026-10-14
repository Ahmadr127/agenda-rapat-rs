<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SignatureStorageService
{
    public function storeUploadedFile(UploadedFile $file, string $directory = 'signatures'): string
    {
        $filename = $directory.'/'.Str::uuid().'.'.$file->getClientOriginalExtension();
        Storage::disk('public')->putFileAs($directory, $file, basename($filename));

        return $filename;
    }

    /**
     * Simpan hasil goresan signature pad (dataURL) sebagai file PNG/JPG.
     *
     * @throws ValidationException bila format tidak valid.
     */
    public function storeDataUrl(string $dataUrl, string $directory = 'signatures'): string
    {
        if (! preg_match('#^data:image/(png|jpe?g);base64,(.+)$#s', $dataUrl, $matches)) {
            throw ValidationException::withMessages([
                'signature_data' => 'Format tanda tangan digital tidak valid.',
            ]);
        }

        $data = base64_decode($matches[2], true);
        if ($data === false || $data === '') {
            throw ValidationException::withMessages([
                'signature_data' => 'Format tanda tangan digital tidak valid.',
            ]);
        }

        $path = $directory.'/'.Str::uuid().'.'.($matches[1] === 'png' ? 'png' : 'jpg');
        Storage::disk('public')->put($path, $data);

        return $path;
    }

    /**
     * Resolusi TTD resmi dari form master (pad digital > upload file >
     * hapus > pertahankan). Menghapus file lama bila diganti/dihapus.
     * Kunci request yang dipakai: signature_data, signature, remove_signature.
     */
    public function resolveOfficialSignature(Request $request, ?string $currentPath, string $directory): ?string
    {
        $dataUrl = $request->input('signature_data');
        if (is_string($dataUrl) && $dataUrl !== '') {
            if ($currentPath) {
                Storage::disk('public')->delete($currentPath);
            }

            return $this->storeDataUrl($dataUrl, $directory);
        }

        if ($request->hasFile('signature')) {
            if ($currentPath) {
                Storage::disk('public')->delete($currentPath);
            }

            return $request->file('signature')->store($directory, 'public');
        }

        if ($request->boolean('remove_signature') && $currentPath) {
            Storage::disk('public')->delete($currentPath);

            return null;
        }

        return $currentPath;
    }
}
