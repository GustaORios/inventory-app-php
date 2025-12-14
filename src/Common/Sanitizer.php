<?php
namespace Src\Common;

class Sanitizer
{
    /**
     * @param string 
     * @return string 
     */
    public static function cleanString($data): string
    {
        if (!is_string($data)) {
            return $data; 
        }
        $data = strip_tags($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return trim($data);
    }

    /**
     * @param array 
     * @return array 
     */
    public static function cleanArray(array $data): array
    {
        $cleaned = [];
        foreach ($data as $key => $value) {
            $cleaned_key = self::cleanString($key);
            
            if (is_array($value)) {
                $cleaned[$cleaned_key] = self::cleanArray($value);
            } else {
                $cleaned[$cleaned_key] = self::cleanString($value);
            }
        }
        return $cleaned;
    }
    /**
     * @param string 
     * @return bool 
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}