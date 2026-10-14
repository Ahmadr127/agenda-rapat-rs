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
}
