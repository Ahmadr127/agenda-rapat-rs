<?php

namespace Tests\Unit;

use App\Services\SignatureStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
}
