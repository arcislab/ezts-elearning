<?php
require_once __DIR__ .'/../helpers/MySqlCommands.php';
require_once __DIR__ .'/../config/authSecurity.php';
require_once __DIR__ .'/../v1/user.php';
$userController = new User();

class Authentication
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    function Login($mobileNumber, $otp)
    {
        global $userController;
        if (ValidateOtp($mobileNumber, $otp)) {
            $userid = $userController->GetUserId($mobileNumber);
            if ($userid != -1) {
                $token = generateToken($userid);

                setcookie("token", $token, [
                    'expires' => time() + 86400,
                    'path' => '/',
                    'domain' => '148.135.137.222',
                    'secure' => false,         // No HTTPS in local development
                    'httponly' => true,        // Still keep HttpOnly for security
                    'samesite' => 'Lax'        // Use 'Lax' for typical scenarios
                ]);

                return Response::json(200, [
                    'status' => 'success',
                    'message' => 'Login successful',
                    // 'token' => $token
                ]);
            } else {
                $userController->CreateNewUser($mobileNumber);
                // return Response::json(401, [
                //     'status' => 'error',
                //     'message' => 'User ID not registered!'
                // ]);
            }
        } else {
            return Response::json(401, [
                'status' => 'error',
                'message' => 'Invalid otp'
            ]);
        }
    }

    function Logout()
    {
        // Destroy any session data if using PHP sessions
        session_unset();  // Remove all session variables
        session_destroy();  // Destroy the session

        // Set the 'token' cookie with an expired time to delete it
        setcookie("token", "", [
            'expires' => time() - 3600,  // Expire one hour ago
            'path' => '/',
            'domain' => '148.135.137.222',  // Match your cookie domain
            'secure' => false,         // Set to true if using HTTPS
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        // Return a response indicating logout success
        return Response::json(200, [
            'status' => 'success',
            'message' => 'Logout successful'
        ]);
    }

    function RedirectUser($userid)
    {
        global $userController;
        if ($userController->IsUserAdmin($userid)) {
            header("Location: http://148.135.137.222/app/admin/home.html");
            exit();
        } else {
            header("Location: http://148.135.137.222/app/dashboard.html");
            exit();
        }
    }

    function RequestOtp($mobileNumber)
    {
        return Response::json(200, [
            'status' => 'success',
            'message' => 'OTP requested'
        ]);
    }

    // Function reserved for future incase of userid and password authentication
    function validateCredentials($userid, $password)
    {
        $query = "SELECT mobile, password FROM users WHERE id = ?";

        $stmt = $this->db->prepare($query);
        if ($stmt === false) {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Database query preparation failed'
            ]);
        }

        $stmt->bind_param('s', $userid);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
