<?php
require_once './api/helpers/MySqlCommands.php';

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    function GetUserDashboardDetails($userid)
    {
        // TODO: Kept pending for solving in future as this is time taking
        // Info to get:
        // 1. total courses enrolled
        // 2. active courses
        // 3. completed courses

        $query = "SELECT COUNT(DISTINCT c.id) AS enrolled_courses
                FROM courses c
                INNER JOIN courses_topics ct ON ct.courses_id = c.id
                INNER JOIN courses_sub_topics cst ON cst.courses_topics_id = ct.id
                INNER JOIN courses_sub_topics_user ctu ON ctu.courses_sub_topics_id = cst.id 
                WHERE ctu.users_id = ?";

        $stmt = $this->db->prepare($query);

        if ($stmt === false) {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Query preparation failed: ' . $this->db->error
            ]);
        }
        $stmt->bind_param('i', $userid);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $types = $result->fetch_all(MYSQLI_ASSOC);

            // Extract the type values into a new array
            // $data = array_column($types, 'type');

            return Response::json(200, [
                'status' => 'success',
                'data' => $types
            ]);
        } else {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Failed to retrieve courses.'
            ]);
        }
    }

    function GetUserInfo($userid)
    {
        $query = "SELECT * FROM users WHERE id = ?";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $userid);

        if ($stmt === false) {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Query preparation failed: ' . $this->db->error
            ]);
        }

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            // Fetch the first row from the result set
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc(); // Fetch a single associative array
                $data[] = [
                    "first_name" => $user['first_name'],
                    "last_name" => $user['last_name'],
                ];

                return Response::json(200, [
                    'status' => 'success',
                    'data' => $data
                ]);
            } else {
                return Response::json(404, [
                    'status' => 'error',
                    'message' => 'User not found'
                ]);
            }
        } else {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Query execution failed: ' . $stmt->error
            ]);
        }
    }

    function CheckIfUserExist($userId)
    {
        $query = "SELECT * FROM users WHERE id = ?";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $userId);

        if ($stmt === false) {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Query preparation failed: ' . $this->db->error
            ]);
        }

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                return true;
            }
        }
        return false;
    }

    function GetUserId($mobileNumber)
    {
        $query = "SELECT id FROM users WHERE mobile = ?";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('s', $mobileNumber);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            // Fetch the first row from the result set
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc(); // Fetch a single associative array
                return $user["id"];
            }
        }
        return -1;
    }

    function CreateNewUser($mobileNumber){
        $sqlHelp = new SqlHelper();
        $query = "INSERT INTO users (`mobile`, `otp_verified`) VALUES (?, ?)";
        $result = $sqlHelp->executeQuery($query, 'si', array($mobileNumber, true));
        return Response::json($result[0], $result[1]);
    }

    function IsUserAdmin($userId)
    {
        $query = "SELECT `type` FROM users WHERE id = ?";

        $stmt = $this->db->prepare($query);
        
        $stmt->bind_param('i', $userId);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            // Fetch the first row from the result set
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc(); // Fetch a single associative array
                if ($user['type'] == null) {
                    return false;
                } else {
                    return true;
                }
            }
        }
        return false;
    }
}
