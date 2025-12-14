<?php

namespace Src\Controllers;
use Src\Models\User;
use Src\Common\Audit;
use Src\Common\Logger;
use Src\Common\Response;

class UserProviderController
{
    
    private $userModel;
    const ALLOWED_ROLES = ['Manager', 'Picker', 'Supplier'];

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function register()
    {
        try {

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
            if ($user !== null) {
                // session_start()
                //$_SESSION['userinfo'] = $user;
                $_SESSION['userinfo'] = [
                    'id'    => $user['userId'],
                    'email' => $user['Email'],
                    'role'  => $user['Role'],
                    'username' => $user['Username']
                ];
                Response::json(['message' => 'Login successful'], 200, "Welcome " . $_SESSION['userinfo']['email']);
                Logger::info("UserProviderController@login: User '{$_SESSION['userinfo']['email']}' logged in successfully");
            } else {
                Response::error("Invalid email or password.", 401);
                Logger::error("UserProviderController@login: Failed login attempt for email '{$input['email']}'");
                return;
            }
        } catch (\Exception $e) {
            Logger::error("UserProviderController@login: " . $e->getMessage());
            Response::error("Internal Server Error: " . $e->getMessage(), 500);
        }
    }

    public function logout()
    {
        try {
            if (isset($_SESSION['userinfo']['username'])) {
                $email = $_SESSION['userinfo']['username'];
                session_destroy();
                Response::json(['message' => 'Logout successful.'], 200, 'Bye '. $email);
            } else {
                Response::error("No active session found.", 400);
            }

        } catch (\Exception $e) {
            Logger::error("UserProviderController@logout: " . $e->getMessage());
            Response::error("Internal Server Error: " . $e->getMessage(), 500);
        }
    }
}
