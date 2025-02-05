<?php

namespace CorderoDigital\QueryLoader;

class Loader
{
    protected static array $loadedFragments = [];

    public static function load(string $filePath, array $variables = []): string
    {
        if (!file_exists($filePath)) {
            throw new \Exception("GraphQL file not found: " . $filePath);
        }

        self::$loadedFragments = []; // Reset fragments tracking
        $query = file_get_contents($filePath);
        $query = self::processIncludes($query, dirname($filePath));

        return trim(self::replaceVariables($query, $variables));
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

    protected static function replaceVariables(string $query, array $variables): string
    {
        // $schemaArray = [];
        // foreach ($variables as $key => $value) {
            // $schemaArray[] = "\${$value['name']}: {$value['type']}";
            // $escapedValue = self::escapeValue($value);
            // $query = str_replace("{{ $key }}", $escapedValue, $query);
        // }
        $schemaDescription = self::buildDescription($variables);
        // $query = str_replace('{{ schemaDescription }}', $schemaDescription, $query);
        $query = preg_replace_callback('/{{\s*(schemaDescription)\s*}}/', function ($matches) use ($schemaDescription) {
            $key = trim($matches[1]); // Remove extra spaces
            return $schemaDescription;
        }, $query);
        foreach ($variables as $variable) {
            $escapedValue = self::escapeValue($variable['value']);
            $value = "( \${$variable['name']}: $escapedValue )";
            $query = str_replace("{{ {$variable['name']} }}", $value, $query);
        }
        return $query;
    }

    protected static function buildDescription(array $variables): string
    {
        $schemaArray = [];
        foreach ($variables as $key => $value) {
            $schemaArray[] = "\${$value['name']}: {$value['type']}";
        }
        return "(" . implode(', ', $schemaArray) . ")";
    }

    protected static function escapeValue(string | bool | null | int | float $value): string
    {
        if (is_string($value)) {
            return '"' . addslashes($value) . '"'; // Properly escape strings
        } elseif (is_bool($value)) {
            return $value ? 'true' : 'false';
        } elseif (is_null($value)) {
            return 'null';
        }
        return (string) $value;
    }
}
