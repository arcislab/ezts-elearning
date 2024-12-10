<?php
require_once __DIR__ .'/../helpers/MySqlCommands.php';

class Orders
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    function GetOrders($userid)
    {
        $query = "SELECT c.name AS name, DATE_FORMAT(o.date, '%d-%m-%Y') AS date, c.author AS author, c.actual_price AS actual_price, (c.actual_price - c.discount_price) AS discount, c.discount_price AS total FROM orders o INNER JOIN courses c ON c.id = o.courses_id WHERE o.users_id = ?";
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
            if ($result->num_rows > 0) {
                $orders = $result->fetch_all(MYSQLI_ASSOC);
                $data = [];
                foreach ($orders as $order) {
                    $data[] = [
                        "course" => $order['name'],
                        "date" => $order['date'],
                        "author" => $order['author'],
                        "ap" => $order['actual_price'],
                        "discount" => $order['discount'],
                        "total" => $order['total']
                    ];
                }

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

    function CheckIfAlreadyPurchased($coursesId, $usersId)
    {
        $query = "SELECT COUNT(*) FROM orders WHERE courses_id = ? AND users_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ii', $coursesId, $usersId);

        if ($stmt === false) {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Query preparation failed: ' . $this->db->error
            ]);
        }

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $count = $result->fetch_row()[0]; // Get the count of matching rows

            return $count > 0; // Return true if count is greater than 0, else false
        } else {
            return false; // or handle error appropriately
        }
    }

    function AddOrder($usersId, $coursesId, $paymentId)
    {
        if ($this->CheckIfAlreadyPurchased($coursesId, $usersId)) {
            return Response::json(200, [
                'status' => 'success',
                'message' => "Course already purchased."
            ]);
        } else {
            $sqlHelp = new SqlHelper();
            $orderId = $this->GetOrderId();
            $query = "INSERT INTO `orders`(`date`, payment_id, `order_id`, `courses_id`, `users_id`, `total_amount`, `discount`) VALUES (CURDATE(), ?, ?, ?, ?, ?, ?)";
            $result = $sqlHelp->executeQuery($query, 'ssiiss', array($orderId, $paymentId, $coursesId, $usersId, $this->GetPaymentDetails($coursesId)["amount"], $this->GetPaymentDetails($coursesId)["discount"]));

            if ($result[0] === 200) {
                if ($result[0] === 200) {
                    $this->AssignSubTopicsToUser($orderId, $coursesId, $usersId);
                } else {
                    return Response::json($result[0], $result[1]);
                }
            } else {
                return Response::json($result[0], $result[1]);
            }
        }
    }

    function AssignSubTopicsToUser($orderId, $coursesId, $usersId)
    {
        $query = "SELECT cst.id FROM courses_sub_topics cst INNER JOIN courses_topics ct ON ct.id = cst.courses_topics_id WHERE ct.courses_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $coursesId);

        if ($stmt === false) {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Query preparation failed: ' . $this->db->error
            ]);
        }

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $subtopics = $result->fetch_all(MYSQLI_ASSOC);
                $insertValues = []; // Array to hold values for bulk insert

                foreach ($subtopics as $subtopic) {
                    // Prepare data for bulk insert
                    $subtopicId = $subtopic['id'];
                    $insertValues[] = "($subtopicId, $usersId, 0)"; // Assuming '1' is the value for 'checked'
                }

                // Perform bulk insert if there are values to insert
                if (!empty($insertValues)) {
                    $bulkInsertQuery = "INSERT INTO `courses_sub_topics_user` (courses_sub_topics_id, users_id, checked) VALUES " . implode(", ", $insertValues);
                    $sqlHelp = new SqlHelper();
                    $result = $sqlHelp->executeQuery($bulkInsertQuery);

                    if ($result[0] !== 200) {
                        return Response::json($result[0], $result[1]);
                    }
                }

                return Response::json(200, [
                    'status' => 'success',
                    'message' => "Order $orderId placed successfully!"
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

    function InsertSubTopicWithUser($subtopicsId, $usersId)
    {
        $sqlHelp = new SqlHelper();
        $orderId = $this->GetOrderId();
        $query = "INSERT INTO `courses_sub_topics_user`(courses_sub_topics_id, users_id, checked) VALUES (?, ?, 1)";
        $result = $sqlHelp->executeQuery($query, 'ii', array($subtopicsId, $usersId));

        if ($result[0] === 200) {
            return true;
        }
        return false;
    }

    function GetOrderId()
    {
        $query = "SELECT COUNT(*) + 1 AS orderId FROM orders";

        $stmt = $this->db->prepare($query);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $orderId = $result->fetch_all(MYSQLI_ASSOC);

            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $orderId = $result->fetch_assoc();
                    return $orderId['orderId'];
                }
            }
        } else {
            return 0;
        }
    }

    function GetPaymentDetails($courseId)
    {
        $query = "SELECT actual_price AS amount, discount_price AS discount FROM courses WHERE id = ?";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $courseId);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $course = $result->fetch_all(MYSQLI_ASSOC);

            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $course = $result->fetch_assoc();
                    return $course;
                }
            }
        } else {
            return 0;
        }
    }
}
