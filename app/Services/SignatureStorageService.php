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
     * Tepi kosong otomatis dipangkas (autocrop).
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

        $path = $directory.'/'.Str::uuid().'.png';
        Storage::disk('public')->put($path, $this->autocrop($data) ?? $data);

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

            $raw = @file_get_contents($request->file('signature')->getPathname());
            $cropped = is_string($raw) ? $this->autocrop($raw) : null;
            if ($cropped !== null) {
                $path = $directory.'/'.Str::uuid().'.png';
                Storage::disk('public')->put($path, $cropped);

                return $path;
            }

            return $request->file('signature')->store($directory, 'public');
        }

        if ($request->boolean('remove_signature') && $currentPath) {
            Storage::disk('public')->delete($currentPath);

            return null;
        }

        return $currentPath;
    }

    /**
     * Pangkas tepi kosong (transparan atau putih) gambar TTD.
     * Mengembalikan bytes PNG hasil crop, atau null bila gambar
     * tidak valid/kosong (pemanggil memakai data asli).
     */
    public function autocrop(string $binary): ?string
    {
        $img = @imagecreatefromstring($binary);
        if ($img === false) {
            return null;
        }
        if (! imageistruecolor($img)) {
            imagepalettetotruecolor($img);
        }

        $w = imagesx($img);
        $h = imagesy($img);
        $minX = $w;
        $minY = $h;
        $maxX = -1;
        $maxY = -1;

        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $c = imagecolorat($img, $x, $y);
                $alpha = ($c >> 24) & 0x7F; // 0 buram - 127 transparan (GD)
                $r = ($c >> 16) & 0xFF;
                $g = ($c >> 8) & 0xFF;
                $b = $c & 0xFF;
                $isBackground = $alpha > 110 || ($r > 240 && $g > 240 && $b > 240);
                if (! $isBackground) {
                    if ($x < $minX) {
                        $minX = $x;
                    }
                    if ($y < $minY) {
                        $minY = $y;
                    }
                    if ($x > $maxX) {
                        $maxX = $x;
                    }
                    if ($y > $maxY) {
                        $maxY = $y;
                    }
                }
            }
        }

        if ($maxX < 0) {
            imagedestroy($img);

            return null; // kanvas kosong
        }

        $pad = max(8, (int) (max($maxX - $minX + 1, $maxY - $minY + 1) * 0.08));
        $x = max(0, $minX - $pad);
        $y = max(0, $minY - $pad);
        $cropW = min($w - $x, $maxX - $minX + 1 + $pad * 2);
        $cropH = min($h - $y, $maxY - $minY + 1 + $pad * 2);

        $cropped = imagecrop($img, ['x' => $x, 'y' => $y, 'width' => $cropW, 'height' => $cropH]);
        imagedestroy($img);
        if ($cropped === false) {
            return null;
        }

        imagesavealpha($cropped, true);
        ob_start();
        imagepng($cropped);
        $out = (string) ob_get_clean();
        imagedestroy($cropped);

        return $out !== '' ? $out : null;
    }
}
