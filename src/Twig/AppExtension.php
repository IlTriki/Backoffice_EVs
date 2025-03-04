<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('file_exists', [$this, 'fileExists']),
        ];
    }

    public function fileExists(string $path): bool
    {
        // Remove any leading slash from the asset path
        $path = ltrim($path, '/');
        
        // Check if the file exists in the public directory
        return file_exists($this->projectDir . '/public/' . $path);
    }
} 