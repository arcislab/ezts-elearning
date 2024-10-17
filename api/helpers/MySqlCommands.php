<?php

class SqlHelper
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function executeQuery($query, $types = null, $params = null)
    {
        try {
            // Prepare the query
            $stmt = $this->db->prepare($query);

            if ($stmt === false) {
                return [
                    500,
                    [
                        'status' => 'error',
                        'message' => 'Query preparation failed: ' . $this->db->error
                    ]
                ];
            }

            // Bind parameters if provided
            if ($types !== null && $params !== null) {
                // Ensure $params is passed by reference (required by bind_param)
                $params_ref = [];
                foreach ($params as &$param) {
                    $params_ref[] = &$param;  // Bind parameters by reference
                }

                // Bind parameters to the statement
                $stmt->bind_param($types, ...$params_ref);
            }

            if ($stmt->execute()) {
                // return json_encode([
                //     'status' => 'success',
                //     'message' => 'Operation successful',
                //     'affected_rows' => $stmt->affected_rows
                // ]);
                return [
                    200,
                    [
                        'status' => 'success',
                        'message' => 'Operation successful',
                        'affected_rows' => $stmt->affected_rows
                    ]
                ];
            } else {
                // return json_encode(['status' => 'error', 'message' => $stmt->error]);
                return [
                    501,
                    ['status' => 'error', 'message' => $stmt->error]
                ];
            }
        } catch (PDOException $e) {
            // return json_encode(['error' => $e->getMessage()]);
            return [
                501,
                ['status' => 'error', 'message' => $e->getMessage()]
            ];
        }
    }
}
