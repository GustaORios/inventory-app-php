<?php
namespace Src\Models;

class AuditLog {
    public static function log($action, $details, $user_id = null) {
        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'action' => $action,
            'details' => $details,
            'user_id' => $user_id ?? 'GUEST'
        ];
        $logFile = __DIR__ . '/../../logs/audit.log';
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0777, true);
        }

        file_put_contents($logFile, json_encode($entry) . PHP_EOL, FILE_APPEND);
        
        return true;
    }

    public function getAll() {
        $logFile = __DIR__ . '/../../logs/audit.log';
        if (!file_exists($logFile)) {
            return [];
        }
        
        $logs = [];
        $lines = file($logFile);
        foreach ($lines as $line) {
            $logs[] = json_decode($line, true);
        }
        return array_reverse($logs); 
    }
}