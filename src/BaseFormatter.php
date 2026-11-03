<?php

namespace JamesSkinner12\Formatter;

abstract class BaseFormatter
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->initialCleanup();
    }

    /**
     * This will return a flattened key => type array that uses '|' pipe notation
     * Would return ["contents|FilingSummary|id" => "int", "contents|FilingSummary|name" => "string]
     */
    public function pipeSchema(array $array = [], String $prefix = ''): array
    {
        if (empty($array)) {
            $array = $this->data;
        }
        $schema = [];
        foreach ($array as $key => $value) {
            $fullKey = $prefix === '' ? (string)$key : "{$prefix}|{$key}";
            if (is_array($value) && !empty($value)) {
                $schema = array_merge($schema, $this->pipeSchema($value, $fullKey));
            } else {
                $schema[$fullKey] = gettype($value);
            }
        }
        return $schema;
    }

    /**
     * Returns the keys and types of the entire data object while maintaining its structure
     * You are able to specify the limit (how deep to dive).
     * A value of -1 means no limit
     */
    public function hierarchicalSchema(array $array, Int $limit = -1, Int $depth = 0): array
    {
        $schema = [];
        if ($limit == -1 || $depth < $limit) {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $schema[$key] = $this->hierarchicalSchema($value, $limit, $depth + 1);
                } else {
                    $schema[$key] = gettype($value);
                }
            }
        }
        return $schema;
    }

    /**
     * Gets the item you are searching for (by pipe notation) and removes it from the body
     * 
     */
    public function pluckItem(String $path, $default = null)
    {
        $record = $this->getItem($path);
        $this->removeItem($path);
        return $record;
    }

    /**
     * Finds an entry based off the provided path and returns it (or null)
     */
    public function getItem(String $path, $default = null)
    {
        return $this->searchPath($this->data, explode("|", $path));
    }

    /**
     * Used to search for nested keys in an array with wildcard functionality
     * 
     * $this->searchPath($data, ["contents", "*", "FilingSummary"])
     * This would return all of the data matching the path
     */
    public function searchPath(mixed $array, array $keys, mixed $default = null)
    {
        if (empty($keys)) {
            return $array;
        }
        if (!is_array($array)) {
            return $default;
        }
        $key = array_shift($keys);
        if ($key === '*') {
            $results = [];
            foreach ($array as $subArray) {
                $res = $this->searchPath($subArray, $keys, $default);
                if (is_array($res)) {
                    $results = array_merge($results, $res);
                } elseif ($res !== $default) {
                    $results[] = $res;
                }
            }
            return !empty($results) ? $results : $default;
        }
        if (array_key_exists($key, $array)) {
            return $this->searchPath($array[$key], $keys, $default);
        }
        return $default;
    }


    /**
     * Moves an entry from one path to another using pipe notation
     */
    public function moveItem(String $originalPath, String $newPath)
    {
        $entry = $this->pluckItem($originalPath);
        $this->putItem($newPath, $entry);
        return true;
    }

    /**
     * Removes a given section of the array based off the provided pipe schema
     * $this->removeItem("contents|FilingSummary|BaseTaxonomies") <- example
     */
    public function removeItem(string $path)
    {
        $array = &$this->data;
        $keys = explode('|', $path);
        if (str_contains($path, "*")) {
            return $this->removePath($array, $keys);
        }
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($array[$key]) || !is_array($array[$key])) {
                return false;
            }
            $array = &$array[$key];
        }
        $lastKey = array_shift($keys);
        if (array_key_exists($lastKey, $array)) {
            unset($array[$lastKey]);
            return true;
        }
        return false;
    }


    /**
     * Similar to the searchPath method, but with added functionality to delete
     * records for the provided wildcard paths
     * 
     * $this->removeItem("contents|*|FilingSummary|MyReports|Report|*|@attributes");
     * This would remove the @attributes array from every Report entry record
     */
    public function removePath(mixed &$array, array $keys): bool
    {
        if (!is_array($array)) {
            return false;
        }
        $key = array_shift($keys);
        if (empty($keys)) {
            if ($key === '*') {
                $array = [];
                return true;
            }
            if (array_key_exists($key, $array)) {
                unset($array[$key]);
                return true;
            }
            return false;
        }
        if ($key === '*') {
            $removedAny = false;
            foreach ($array as &$subArray) {
                if ($this->removePath($subArray, $keys)) {
                    $removedAny = true;
                }
            }
            return $removedAny;
        }
        if (array_key_exists($key, $array)) {
            return $this->removePath($array[$key], $keys);
        }
        return false;
    }

    /**
     * Remove entries from dataset that have an empty final key
     */
    public function purgeEmptyEntries()
    {
        foreach ($this->pipeSchema($this->data) as $entryKey => $entryValue) {
            if (str_ends_with($entryKey, "|")) {
                $this->removeItem($entryKey);
            }
        }
        return true;
    }

    /**
     * Inserts the provided $value at the provided $path (using pipe notation)
     */
    public function putItem(string $path, mixed $value)
    {
        $keys = explode("|", $path);
        $current = &$this->data;
        foreach ($keys as $key) {
            if (!isset($current[$key]) || !is_array($current[$key])) {
                $current[$key] = [];
            }
            $current = &$current[$key];
        }
        $current = $value;
        return true;
    }

    /**
     * Abstract function to force extending classes to utilize this on class instantiation
     * 
     * Should be used to do default things like run purgeEmptyEntries() or other custom cleanup
     */
    abstract protected function initialCleanup();
}

