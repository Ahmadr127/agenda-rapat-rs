<?php

namespace Tests\Unit;

use App\Services\SignatureStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SignatureStorageServiceTest extends TestCase
{
    public function test_stores_uploaded_signature_as_png_file(): void
    {
        Storage::fake('public');

        $service = new SignatureStorageService;

        $file = UploadedFile::fake()->image('signature.png', 300, 100);

        $path = $service->storeUploadedFile($file);

        $this->assertStringStartsWith('signatures/', $path);
        $this->assertStringEndsWith('.png', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_stores_signature_pad_data_url_as_file(): void
    {
        Storage::fake('public');

        $service = new SignatureStorageService;

        $path = $service->storeDataUrl(
            'data:image/png;base64,'.base64_encode('fake-png-bytes'),
            'user-signatures'
        );

        $this->assertStringStartsWith('user-signatures/', $path);
        $this->assertStringEndsWith('.png', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_rejects_invalid_signature_data_url(): void
    {
        Storage::fake('public');

        $this->expectException(ValidationException::class);

        (new SignatureStorageService)->storeDataUrl('bukan-data-url');
    }

    private function paddedPngDataUrl(int $w = 400, int $h = 200): string
    {
        $img = imagecreatetruecolor($w, $h);
        imagefill($img, 0, 0, imagecolorallocate($img, 255, 255, 255));
        imagefilledrectangle($img, 150, 70, 250, 130, imagecolorallocate($img, 0, 0, 0));
        ob_start();
        imagepng($img);
        $bin = (string) ob_get_clean();
        imagedestroy($img);

        return 'data:image/png;base64,'.base64_encode($bin);
    }

    public function test_autocrops_empty_margins_on_save(): void
    {
        Storage::fake('public');

        $path = (new SignatureStorageService)->storeDataUrl($this->paddedPngDataUrl(), 'user-signatures');

        [$w, $h] = getimagesize((string) Storage::disk('public')->path($path));
        // 400x200 terpangkas: tinta 101x61 + padding, jauh lebih kecil.
        $this->assertLessThan(400, $w);
        $this->assertLessThan(200, $h);
        $this->assertGreaterThan(100, $w);
        $this->assertGreaterThan(60, $h);
    }

    public function test_blank_image_is_stored_untouched(): void
    {
        Storage::fake('public');

        $img = imagecreatetruecolor(300, 100);
        imagefill($img, 0, 0, imagecolorallocate($img, 255, 255, 255));
        ob_start();
        imagepng($img);
        $bin = (string) ob_get_clean();
        imagedestroy($img);

        $path = (new SignatureStorageService)->storeDataUrl(
            'data:image/png;base64,'.base64_encode($bin),
            'user-signatures'
        );

        [$w, $h] = getimagesize((string) Storage::disk('public')->path($path));
        $this->assertSame(300, $w);
        $this->assertSame(100, $h);
    }
}
