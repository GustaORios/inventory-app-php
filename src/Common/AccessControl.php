<?php

namespace Src\Common;

class AccessControl
{
    const ROLE_MANAGER = 'manager';
    const ROLE_PICKER = 'picker';
    const ROLE_SUPPLIER = 'supplier';
    const ROLE_ADMIN = 'admin';

    public static function enforceRoles(array $allowedRoles)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['userinfo']) || !isset($_SESSION['userinfo']['role'])) {
            Response::error("Unauthorized: You must be logged in.", 401);
            Logger::error("AccessControl@enforceRoles: Unauthorized access attempt.");
            exit; 
        }

        $userRole = $_SESSION['userinfo']['role'];

        if (!in_array($userRole, $allowedRoles)) {
            Response::error("Forbidden: You do not have permission to perform this action.", 403);
            Logger::error("AccessControl@enforceRoles: Forbidden access attempt by role '{$userRole}'.");
            exit;
        }
        
    }

   /* public static function hasRole(array $allowedRoles): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $role = $_SESSION['userinfo']['role'] ?? null;
        return in_array($role, $allowedRoles);
    }*/
}