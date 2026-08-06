<?php

namespace Tests\Unit;

use App\Services\SignatureStorageService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SignatureStorageServiceTest extends TestCase
{
    public function test_stores_base64_signature_as_png_file(): void
    {
        Storage::fake('public');

        $service = new SignatureStorageService();

        // Create a minimal valid 1x1 pixel PNG in base64
        $pngData = base64_encode(hex2bin(
            '89504e470d0a1a0a0000000d49484452000000010000000108060000001f15c489' .
            '0000000a49444154789c626000000002000198e195290000000049454e44ae426082'
        ));
        $base64 = 'data:image/png;base64,' . $pngData;

        $path = $service->storeBase64($base64);

        $this->assertStringStartsWith('signatures/', $path);
        $this->assertStringEndsWith('.png', $path);
        Storage::disk('public')->assertExists($path);
    }
}
