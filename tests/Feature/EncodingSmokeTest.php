<?php

namespace Tests\Feature;

use Tests\TestCase;

class EncodingSmokeTest extends TestCase
{
    public function test_fichiers_visibles_sans_mojibake(): void
    {
        $roots = [
            base_path('app'),
            base_path('resources'),
            base_path('routes'),
            base_path('config'),
        ];

        $files = collect($roots)
            ->flatMap(fn (string $root) => iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root))))
            ->filter(fn (\SplFileInfo $file) => $file->isFile())
            ->filter(fn (\SplFileInfo $file) => in_array($file->getExtension(), ['php', 'blade.php', 'md', 'css', 'js'], true))
            ->map(fn (\SplFileInfo $file) => $file->getPathname())
            ->push(base_path('README.md'))
            ->push(base_path('MAIN_CODE_DESIGN_REVIEW.md'))
            ->filter(fn (string $path) => is_file($path));

        $markers = ['Ã', 'Â', '�', 'â€', 'â”', 'â•', 'Ãƒ', 'Ã‚', 'ï¿½', 'Ã¢â‚¬', 'Ã¢â€', 'Ã¢â€¢'];
        $offenders = [];

        foreach ($files as $path) {
            $contents = file_get_contents($path);

            foreach ($markers as $marker) {
                if (str_contains($contents, $marker)) {
                    $offenders[] = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);
                    break;
                }
            }
        }

        $this->assertSame([], array_values(array_unique($offenders)));
    }
}
