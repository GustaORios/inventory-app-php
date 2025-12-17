<?php

namespace Src\Common;

use Src\Common\Response;
use Src\Common\Logger;

class AccessControl
{
    const ROLE_MANAGER  = 'manager';
    const ROLE_PICKER   = 'picker';
    const ROLE_SUPPLIER = 'supplier';
    const ROLE_ADMIN    = 'admin';

    public static function enforceRoles(\mysqli $conn, int $userId, array $allowedRoles): void
    {
        $stmt = $conn->prepare(
            "SELECT Role FROM users WHERE userId = ? AND IsActive = TRUE LIMIT 1"
        );

        if (!$stmt) {
            Logger::error("AccessControl@enforceRoles: DB prepare failed");
            Response::error("Internal Server Error", 500);
            exit;
        }

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            Logger::error("AccessControl@enforceRoles: User {$userId} not found or inactive");
            Response::error("Unauthorized", 401);
            exit;
        }

        $role = $user['Role'];

        if (!in_array($role, $allowedRoles, true)) {
            Logger::error(
                "AccessControl@enforceRoles: Forbidden for user {$userId} with role '{$role}'"
            );
            Response::error(
                "Forbidden: You do not have permission to perform this action.",
                403
            );
            exit;
        }
    }
}
