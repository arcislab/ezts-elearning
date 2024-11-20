<?php
require_once './api/helpers/MySqlCommands.php';
require_once './api/v1/quiz.php';
require_once './api/v1/user.php';
require_once './api/v1/aws-s3-manage.php';

$quizController = new Quiz();
$userController = new User();
$awsController = new AwsS3();

class Courses
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    function CoursesTypes($uuid)
    {
        if ($uuid === null) {
            $query = "SELECT `id` AS uuid, `type` FROM courses_types";
        } else {
            $query = "SELECT `id` AS uuid, `type` FROM courses_types WHERE id = ?";
        }

        $stmt = $this->db->prepare($query);

        if ($stmt === false) {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Query preparation failed: ' . $this->db->error
            ]);
        }

        if ($uuid !== null) {
            $stmt->bind_param('i', $uuid);
        }

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

    function GetCourses($count = null, $id = null, $uuid = null)
    {
        if ($count === null && $id === null) {
            $query = "SELECT id AS uuid, `name`, author, duration, actual_price, discount_price, expiry FROM courses LIMIT 20";
        } else if ($count !== null && $id === null) {
            $query = "SELECT id AS uuid, `name`, author, duration, actual_price, discount_price, expiry FROM courses LIMIT ?";
        } else if ($count === null && $id !== null) {
            $query = "SELECT ct.type AS 'Type', c.id AS uuid, c.name, c.author, c.duration, c.actual_price, c.discount_price, c.expiry FROM courses c INNER JOIN courses_types ct ON ct.id = c.course_type_id WHERE course_type_id = ?";
        } else if ($count !== null && $id !== null) {
            $query = "SELECT id AS uuid, `name`, author, duration, actual_price, discount_price, expiry FROM courses WHERE course_type_id = ? LIMIT ?";
        }

        if ($uuid !== null) {
            $query = "SELECT id AS uuid, `name`, author, duration, actual_price, discount_price, expiry FROM courses WHERE id = ?";
        }

        $stmt = $this->db->prepare($query);

        if ($stmt === false) {
            return Response::json(500, [
                'status' => 'error',
                // 'message' => 'Query preparation failed: ' . $this->db->error
                'message' => 'Server error'
            ]);
        }

        if ($count !== null && $id === null) {
            $stmt->bind_param('i', $count);
        } else if ($count === null && $id !== null) {
            $stmt->bind_param('i', $id);
        } else if ($count !== null && $id !== null) {
            $stmt->bind_param('ii', $count, $id);
        }

        if ($uuid !== null) {
            $stmt->bind_param('i', $uuid);
        }

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $coursesResult = $result->fetch_all(MYSQLI_ASSOC);
            // $data = array_column($courses, 'type');

            $data = [];
            foreach ($coursesResult as $course) {
                $data[] = [
                    "uuid" => $course['uuid'],
                    "name" => $course['name'],
                    "author" => $course['author'],
                    "duration" => $course['duration'],
                    "ap" => $course['actual_price'],
                    "dp" => $course['discount_price'],
                    "expiry" => $course['expiry']
                ];
            }

            return Response::json(200, [
                'status' => 'success',
                'type' => (isset($coursesResult[0]["Type"]) && count($coursesResult) > 0) ? $coursesResult[0]["Type"] : null,
                'data' => $data
            ]);
        } else {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Failed to retrieve courses.'
            ]);
        }
    }

    function Topics($courseId, $uuid, $userId, $returnArray = false)
    {
        global $quizController;
        if ($courseId !== null) {
            // $query = "SELECT id AS uuid, topic_name, content_url FROM courses_topics WHERE courses_id = ?";
            $query = "SELECT ct.id, 
                           ct.topic_name AS name, 
                           SUM(cst.duration) AS duration, 
                           (SELECT COUNT(*) 
                            FROM courses_sub_topics 
                            WHERE courses_topics_id = ct.id) AS total_sub_topics, 
                           (SELECT COUNT(*) 
                            FROM courses_sub_topics 
                            WHERE demo = 1 AND courses_topics_id = ct.id) AS demo_videos 
                    FROM courses_topics ct 
                    LEFT JOIN courses_sub_topics cst 
                           ON cst.courses_topics_id = ct.id 
                    WHERE ct.courses_id = ? 
                    GROUP BY ct.id, ct.topic_name;";
        }

        if ($uuid !== null) {
            // $query = "SELECT id AS uuid, topic_name, content_url FROM courses_topics WHERE id = ?";
            $query = "SELECT ct.id, 
                           ct.topic_name AS name, 
                           SUM(cst.duration) AS duration, 
                           (SELECT COUNT(*) 
                            FROM courses_sub_topics 
                            WHERE courses_topics_id = ct.id) AS total_sub_topics, 
                           (SELECT COUNT(*) 
                            FROM courses_sub_topics 
                            WHERE demo = 1 AND courses_topics_id = ct.id) AS demo_videos 
                    FROM courses_topics ct 
                    INNER JOIN courses_sub_topics cst 
                           ON cst.courses_topics_id = ct.id 
                    WHERE ct.id = ? 
                    GROUP BY ct.id, ct.topic_name;";
        }

        $stmt = $this->db->prepare($query);

        if ($stmt === false) {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Query preparation failed: ' . $this->db->error
            ]);
        }

        if ($courseId !== null) {
            $stmt->bind_param('i', $courseId);
        }

        if ($uuid !== null) {
            $stmt->bind_param('i', $uuid);
        }

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $topics = $result->fetch_all(MYSQLI_ASSOC);
            if ($returnArray) {
                $data = [];
                foreach ($topics as $topic) {
                    $data[] = [
                        "uuid" => $topic['id'],
                        "name" => $topic['name'],
                        "duration" => $topic['duration'],
                        "total_sub_topics" => $topic['total_sub_topics'],
                        "demo_videos" => $topic['demo_videos'],
                        "access" => $this->IsTopicLocked($topic['id'], $userId) == true ? false : true,
                        "provide_quiz" => $quizController->CheckIfQuizAllowed($topic['id'], $userId, false)
                    ];
                }

                // return Response::json(200, [
                //     'status' => 'success',
                //     'topics' => $data
                // ]);
                return $data;
            } else {
                return Response::json(200, [
                    'status' => 'success',
                    'topics' => $topics
                ]);
            }
        } else {
            if ($returnArray) {
                return null;
            } else {
                return Response::json(500, [
                    'status' => 'error',
                    'message' => 'Failed to retrieve courses.'
                ]);
            }
        }
    }

    function SubTopics($courseTopicId, $uuid, $userId)
    {
        global $userController;
        if ($this->IsTopicLocked($courseTopicId, $userId) && !$userController->IsUserAdmin($userId)) {
            return Response::json(403, [
                'status' => 'error',
                'message' => 'Topic is locked.'
            ]);
        } else {
            if ($courseTopicId !== null) {
                $query = "SELECT id AS uuid, topic_name, video_url, project_url, duration, demo FROM courses_sub_topics WHERE courses_topics_id = ?";
            }

            if ($uuid !== null) {
                $query = "SELECT id AS uuid, topic_name, video_url, project_url, duration, demo FROM courses_sub_topics WHERE id = ?";
            }

            $stmt = $this->db->prepare($query);

            if ($stmt === false) {
                return Response::json(500, [
                    'status' => 'error',
                    'message' => 'Query preparation failed: ' . $this->db->error
                ]);
            }

            if ($courseTopicId !== null) {
                $stmt->bind_param('i', $courseTopicId);
            }

            if ($uuid !== null) {
                $stmt->bind_param('i', $uuid);
            }

            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $topics = $result->fetch_all(MYSQLI_ASSOC);

                return Response::json(200, [
                    'status' => 'success',
                    'sub_topics' => $topics
                ]);
            } else {
                return Response::json(500, [
                    'status' => 'error',
                    'message' => 'Failed to retrieve courses.'
                ]);
            }
        }
    }

    function EnrolledCourses($usersId)
    {
        $query = "SELECT c.id AS uuid, c.name AS name, DATE_FORMAT(o.date, '%d-%m-%Y') AS start_date, DATE_FORMAT(DATE_ADD(o.date, INTERVAL c.expiry DAY), '%d-%m-%Y') AS end_date FROM courses c INNER JOIN orders o ON o.courses_id = c.id WHERE o.users_id = ?";
        $stmt = $this->db->prepare($query);

        if ($stmt === false) {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Query preparation failed: ' . $this->db->error
            ]);
        }

        $stmt->bind_param('i', $usersId);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $totalEnrolledCourses = count($data);
            $completedCourses = $this->EnrolledCoursesAnalytics($usersId, $totalEnrolledCourses);

            return Response::json(200, [
                'status' => 'success',
                'total' => $totalEnrolledCourses,
                'completed' => $completedCourses,
                'active' => $totalEnrolledCourses - $completedCourses,
                'data' => $data
            ]);
        } else {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Failed to retrieve courses.'
            ]);
        }
    }

    function EnrolledCoursesAnalytics($usersId, $totalEnrolledCourses)
    {
        $query = "SELECT COUNT(*) AS completed
                    FROM (
                        SELECT 
                            (SELECT COUNT(*) 
                             FROM courses_sub_topics_user cstu 
                             INNER JOIN courses_sub_topics cst 
                             ON cst.id = cstu.courses_sub_topics_id 
                             WHERE cst.courses_topics_id = c.id 
                             AND cstu.checked = 1) AS checked,
                            (SELECT COUNT(*) 
                             FROM courses_sub_topics 
                             WHERE courses_topics_id = c.id) AS total_subtopics
                        FROM courses c
                        INNER JOIN orders o 
                        ON o.courses_id = c.id
                        WHERE o.users_id = ?
                    ) AS subquery
                    WHERE checked = total_subtopics;";

        $stmt = $this->db->prepare($query);

        if ($stmt === false) {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Query preparation failed: ' . $this->db->error
            ]);
        }

        $stmt->bind_param('i', $usersId);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $course = $result->fetch_assoc();
                return $course['completed'];
            }
        }
        return 0;
    }

    function EnrolledCourseInfo($usersId, $courseId)
    {
        $query = "SELECT topic_name FROM courses_topics WHERE courses_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('s', $usersId);

        if ($stmt === false) {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Query preparation failed: ' . $this->db->error
            ]);
        }

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $topics = $result->fetch_all(MYSQLI_ASSOC);

            return Response::json(200, [
                'status' => 'success',
                'data' => $topics
            ]);
        } else {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Failed to retrieve courses.'
            ]);
        }
    }

    function GetCourseInfo($userId, $courseId)
    {
        if ($this->CheckIfPurchased($userId, $courseId)) {
            $query = "SELECT c.id As id, c.name AS name, c.duration AS duration, 
            (SELECT COUNT(*) FROM courses_sub_topics cst INNER JOIN courses_topics ct ON ct.id = cst.courses_topics_id WHERE ct.courses_id = c.id AND cst.video_url IS NOT NULL AND cst.video_url <> '') AS total_videos,
            (SELECT COUNT(*) FROM courses_sub_topics cst INNER JOIN courses_topics ct ON ct.courses_id = c.id WHERE cst.courses_topics_id = ct.id AND cst.project_url IS NOT NULL AND cst.project_url <> '') AS total_projects,
            (SELECT COUNT(*) FROM courses_topics WHERE courses_id = c.id AND content_url IS NOT NULL) AS downloadable_content
            FROM courses c
            WHERE c.id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $courseId);

            if ($stmt === false) {
                return Response::json(500, [
                    'status' => 'error',
                    'message' => 'Query preparation failed: ' . $this->db->error
                ]);
            }

            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $info = $result->fetch_assoc();
                    return Response::json(200, [
                        'status' => 'success',
                        'data' => [
                            'course_name' => $info["name"],
                            'total_videos' => $info["total_videos"],
                            'total_projects' => $info["total_projects"],
                            'duration' => $info["duration"],
                            'downloadable_content' => $info["downloadable_content"],
                            'expiry' => $this->GetExpiry($userId),
                            'topics' => $this->Topics($courseId, null, $userId, true)

                        ]
                    ]);
                }
            } else {
                return Response::json(500, [
                    'status' => 'error',
                    'message' => 'Failed to retrieve courses.'
                ]);
            }
        } else {
            return Response::json(200, [
                'status' => 'success',
                'message' => 'Course not purchased'
            ]);
        }
    }

    function IsTopicLocked($topicId, $userId)
    {
        global $quizController;
        if ($this->IsTopicFirst($topicId)) {
            return false;
        } else {
            //TODO: Remove the below logic and add logic to check if user has passed a quiz or not in this topic
            if ($quizController->CheckIfQuizAllowed($topicId, $userId, false)) {
                return true;
            } else {
                return false;
            }
        }
    }

    //Check if topic is first in the list of topics so that we can prevent it from being locked as the first topic should be accessible to user
    function IsTopicFirst($topicId)
    {
        $query = "SELECT MIN(ct.id) AS `LowestTopicId`
          FROM courses_topics ct
          INNER JOIN courses c ON c.id = ct.courses_id
          WHERE ct.courses_id = (
              SELECT courses_id FROM courses_topics WHERE id = ?
          )";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $topicId);

        if ($stmt === false) {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Query preparation failed: ' . $this->db->error
            ]);
        }

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $data = $result->fetch_assoc();
                $lowestTopicId = $data['LowestTopicId'];
                return $topicId == $lowestTopicId ? true : false;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function GetExpiry($usersId)
    {
        $query = "SELECT DATE_FORMAT(DATE_ADD(o.date, INTERVAL c.expiry DAY), '%d-%m-%Y') AS expiry FROM courses c INNER JOIN orders o ON o.courses_id = c.id WHERE o.users_id = ?";
        $stmt = $this->db->prepare($query);

        if ($stmt === false) {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Query preparation failed: ' . $this->db->error
            ]);
        }

        $stmt->bind_param('i', $usersId);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $course = $result->fetch_assoc();
                return $course['expiry'];
            }
        }
        return 0;
    }

    function CheckIfPurchased($userId, $courseId)
    {
        $query = "SELECT id FROM orders WHERE courses_id = ? AND users_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ii', $courseId, $userId);

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
            return false;
        } else {
            return false;
        }
    }

    // Admin section
    // CRUD Courses Type
    function AddCourseType($courseType)
    {
        $sqlHelp = new SqlHelper();
        $query = "INSERT INTO courses_types (`type`) VALUES (?)";
        $result = $sqlHelp->executeQuery($query, 's', array($courseType));
        return Response::json($result[0], $result[1]);
    }
    function UpdateCourseType($id, $type, $adminId = null)
    {
        $sqlHelp = new SqlHelper();
        $query = "UPDATE courses_types SET `type` = ? WHERE id = ?";
        $result = $sqlHelp->executeQuery($query, 'si', array($type, $id));
        return Response::json($result[0], $result[1]);
    }
    function DeleteCourseType($id, $adminId = null)
    {
        $sqlHelp = new SqlHelper();
        $query = "DELETE FROM courses_types WHERE id = ?";
        $result = $sqlHelp->executeQuery($query, 's', array($id));
        return Response::json($result[0], $result[1]);
    }

    //CRUD Courses
    function AddCourses($type, $course, $author, $duration, $actualPrice, $discountPrice, $expiry)
    {
        $sqlHelp = new SqlHelper();
        $query = "INSERT INTO `courses`(`course_type_id`, `name`, `author`, `duration`, `actual_price`, `discount_price`, `expiry`) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $result = $sqlHelp->executeQuery($query, 'sssssss', array($type, $course, $author, $duration, $actualPrice, $discountPrice, $expiry));
        return Response::json($result[0], $result[1]);
    }

    function UpdateCourse($id, $course, $author, $duration, $actualPrice, $discountPrice, $expiry)
    {
        $sqlHelp = new SqlHelper();
        $query = "UPDATE `courses` SET `name` = ?, author = ?, duration = ?, actual_price = ?, discount_price = ?, expiry = ? WHERE id = ?";
        $result = $sqlHelp->executeQuery($query, 'ssssssi', array($course, $author, $duration, $actualPrice, $discountPrice, $expiry, $id));
        return Response::json($result[0], $result[1]);
    }

    function DeleteCourse($id, $adminId = null)
    {
        $sqlHelp = new SqlHelper();
        $query = "DELETE FROM courses WHERE id = ?";
        $result = $sqlHelp->executeQuery($query, 's', array($id));
        return Response::json($result[0], $result[1]);
    }

    // CRUD Topics
    function AddTopic($topicName, $course, $content)
    {
        $sqlHelp = new SqlHelper();
        $query = "INSERT INTO `courses_topics`(`topic_name`, `courses_id`, `content_url`) VALUES (?, ?, ?)";
        $result = $sqlHelp->executeQuery($query, 'sss', array($topicName, $course, $content));
        return Response::json($result[0], $result[1]);
    }

    function UpdateTopic($id, $topicName, $content)
    {
        $sqlHelp = new SqlHelper();
        $query = "UPDATE `courses_topics` SET `topic_name` = ?, content_url = ? WHERE id = ?";
        $result = $sqlHelp->executeQuery($query, 'ssi', array($topicName, $content, $id));
        return Response::json($result[0], $result[1]);
    }

    function DeleteTopic($id, $adminId = null)
    {
        $sqlHelp = new SqlHelper();
        $query = "DELETE FROM courses_topics WHERE id = ?";
        $result = $sqlHelp->executeQuery($query, 'i', array($id));
        return Response::json($result[0], $result[1]);
    }

    //CRUD Sub Topics
    function AddSubTopic($topic, $name, $video, $project, $duration, $demo)
    {
        $sqlHelp = new SqlHelper();
        $query = "INSERT INTO `courses_sub_topics`(`courses_topics_id`, `topic_name`, `project_url`, `duration`, `demo`) VALUES (?, ?, ?, ?, ?)";
        $result = $sqlHelp->executeQuery($query, 'sssss', array($topic, $name, $project, $duration, $demo));
        $this->UploadCourseVideo($video);
        return Response::json($result[0], $result[1]);
    }

    function UploadCourseVideo($video) {
        global $awsController;
        $awsController->Upload($video);
    }

    function UpdateSubTopic($id, $name, $video, $project, $duration, $demo)
    {
        $sqlHelp = new SqlHelper();
        $query = "UPDATE `courses_sub_topics` SET `topic_name` = ?, video_url = ?, project_url = ?, duration = ?, demo = ? WHERE id = ?";
        $result = $sqlHelp->executeQuery($query, 'sssssi', array($name, $video, $project, $duration, $demo, $id));
        return Response::json($result[0], $result[1]);
    }

    function DeleteSubTopic($id, $adminId = null)
    {
        $sqlHelp = new SqlHelper();
        $query = "DELETE FROM courses_sub_topics WHERE id = ?";
        $result = $sqlHelp->executeQuery($query, 's', array($id));
        return Response::json($result[0], $result[1]);
    }
}
