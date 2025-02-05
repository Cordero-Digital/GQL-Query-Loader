<?php

namespace CorderoDigital\QueryLoader;

class Loader
{
    protected static array $loadedFragments = [];

    public static function load(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new \Exception("GraphQL file not found: " . $filePath);
        }

        self::$loadedFragments = []; // Reset fragments tracking
        $query = file_get_contents($filePath);

        return trim(self::processIncludes($query, dirname($filePath)));
    }

    protected static function processIncludes(string $query, string $basePath): string
    {
        return preg_replace_callback('/#include\s+"([^"]+)"/', function ($matches) use ($basePath) {
            $fragmentPath = realpath($basePath . '/' . $matches[1]);

            if (!$fragmentPath || isset(self::$loadedFragments[$fragmentPath])) {
                return ''; // Prevent duplicate loading
            }

            self::$loadedFragments[$fragmentPath] = true;
            return self::load($fragmentPath);
        }, $query);
    }
}
