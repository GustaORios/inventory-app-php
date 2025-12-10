<?php
namespace Src\Models;

class AuditLog {
    
    // Método estático para facilitar o uso em qualquer lugar do sistema
    public static function log($action, $details, $user_id = null) {
        // Formato do log
        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'action' => $action,
            'details' => $details,
            'user_id' => $user_id ?? 'GUEST'
        ];

        // POR ENQUANTO: Vamos salvar em um arquivo de texto simples para simular
        // No futuro, isso seria um: INSERT INTO audit_logs ...
        $logFile = __DIR__ . '/../../logs/audit.log';
        
        // Garante que a pasta logs existe
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0777, true);
        }

        file_put_contents($logFile, json_encode($entry) . PHP_EOL, FILE_APPEND);
        
        return true;
    }

    public function getAll() {
        // Lê o arquivo de log e retorna array
        $logFile = __DIR__ . '/../../logs/audit.log';
        if (!file_exists($logFile)) {
            return [];
        }
        
        $logs = [];
        $lines = file($logFile);
        foreach ($lines as $line) {
            $logs[] = json_decode($line, true);
        }
        return array_reverse($logs); // Mais recentes primeiro
    }
}