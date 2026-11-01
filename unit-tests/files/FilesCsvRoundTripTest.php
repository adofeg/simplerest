<?php

namespace Boctulus\Simplerest\Tests\Files;

use Boctulus\Simplerest\Core\Libs\Files;
use PHPUnit\Framework\TestCase;

class FilesCsvRoundTripTest extends TestCase
{
    public function test_array_to_csv_round_trips_values_with_literal_backslashes(): void
    {
        $directory = sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . 'simplerest-csv-' . bin2hex(random_bytes(8));
        self::assertTrue(mkdir($directory));

        $path = $directory . DIRECTORY_SEPARATOR . 'round-trip.csv';
        $expectedRows = [
            [
                'trailing-backslash' => 'value\\',
                'quoted-value' => 'say "hello"',
                'separator-value' => 'left,right',
                'multiline-value' => "line one\nline two",
            ],
            [
                'trailing-backslash' => 'two\\',
                'quoted-value' => 'plain',
                'separator-value' => 'plain',
                'multiline-value' => 'plain',
            ],
        ];

        try {
            Files::arrayToCSV($path, $expectedRows);

            $parsed = Files::getCSV($path, ',', true, true);
            self::assertSame(array_keys($expectedRows[0]), $parsed['header']);
            self::assertSame($expectedRows, $parsed['rows']);

            $autoDetected = Files::getCSV($path, 'AUTO', true, true);
            self::assertSame($expectedRows, $autoDetected['rows']);

            $processedRows = [];
            Files::processCSV($path, 'AUTO', true, static function ($row) use (&$processedRows): void {
                $processedRows[] = $row;
            });

            self::assertSame($expectedRows, $processedRows);
        } finally {
            if (is_file($path)) {
                unlink($path);
            }

            rmdir($directory);
        }
    }
}
