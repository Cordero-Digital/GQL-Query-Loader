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
            if (!$fragmentPath) {
                throw new \Exception("Fragment file not found: " . $matches[1]);
            }
            if (isset(self::$loadedFragments[$fragmentPath])) {
                return ''; // Prevent duplicate loading
            }

            self::$loadedFragments[$fragmentPath] = true;
            return self::load($fragmentPath);
        }, $query);
    }

    protected static function replaceVariables(string $query, array $variables): string
    {
        $schemaDescription = self::buildDescription($variables);
        $query = preg_replace_callback('/{{\s*([\w,\s]+)\s*}}/', function ($matches) use ($variables, $schemaDescription) {
            $key = trim($matches[1]);
            if ($key === 'schemaDescription') {
                return $schemaDescription;
            }
            $keys = array_map('trim', explode(',', $key));
            $formattedVars = [];
            $variablesHash = array_reduce($variables, function($carry, $item) {
                $carry[$item['name']] = $item;
                return $carry;
            }, []);
            foreach ($keys as $k => $key) {;
                if (!isset($variablesHash[$key])) {
                    throw new \Exception("Variable \$$key is set in query but not in variables array");
                }
                $formattedVars[] = "\${$variablesHash[$key]['name']}: " . self::escapeValue($variablesHash[$key]['value']);
            }
            return '(' . join(', ', $formattedVars) . ')';
        }, $query);
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
