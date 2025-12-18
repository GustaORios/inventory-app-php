<?php
namespace Src\Common;

class Sanitizer
{
    /**
     * Cleans a string by removing HTML tags, unescaping slashes, 
     * and converting special characters to prevent XSS.
     * * @param mixed $data
     * @return string 
     */
    public static function cleanString($data): string
    {
        // If data isn't a string (e.g., null or int), cast it to string to match the return type
        if (!is_string($data)) {
            return (string)$data; 
        }

        // Strip HTML and PHP tags to prevent code injection
        $data = strip_tags($data);

        // Remove backslashes often added by magic quotes or manual escaping
        $data = stripslashes($data);

        // Convert special characters (like <, >, &) to HTML entities to prevent XSS attacks
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

        // Remove unnecessary whitespace from the beginning and end of the string
        return trim($data);
    }

    /**
     * Deep cleans an entire array, processing both keys and values recursively.
     * * @param array $data
     * @return array 
     */
    public static function cleanArray(array $data): array
    {
        $cleaned = [];

        foreach ($data as $key => $value) {
            // Clean the key name itself to ensure the array structure is safe
            $cleaned_key = self::cleanString($key);
            
            // If the value is an array, call this method again (recursion)
            if (is_array($value)) {
                $cleaned[$cleaned_key] = self::cleanArray($value);
            } else {
                // Otherwise, treat the value as a standard string and clean it
                $cleaned[$cleaned_key] = self::cleanString($value);
            }
        }

        return $cleaned;
    }

    /**
     * Validates whether a string is a properly formatted email address.
     * * @param string $email
     * @return bool 
     */
    public static function validateEmail(string $email): bool
    {
        // Use PHP's built-in filter to check for valid email syntax
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}