<?php
require_once './api/config/database.php';
require_once './api/helpers/Response.php';
require_once './api/helpers/GenerateUUID.php';

class Bookmarks
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    function GetBookmarks($userid)
    {
        $query = "SELECT c.id AS uuid, c.name, c.author, c.duration, c.actual_price, c.discount_price, (SELECT AVG(r.rating) FROM ratings r WHERE r.courses_id = c.id) AS rating FROM bookmarks b INNER JOIN courses c ON c.id = b.courses_id WHERE users_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('s', $userid);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $bookmarks = $result->fetch_all(MYSQLI_ASSOC);

            $data = [];
            foreach ($bookmarks as $course) {
                $data[] = [
                    "uuid" => $course['uuid'],
                    "name" => $course['name'],
                    "author" => $course['author'],
                    "duration" => $course['duration'],
                    "ap" => $course['actual_price'],
                    "dp" => $course['discount_price']
                ];
            }

            return Response::json(200, [
                'status' => 'success',
                'data' => $data
            ]);
        } else {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Failed to retrieve bookmarks.'
            ]);
        }
    }

    function AddBookmark($userid, $coursesid)
    {
        $retry = true;
        while ($retry) {
            try {
                $uuid = generateUUID();
                $query = "INSERT INTO bookmarks (users_id, courses_id, uuid) VALUES (?, ?, ?)";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param('sss', $userid, $coursesid, $uuid);
                if ($stmt->execute()) {
                    $retry = false;
                    return Response::json(201, [
                        'status' => 'success',
                        'message' => 'Bookmark added successfully.'
                    ]);
                } else {
                    return Response::json(500, [
                        'status' => 'error',
                        'message' => 'Failed to add bookmark.'
                    ]);
                }
            } catch (PDOException $e) {
                // If error code indicates a duplicate key violation, try again
                if ($e->getCode() == 23000) {  // MySQL error code for duplicate key
                    $retry = true;  // Retry generating a new UUID
                } else {
                    throw $e;
                }
            }
        }
    }

    function DeleteBookmark($bookmarksId)
    {
        $query = "DELETE FROM bookmarks WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('s', $bookmarksId);
        if ($stmt->execute()) {
            return Response::json(200, [
                'status' => 'success',
                'message' => 'Bookmark deleted successfully.'
            ]);
        } else {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Failed to delete bookmark.'
            ]);
        }
    }
}
