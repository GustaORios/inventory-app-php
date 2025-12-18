<?php

namespace Src\Controllers;

use Src\Common\AccessControl;
use Src\Models\User;
use Src\Common\Logger;
use Src\Common\Response;

use function Src\Common\generate_token;
use function Src\Common\store_access_token;
use function Src\Common\require_auth;
use function Src\Common\revoke_access_token;
use function Src\Common\get_bearer_token;

class UserProviderController
{
    private $userModel;
    const ALLOWED_ROLES = ['manager', 'picker', 'supplier', 'admin'];
    private $conn;

    public function __construct()
    {
        $this->userModel = new User();
        $this->conn = require __DIR__ . '/../Common/config.php';

        if (!($this->conn instanceof \mysqli)) {
            throw new \Exception("Database connection not available. Check config.php");
        }
    }

    public function __destruct()
    {
        if ($this->conn instanceof \mysqli) {
            $this->conn->close();
        }
    }

    public function register()
    {
        try {
            $userId = require_auth($this->conn);
            AccessControl::enforceRoles(
                $this->conn,
                $userId,
                [
                    AccessControl::ROLE_MANAGER,
                    AccessControl::ROLE_ADMIN
                ]
            );


            $input = json_decode(file_get_contents("php://input"), true);
            if (!$input) {
                Response::error("Invalid JSON body.", 400);
                return;
            }

            if (!in_array($input['role'], self::ALLOWED_ROLES)) {
                Response::error("Invalid role. Allowed: " . implode(', ', self::ALLOWED_ROLES), 400);
                return;
            }

            $requiredUser = ['username', 'email', 'password', 'role'];
            foreach ($requiredUser as $field) {
                if (!isset($input[$field]) || trim($input[$field]) === '') {
                    Response::error("Missing user field: {$field}", 400);
                    return;
                }
            }

            $userId = $this->userModel->create($input);
            Response::json(['userId' => $userId], 201, "User created successfully.");
        } catch (\Exception $e) {
            Logger::error("SupplierController@create: " . $e->getMessage());
            Response::error("Internal Server Error: " . $e->getMessage(), 500);
        }
    }

    public function login()
    {
        try {
            $input = json_decode(file_get_contents("php://input"), true);
            if (!$input) {
                Response::error("Invalid JSON body.", 400);
                return;
            }

            $requiredUser = ['email', 'password'];
            foreach ($requiredUser as $field) {
                if (!isset($input[$field]) || trim($input[$field]) === '') {
                    Response::error("Missing user field: {$field}", 400);
                    return;
                }
            }

            $user = $this->userModel->authenticate($input['email'], $input['password']);

            if ($user === null) {
                Response::error("Invalid email or password.", 401);
                Logger::error("UserProviderController@login: Failed login attempt for email '{$input['email']}'");
                return;
            }

            $rawToken = generate_token();
            store_access_token($this->conn, (int) $user['userId'], $rawToken);

            Response::json([
                'message' => 'Login successful',
                'access_token' => $rawToken,
                'token_type' => 'Bearer'
            ], 200, "Welcome " . $user['Email']);

            Logger::info("UserProviderController@login: User '{$user['Email']}' logged in successfully");

        } catch (\Exception $e) {
            Logger::error("UserProviderController@login: " . $e->getMessage());
            Response::error("Internal Server Error: " . $e->getMessage(), 500);
        }
    }

    public function logout()
    {
        try {
            $token = get_bearer_token();
            if ($token === null) {
                Response::error("Missing Bearer Token.", 401);
                return;
            }

            revoke_access_token($this->conn, $token);
            Response::json(['message' => 'Logout successful.'], 200, 'Token revoked.');
        } catch (\Exception $e) {
            Logger::error("UserProviderController@logout: " . $e->getMessage());
            Response::error("Internal Server Error: " . $e->getMessage(), 500);
        }
    }

    public function getMe()
    {
        try {
            $userId = require_auth($this->conn);

            Response::json(['userId' => $userId], 200, "Authenticated.");
        } catch (\Exception $e) {
            Logger::error("UserProviderController@getMe: " . $e->getMessage());
            Response::error("Internal Server Error: " . $e->getMessage(), 500);
        }
    }
}
