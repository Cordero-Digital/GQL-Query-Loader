<?php

namespace CorderoDigital\GQLQueryLoader;

class Loader
{
    protected string $query;

    protected array $variables;

    public function loadQuery(string $filePath, array $variables = []): self
    {
        if (!file_exists($filePath)) {
            throw new \Exception("GraphQL file not found: " . $filePath);
        }

        self::validateVariables($variables);

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
        $loadedFragments = [];
        return preg_replace_callback('/#include\s+"([^"]+)"/', function ($matches) use ($basePath) {
            $fragmentPath = realpath($basePath . '/' . $matches[1]);
            if (!$fragmentPath) {
                throw new \Exception("Fragment file not found: " . $matches[1]);
            }
            if (isset($loadedFragments[$fragmentPath])) {
                return ''; // Prevent duplicate loading
            }

            $loadedFragments[$fragmentPath] = true;
            return (new self())->loadQuery($fragmentPath)->query();
        }, $query);
    }

    protected function replaceVariables(string $query, array $variables): string
    {
        $schemaDescription = $this->buildDescription($variables);
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
                $formattedVars[] = "\${$variablesHash[$key]['name']}: " . $this->escapeValue($variablesHash[$key]['value']);
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

    protected static function validateVariables(array $variables): void
    {
        foreach ($variables as $index => $variable) {
            // Ensure it's an array
            if (!is_array($variable)) {
                throw new \Exception("Variable at index $index must be an associative array.");
            }

            // Ensure required keys exist
            $requiredKeys = ['name', 'type', 'value'];
            foreach ($requiredKeys as $key) {
                if (!array_key_exists($key, $variable)) {
                    throw new \Exception("Variable at index $index is missing the required key '$key'.");
                }
            }

            // Validate 'name' (must be a string)
            if (!is_string($variable['name']) || empty(trim($variable['name']))) {
                throw new \Exception("Variable 'name' at index $index must be a non-empty string.");
            }

            // Validate 'type' (must be a string)
            if (!is_string($variable['type']) || empty(trim($variable['type']))) {
                throw new \Exception("Variable 'type' at index $index must be a non-empty string.");
            }

            // Validate 'value' (must be a scalar or null)
            if (!is_scalar($variable['value']) && !is_null($variable['value'])) {
                throw new \Exception("Variable 'value' at index $index must be a scalar value (string, int, float, bool, or null).");
            }
        }
    }
}
