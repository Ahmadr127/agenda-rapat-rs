<?php

namespace Tests\Unit;

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\EmployeeRecapController;
use ReflectionMethod;
use Tests\TestCase;

class CsvTextSanitizerTest extends TestCase
{
    public function test_sanitize_csv_text_replaces_newlines_and_condenses_whitespace(): void
    {
        foreach ($this->controllerClasses() as $controllerClass) {
            $controller = new $controllerClass();
            $method = new ReflectionMethod($controller, 'sanitizeCsvText');
            $method->setAccessible(true);

            $sanitized = $method->invoke($controller, "Baris satu\r\nBaris dua\n\nBaris\t tiga  ");

            $this->assertSame('Baris satu Baris dua Baris tiga', $sanitized);
        }
    }

    public function test_sanitize_csv_text_returns_empty_string_for_null_or_empty_value(): void
    {
        foreach ($this->controllerClasses() as $controllerClass) {
            $controller = new $controllerClass();
            $method = new ReflectionMethod($controller, 'sanitizeCsvText');
            $method->setAccessible(true);

            $this->assertSame('', $method->invoke($controller, null));
            $this->assertSame('', $method->invoke($controller, ''));
        }
    }

    /**
     * @return array<int, class-string>
     */
    private function controllerClasses(): array
    {
        return [
            AgendaController::class,
            EmployeeRecapController::class,
        ];
    }
}
