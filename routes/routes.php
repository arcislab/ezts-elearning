<?php
require_once './api/config/env.php';
require_once './api/v1/autofill.php';
require_once './api/v1/bookmarks.php';
require_once './api/v1/courses.php';
require_once './api/v1/notifications.php';
require_once './api/v1/orders.php';
require_once './api/v1/quiz.php';
require_once './api/v1/user.php';
require_once './api/v1/leader.php';
require_once './api/v1/authen.php';

//headers
header("Access-Control-Allow-Origin: http://ezts.local");
header("Access-Control-Allow-Credentials: true");
// header("Access-Control-Allow-Origin: *"); // Allow all origins, or specify your front-end origin
header("Access-Control-Allow-Methods: POST, PUT, GET, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type"); // Allow specific headers

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
    // header("Access-Control-Allow-Headers: Content-Type"); //Already declared above
    exit(0); // End the response for the preflight request
}

$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$data = null;
if ($method === 'POST' || $method === 'PUT' || $method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
}

$autofillController = new Autofill();
$bookmarksController = new Bookmarks();
$coursesController = new Courses();
$notificationsController = new Notifications();
$ordersController = new Orders();
$quizController = new Quiz();
$authController = new Authentication();
$userController = new User();
$leaderController = new Leadership();

switch ($request) {
        //Bookmark routes
    case '/':
        if ($method == 'GET') {
            call_user_func('CallHome');
        }
        break;
    case '/api/v1/bookmarks':
        if ($method == 'POST') {
            $userId = Authenticate();
            if (is_numeric($userId)) {
                echo $bookmarksController->GetBookmarks($userId);
            }
        }
        break;
    case '/api/v1/bookmarks/add':
        if ($method == 'POST') {
            if (!isset($data["course"])) {
                return Response::json(404, [
                    'status' => 'error',
                    'message' => 'Invalid request.'
                ]);
            } else {
                $course = $data["course"];
                $userId = Authenticate();
                if (is_numeric($userId)) {
                    echo $bookmarksController->AddBookmark($userId, $course);
                }
            }
        }
        break;
    case '/api/v1/bookmarks/remove':
        if ($method == 'POST') {
            if (!isset($data["bookmark"])) {
                return Response::json(404, [
                    'status' => 'error',
                    'message' => 'Invalid request.'
                ]);
            } else {
                $bookmark = $data["bookmark"];
                $userId = Authenticate();
                if (is_numeric($userId)) {
                    echo $bookmarksController->DeleteBookmark($userId, $bookmark);
                }
            }
        }
        break;
        //Blogs
    case '/api/v1/blogs':
        if ($method == 'GET') {
            $userId = Authenticate(true);
            if (is_numeric($userId)) {
                echo $bookmarksController->GetBookmarks($userId);
            }
        }
        break;
        // Courses routes
    case '/api/v1/courses-types': //Courses types
        if ($method == 'POST') {
            $uuid = isset($data["uuid"]) ? $data["uuid"] : null;
            echo $coursesController->CoursesTypes($uuid);
        }
        break;
    case '/api/v1/courses-types/add': //Courses types add
        if ($method == 'POST') {
            $type = isset($data["type"]) ? $data["type"] : null;
            $userId = Authenticate(true);
            if (is_numeric($userId)) {
                echo $coursesController->AddCourseType($type);
            }
        }
        break;
    case '/api/v1/courses-types/update': //Courses types update
        if ($method == 'POST') {
            $id = isset($data["uuid"]) ? $data["uuid"] : null;
            $type = isset($data["type"]) ? $data["type"] : null;
            $userId = Authenticate(true);
            if (is_numeric($userId)) {
                echo $coursesController->UpdateCourseType($id, $type);
            }
        }
        break;
    case '/api/v1/courses-types/delete': //Courses types update
        if ($method == 'POST') {
            $id = isset($data["uuid"]) ? $data["uuid"] : null;
            $userId = Authenticate(true);
            if (is_numeric($userId)) {
                echo $coursesController->DeleteCourseType($id);
            }
        }
        break;
    case '/api/v1/courses': //Courses
        if ($method == 'POST') {
            $count = isset($data["count"]) ? $data["count"] : null;
            $course_type_id = isset($data["type"]) ? $data["type"] : null;
            $course_id = isset($data["uuid"]) ? $data["uuid"] : null;
            echo $coursesController->GetCourses($count, $course_type_id, $course_id);
        }
        break;
    case '/api/v1/courses/info': //Courses Information
        if ($method == 'POST') {
            if (!isset($data["course"])) {
                return Response::json(501, [
                    'status' => 'error',
                    'message' => 'Please select course.'
                ]);
            } else {
                $userId = Authenticate();
                if (is_numeric($userId)) {
                    echo $coursesController->GetCourseInfo($userId, $data["course"]);
                }
            }
        }
        break;
    case '/api/v1/courses/add': //Courses add
        if ($method == 'POST') {
            $type = isset($data["type"]) ? $data["type"] : null;
            $name = isset($data["name"]) ? $data["name"] : null;
            $author = isset($data["author"]) ? $data["author"] : null;
            $duration = isset($data["duration"]) ? $data["duration"] : null;
            $actual_price = isset($data["actual_price"]) ? $data["actual_price"] : null;
            $discount_price = isset($data["discount_price"]) ? $data["discount_price"] : null;
            $expiry = isset($data["expiry"]) ? $data["expiry"] : null;

            $userId = Authenticate(true);
            if (is_numeric($userId)) {
                echo $coursesController->AddCourses($type, $name, $author, $duration, $actual_price, $discount_price, $expiry);
            }
        }
        break;
    case '/api/v1/courses/update': //Courses add update
        if ($method == 'POST') {
            $id = isset($data["uuid"]) ? $data["uuid"] : null;
            $name = isset($data["name"]) ? $data["name"] : null;
            $author = isset($data["author"]) ? $data["author"] : null;
            $duration = isset($data["duration"]) ? $data["duration"] : null;
            $actual_price = isset($data["actual_price"]) ? $data["actual_price"] : null;
            $discount_price = isset($data["discount_price"]) ? $data["discount_price"] : null;
            $expiry = isset($data["expiry"]) ? $data["expiry"] : null;

            $userId = Authenticate(true);
            if (is_numeric($userId)) {
                echo $coursesController->UpdateCourse($id, $name, $author, $duration, $actual_price, $discount_price, $expiry);
            }
        }
        break;
    case '/api/v1/courses/delete': //Courses add delete
        if ($method == 'POST') {
            $id = isset($data["uuid"]) ? $data["uuid"] : null;
            $userId = Authenticate(true);
            if (is_numeric($userId)) {
                echo $coursesController->DeleteCourse($id);
            }
        }
        break;
    case '/api/v1/courses/topics': //Topics
        if ($method == 'POST') {
            $uuid = isset($data["uuid"]) ? $data["uuid"] : null;
            $course = isset($data["course"]) ? $data["course"] : null;
            $userId = Authenticate();
            if (is_numeric($userId)) {
                echo $coursesController->Topics($course, $uuid, $userId);
            }
        }
        break;
    case '/api/v1/courses/topics/add': //Topics add
        if ($method == 'POST') {
            $topic = isset($data["topic"]) ? $data["topic"] : null;
            $course = isset($data["course"]) ? $data["course"] : null;
            $content = isset($data["content"]) ? $data["content"] : null;
            $userId = Authenticate(true);
            if (is_numeric($userId)) {
                echo $coursesController->AddTopic($topic, $course, $content);
            }
        }
        break;
    case '/api/v1/courses/topics/update': //Topics update
        if ($method == 'POST') {
            $id = isset($data["uuid"]) ? $data["uuid"] : null;
            $topic = isset($data["topic"]) ? $data["topic"] : null;
            $content = isset($data["content"]) ? $data["content"] : null;
            $userId = Authenticate(true);
            if (is_numeric($userId)) {
                echo $coursesController->UpdateTopic($id, $topic, $content);
            }
        }
        break;
    case '/api/v1/courses/topics/delete': //Topics delete
        if ($method == 'POST') {
            $id = isset($data["uuid"]) ? $data["uuid"] : null;
            $userId = Authenticate(true);
            if (is_numeric($userId)) {
                echo $coursesController->DeleteTopic($id);
            }
        }
        break;
    case '/api/v1/courses/video': //Test endpoint for aws video upload
        if ($method == 'POST') {
            if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
                $awsController = new AwsS3();
                $awsController->Upload($_FILES['video']);
            }
        }
        break;
    case '/api/v1/get-sign': //Get aws s3 signed url
        if ($method == 'GET') {
            if (!isset($_GET['filekey'])) {
                return Response::json(404, [
                    'status' => 'error',
                    'message' => 'File key is missing!'
                ]);
                exit;
            }
            $signedUrl = $awsController->GetSignedUrl($_GET['filekey']);
            if ($signedUrl) {
                return Response::json(200, [
                    'status' => 'success',
                    'sign' => $signedUrl
                ]);
            } else {
                return Response::json(406, [
                    'status' => 'error',
                    'message' => 'Error while getting key'
                ]);
            }
        }
        break;
    case '/api/v1/courses/sub-topics': //Sub Topics
        if ($method == 'POST') {
            $course_topic = isset($data["course_topic"]) ? $data["course_topic"] : null;
            $uuid = isset($data["uuid"]) ? $data["uuid"] : null;
            $userId = Authenticate();
            if (is_numeric($userId)) {
                echo $coursesController->SubTopics($course_topic, $uuid, $userId);
            }
        }
        break;
    case '/api/v1/courses/sub-topics/add': //Sub Topics add
        if ($method == 'POST') {
            $topic = isset($_POST["topic"]) ? $_POST["topic"] : null;
            $name = isset($_POST["name"]) ? $_POST["name"] : null;
            $video = isset($_POST["video"]) ? $_POST["video"] : null;
            $project = isset($_FILES["project"]) ? $_FILES["project"] : null;
            $duration = isset($_POST["duration"]) ? $_POST["duration"] : null;
            $demo = isset($_POST["demo"]) ? $_POST["demo"] : null;
            $userId = Authenticate(true);
            if (is_numeric($userId)) {
                if (!$topic || !$name || !$video) {
                    return Response::json(404, [
                        'status' => 'error',
                        'message' => 'Invalid request.',
                        'topic' => $topic
                    ]);
                    exit;
                }
                echo $coursesController->AddSubTopic($topic, $name, $video, $project, $duration, $demo);
            }
        }
        break;
    case '/api/v1/courses/sub-topics/update': //Sub Topics update
        if ($method == 'POST') {
            $id = isset($data["uuid"]) ? $data["uuid"] : null;
            $name = isset($data["name"]) ? $data["name"] : null;
            $video = isset($data["video"]) ? $data["video"] : null;
            $project = isset($data["project"]) ? $data["project"] : null;
            $duration = isset($data["duration"]) ? $data["duration"] : null;
            $demo = isset($data["demo"]) ? $data["demo"] : null;
            $userId = Authenticate(true);
            if (is_numeric($userId)) {
                echo $coursesController->UpdateSubTopic($id, $topic, $name, $video, $project, $duration, $demo);
            }
        }
        break;
    case '/api/v1/courses/sub-topics/delete': //Sub Topics delete
        if ($method == 'POST') {
            $id = isset($data["uuid"]) ? $data["uuid"] : null;
            $userId = Authenticate(true);
            if (is_numeric($userId)) {
                echo $coursesController->DeleteSubTopic($id);
            }
        }
        break;
    case '/api/v1/courses/enrolled': //Enrolled courses list
        if ($method == 'POST') {
            $userId = Authenticate();
            if (is_numeric($userId)) {
                echo $coursesController->EnrolledCourses($userId);
            }
        }
        break;
    case '/api/v1/courses/enrolled/course': //Enrolled course information
        if ($method == 'POST') {
            $userId = Authenticate();
            if (is_numeric($userId)) {
                echo $coursesController->EnrolledCourseInfo($userId, $data["courseInfo"]);
            }
        }
        break;
        //Notifications routes
    case '/api/v1/notifications':
        if ($method == 'POST') {
            $userId = Authenticate();
            if (is_numeric($userId)) {
                echo $notificationsController->GetNotifications($userId);
            }
        }
        break;
        //Orders routes
    case '/api/v1/orders':
        if ($method == 'POST') {
            $userId = Authenticate();
            if (is_numeric($userId)) {
                echo $ordersController->GetOrders($userId);
            }
        }
        break;
        //Orders routes
    case '/api/v1/orders/add':
        if ($method == 'POST') {
            if (!isset($data["course"])) {
                return Response::json(501, [
                    'status' => 'error',
                    'message' => 'Course not selected.'
                ]);
            } else if (!isset($data["payment"])) {
                return Response::json(501, [
                    'status' => 'error',
                    'message' => 'Payment not confirmed.'
                ]);
            } else {
                $userId = Authenticate();
                if (is_numeric($userId)) {
                    echo $ordersController->AddOrder($userId, $data["course"], $data["payment"]);
                }
            }
        }
        break;
        //Quiz routes
    case '/api/v1/quiz':
        //Get quiz questions 
        if ($method == 'POST') {
            if (!isset($data["course_topic"])) {
                return Response::json(501, [
                    'status' => 'error',
                    'message' => 'Topic not selected.'
                ]);
            } else {
                $userId = Authenticate();
                if (is_numeric($userId)) {
                    echo $quizController->SendAssignedQuestions($data["course_topic"], $userId);
                }
            }
        }
        break;
    case '/api/v1/quiz/info':
        //Get quiz questions 
        if ($method == 'POST') {
            if (!isset($data["course_topic"])) {
                return Response::json(501, [
                    'status' => 'error',
                    'message' => 'Topic not selected.'
                ]);
            } else {
                $userId = Authenticate();
                if (is_numeric($userId)) {
                    echo $quizController->GetQuizInfo($data["course_topic"]);
                }
            }
        }
        break;
    case '/api/v1/quiz/request':
        //Create a quiz of x questions for user
        if ($method == 'POST') {
            if (!isset($data["course_topic"])) {
                return Response::json(501, [
                    'status' => 'error',
                    'message' => 'Topic not selected.'
                ]);
            } else {
                $userId = Authenticate();
                if (is_numeric($userId)) {
                    if ($userController->IsUserAdmin($userId)) {
                        echo $quizController->GetQuizDetails($data["course_topic"]);
                    } else {
                        echo $quizController->CreateQuiz($data["course_topic"], $userId);
                    }
                }
            }
        }
        break;
    case '/api/v1/quiz/manage':
        if ($method == 'POST') {
            if (!isset($data["topic"])) {
                return Response::json(501, [
                    'status' => 'error',
                    'message' => 'Topic is empty.'
                ]);
            } else if (!isset($data["gt"])) {
                return Response::json(501, [
                    'status' => 'error',
                    'message' => 'Grand test is null.'
                ]);
            } else if (!isset($data["time"])) {
                return Response::json(501, [
                    'status' => 'error',
                    'message' => 'Time is empty.'
                ]);
            } else {
                $userId = Authenticate(true);
                if (is_numeric($userId)) {
                    echo $quizController->AddQuiz($data["topic"], $data["gt"], $data["time"]);
                }
            }
        } else if ($method == "PUT") {
            if (!isset($data["uuid"])) {
                return Response::json(501, [
                    'status' => 'error',
                    'message' => 'Quiz is not selected.'
                ]);
            } else if (!isset($data["gt"])) {
                return Response::json(501, [
                    'status' => 'error',
                    'message' => 'Grand test is null.'
                ]);
            } else if (!isset($data["time"])) {
                return Response::json(501, [
                    'status' => 'error',
                    'message' => 'Time is empty.'
                ]);
            } else {
                $userId = Authenticate(true);
                if (is_numeric($userId)) {
                    echo $quizController->UpdateQuiz($data["uuid"], $data["gt"], $data["time"]);
                }
            }
        } else if ($method == "DELETE") {
            if (!isset($data["uuid"])) {
                return Response::json(501, [
                    'status' => 'error',
                    'message' => 'Quiz is not selected.'
                ]);
            } else {
                $userId = Authenticate(true);
                if (is_numeric($userId)) {
                    echo $quizController->DeleteQuiz($data["uuid"]);
                }
            }
        }
        break;
    case '/api/v1/quiz/start':
        if ($method == 'POST') {
            $userId = Authenticate();
            if (is_numeric($userId)) {
                echo $quizController->StartQuiz($data["quiz"], $userId);
            }
        }
        break;
    case '/api/v1/quiz/complete':
        if ($method == 'POST') {
            $userId = Authenticate();
            if (is_numeric($userId)) {
                echo $quizController->CompleteQuiz($data["quiz"], $userId);
            }
        }
        break;
    case '/api/v1/quiz/exit':
        if ($method == 'POST') {
            $userId = Authenticate();
            if (!isset($data["quiz"])) {
                return Response::json(404, [
                    'status' => 'error',
                    'message' => 'Quiz is not selected.'
                ]);
            } else {
                $userId = Authenticate();
                if (is_numeric($userId)) {
                    echo $quizController->ExitQuiz($data["quiz"], $userId);
                }
            }
        }
        break;
    case '/api/v1/quiz/question':
        if ($method == 'POST') {
            if (!isset($data["question"])) {
                return Response::json(404, [
                    'status' => 'error',
                    'message' => 'Please select question.'
                ]);
            } else {
                $userId = Authenticate();
                if (is_numeric($userId)) {
                    echo $quizController->GetQuestion($data["question"]);
                }
            }
        }
        break;
    case '/api/v1/quiz/questions':
        if ($method == 'POST') {
            if (!isset($data["quiz"])) {
                return Response::json(404, [
                    'status' => 'error',
                    'message' => 'Please select quiz.'
                ]);
            } else {
                $userId = Authenticate();
                if (is_numeric($userId)) {
                    echo $quizController->GetQuestions($data["quiz"]);
                }
            }
        }
        break;
    case '/api/v1/quiz/questions/manage':
        if ($method == 'POST') {
            if (!isset($data["quiz"])) {
                return Response::json(404, [
                    'status' => 'error',
                    'message' => 'Please select quiz.'
                ]);
            } else if (!isset($data["question"])) {
                return Response::json(404, [
                    'status' => 'error',
                    'message' => 'Please enter question.'
                ]);
            } else {
                $userId = Authenticate(true);
                if (is_numeric($userId)) {
                    echo $quizController->AddQuestion($data["quiz"], $data["question"]);
                }
            }
        } else if ($method == 'PUT') {
            if (!isset($data["uuid"])) {
                return Response::json(404, [
                    'status' => 'error',
                    'message' => 'Please select question.'
                ]);
            } else if (!isset($data["question"])) {
                return Response::json(404, [
                    'status' => 'error',
                    'message' => 'Please enter question.'
                ]);
            } else {
                $userId = Authenticate(true);
                if (is_numeric($userId)) {
                    echo $quizController->UpdateQuestion($data["uuid"], $data["question"]);
                }
            }
        } else if ($method == 'DELETE') {
            if (!isset($data["uuid"])) {
                return Response::json(404, [
                    'status' => 'error',
                    'message' => 'Please select question.'
                ]);
            } else {
                $userId = Authenticate(true);
                if (is_numeric($userId)) {
                    echo $quizController->DeleteQuestion($data["uuid"]);
                }
            }
        }
        break;
    case '/api/v1/quiz/answer':
        if ($method == 'POST') {
            if (!isset($data["uuid"])) {
                return Response::json(404, [
                    'status' => 'error',
                    'message' => 'Please select answer.'
                ]);
            } else {
                $userId = Authenticate();
                if (is_numeric($userId)) {
                    echo $quizController->GetAnswer($data["uuid"]);
                }
            }
        }
        break;
    case '/api/v1/quiz/answers':
        if ($method == 'POST') {
            if (!isset($data["question"])) {
                return Response::json(404, [
                    'status' => 'error',
                    'message' => 'Please select question.'
                ]);
            } else {
                $userId = Authenticate();
                if (is_numeric($userId)) {
                    if ($userController->IsUserAdmin($userId)) {
                        echo $quizController->GetAnswersInfo($data["question"]);
                    } else {
                        echo $quizController->GetAnswers($data["question"]);
                    }
                }
            }
        }
        break;
    case '/api/v1/quiz/answers/manage':
        if ($method == 'POST') {
            if (!isset($data["question"])) {
                return Response::json(404, [
                    'status' => 'error',
                    'message' => 'Please select question.'
                ]);
            } else if (!isset($data["answer"])) {
                return Response::json(404, [
                    'status' => 'error',
                    'message' => 'Answer is empty.'
                ]);
            } else if (!isset($data["sub_topic"])) {
                return Response::json(404, [
                    'status' => 'error',
                    'message' => 'Please select sub-topic.'
                ]);
            } else {
                $userId = Authenticate(true);
                if (is_numeric($userId)) {
                    $correctAnswer = isset($data["correct"]) ? $data["correct"] : false;
                    $explaination = isset($data["explaination"]) ? $data["explaination"] : null;
                    echo $quizController->AddAnswer($data["question"], $data["answer"], $correctAnswer, $explaination, $data["sub_topic"]);
                }
            }
        } else if ($method == 'PUT') {
            if (!isset($data["uuid"])) {
                return Response::json(404, [
                    'status' => 'error',
                    'message' => 'Please select answer.'
                ]);
            } else if (!isset($data["answer"])) {
                return Response::json(404, [
                    'status' => 'error',
                    'message' => 'Answer is empty.'
                ]);
            } else {
                $userId = Authenticate(true);
                if (is_numeric($userId)) {
                    $correctAnswer = isset($data["correct"]) ? $data["correct"] : false;
                    $explaination = isset($data["explaination"]) ? $data["explaination"] : null;
                    echo $quizController->UpdateAnswer($data["uuid"], $data["answer"], $correctAnswer, $explaination);
                }
            }
        } else if ($method == 'DELETE') {
            if (!isset($data["uuid"])) {
                return Response::json(404, [
                    'status' => 'error',
                    'message' => 'Please select answer.'
                ]);
            } else {
                $userId = Authenticate(true);
                if (is_numeric($userId)) {
                    echo $quizController->DeleteAnswer($data["uuid"]);
                }
            }
        }
        break;
    case '/api/v1/quiz/send':
        if ($method == 'POST') {
            $userId = Authenticate();
            if (is_numeric($userId)) {
                if (!isset($data["data"])) {
                    return Response::json(404, [
                        'status' => 'error',
                        'message' => 'Quiz is empty.'
                    ]);
                } else {
                    $userId = Authenticate();
                    if (is_numeric($userId)) {
                        // $data = json_decode($body, true);

                        if (!isset($data["data"]) || !is_array($data["data"])) {
                            return Response::json(404, [
                                'status' => 'error',
                                'message' => 'Quiz is empty.'
                            ]);
                        } else {
                            echo $quizController->CheckResult($data["data"], $userId);
                        }
                    }
                }
            }
        }
        break;
    case '/api/v1/quiz/result':
        if ($method == 'POST') {
            $userId = Authenticate();
            if (is_numeric($userId)) {
                echo $quizController->CheckResult($data["quiz"], $userId);
            }
        }
        break;
    case '/api/v1/quiz/check':
        if ($method == 'POST') {
            if (!isset($data["course_topic"])) {
                return Response::json(404, [
                    'status' => 'error',
                    'message' => 'Topic is null.'
                ]);
            } else {
                $userId = Authenticate();
                if (is_numeric($userId)) {
                    echo $coursesController->CheckCourse($data["course_topic"], $userId);
                }
            }
        }
        break;
    case '/api/v1/login':
        if ($method == 'POST') {
            if (!isset($data["mobile"])) {
                return Response::json(404, [
                    'status' => 'error',
                    'message' => 'Please enter mobile number.'
                ]);
            } else if (!isset($data["otp"])) {
                return Response::json(404, [
                    'status' => 'error',
                    'message' => 'Please enter otp.'
                ]);
            } else {
                echo $authController->Login($data["mobile"], $data["otp"]);
            }
        }
        break;
    case '/api/v1/logout':
        if ($method == 'GET') {
            echo $authController->Logout();
        }
        break;
    case '/api/v1/redirect':
        if ($method == 'GET') {
            $userId = Authenticate();
            if (is_numeric($userId)) {
                echo $authController->RedirectUser($userId);
            }
        }
        break;
    case '/api/v1/otp':
        if ($method == 'POST') {
            if (!isset($data["mobile"])) {
                return Response::json(404, [
                    'status' => 'error',
                    'message' => 'Please enter mobile number.'
                ]);
            } else {
                echo $authController->RequestOtp($data["mobile"]);
            }
        }
        break;
    case '/api/v1/user/info':
        if ($method == 'POST') {
            $userId = Authenticate();
            if (is_numeric($userId)) {
                echo $userController->GetUserInfo($userId);
            }
        }
        break;
    case '/api/v1/user/dash':
        if ($method == 'POST') {
            $userId = Authenticate();
            if (is_numeric($userId)) {
                echo $userController->GetUserDashboardDetails($userId);
            }
        }
        break;
    case '/api/v1/leaderboard':
        if ($method == 'GET') {
            $userId = Authenticate();
            if (is_numeric($userId)) {
                echo $leaderController->GetLeaderBoard();
            }
        }
        break;
    default:
        return Response::json(404, [
            'status' => 'error',
            'message' => 'Path not found.',
            // 'req' => $request
        ]);
}

function CallHome()
{
    readfile('home.html');
}
