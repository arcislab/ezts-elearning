<?php
require_once './api/helpers/MySqlCommands.php';

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

    function AddOrder($usersId, $coursesId, $paymentId)
    {
        $sqlHelp = new SqlHelper();
        $orderId = $this->GetOrderId();
        $query = "INSERT INTO `orders`(`date`, payment_id, `order_id`, `courses_id`, `users_id`, `total_amount`, `discount`) VALUES (CURDATE(), ?, ?, ?, ?, ?, ?)";
        $result = $sqlHelp->executeQuery($query, 'ssiiss', array($orderId, $paymentId, $coursesId, $usersId, $this->GetPaymentDetails($coursesId)["amount"], $this->GetPaymentDetails($coursesId)["discount"]));
        
        if($result[0] === 200){
             return Response::json(200, [
                'status' => 'success',
                'message' => "Order $orderId placed successfully!"
            ]);
        }else{
            return Response::json($result[0], $result[1]);
        }
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

    function GetPaymentDetails($courseId){
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
