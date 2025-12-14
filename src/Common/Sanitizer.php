<?php
namespace Src\Common;

class Sanitizer
{
    /**
     * Limpa uma string, removendo tags HTML e caracteres especiais.
     * @param string $data A string a ser limpa.
     * @return string A string limpa.
     */
    public static function cleanString($data): string
    {
        if (!is_string($data)) {
            // Se não for uma string (ex: número), apenas retorna o valor original.
            return $data; 
        }
        
        // Remove tags HTML, mas permite quebras de linha e espaços.
        $data = strip_tags($data);
        
        // Remove barras invertidas adicionadas por magic_quotes_gpc (obsoleto, mas seguro)
        $data = stripslashes($data);
        
        // Converte caracteres especiais para entidades HTML para prevenir XSS
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        
        // Trim para remover espaços em branco no início e fim
        return trim($data);
    }

    /**
     * Aplica a sanitização recursivamente em um array (POST/PUT/PATCH input).
     * @param array $data O array de dados de entrada.
     * @return array O array com todas as strings sanitizadas.
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
     * Valida se uma string é um formato de email válido.
     * Este é o método que estava faltando.
     * @param string $email O email a ser validado.
     * @return bool True se o email for válido, false caso contrário.
     */
    public static function validateEmail(string $email): bool
    {
        // Usa a função nativa do PHP para validar o formato de email
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}