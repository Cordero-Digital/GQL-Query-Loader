<?php

namespace CorderoDigital\GQLQueryLoader;

class Loader
{
    protected static array $loadedFragments = [];

    protected string $query;

    protected array $variables;

    public function loadQuery(string $filePath, array $variables = [], bool $isFragment = false): self
    {
        if (!file_exists($filePath)) {
            throw new \Exception("GraphQL file not found: " . $filePath);
        }

        if (!$isFragment) {
            self::$loadedFragments = [];
        }

        $this->variables = $variables;
        $query = file_get_contents($filePath);
        $query = self::processIncludes($query, dirname($filePath));
        $this->query = trim($this->replaceVariables($query, $this->variables));

        return $this;
    }

    public function query(): string
    {
        return $this->query;
    }

    public function variables(): array
    {
        return $this->variables;
    }

    protected static function processIncludes(string $query, string $basePath): string
    {
        return preg_replace_callback('/#include\s+"([^"]+)"/', function ($matches) use ($basePath) {
            $fragmentPath = realpath($basePath . '/' . $matches[1]);
            if (!$fragmentPath) {
                throw new \Exception("Fragment file not found: " . $matches[1]);
            }
            if (isset(self::$loadedFragments[$fragmentPath])) {
                return $matches[0] . ' - duplicate'; // Prevent duplicate loading
            }

            self::$loadedFragments[$fragmentPath] = true;
            return (new self())->loadQuery(filePath: $fragmentPath, isFragment: true)->query();
        }, $query);
    }

    protected function replaceVariables(string $query, array $variables): string
    {
        $query = preg_replace_callback('/{{\s*([\w,\s]+)\s*}}/', function ($matches) use ($variables) {
            $key = trim($matches[1]);
            $keys = array_map('trim', explode(',', $key));
            $formattedVars = [];
            foreach ($keys as $k => $key) {
                if (!array_key_exists($key, $variables)) {
                    continue;
                }
                $formattedVars[] = "$key: " . $this->escapeValue($variables[$key]);
            }
            if (empty($formattedVars)) {
                return '';
            }
            return '(' . join(', ', $formattedVars) . ')';
        }, $query);
        return $query;
    }

    protected function buildDescription(array $variables): string
    {
        $schemaArray = [];
        foreach ($variables as $key => $value) {
            $schemaArray[] = "\${$value['name']}: {$value['type']}";
        }
        return "(" . implode(', ', $schemaArray) . ")";
    }

    protected function escapeValue(string | bool | null | int | float $value): string
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
