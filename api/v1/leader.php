<?php

class Leadership{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    function GetLeaderBoard(){
        $totalStudents = function () {
            $query = "SELECT COUNT(*) AS count FROM users";
            $stmt = $this->db->prepare($query);

            if ($stmt === false) {
                return Response::json(500, [
                    'status' => 'error',
                    'message' => 'Query preparation failed: ' . $this->db->error
                ]);
            }
            
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $res = $result->fetch_assoc();
                    return $res['count'];
                }
            }
        };

        return Response::json(200, [
            'status' => 'success',
            'total_students' => $totalStudents()
        ]);
    }
}

?>