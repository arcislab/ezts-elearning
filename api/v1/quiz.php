<?php
require_once __DIR__ .'/../helpers/MySqlCommands.php';
class Quiz
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    function Test()
    {
        $sqlHelp = new SqlHelper();
        $query = "INSERT INTO quizes_user (users_id, start_time, quizes_id, quizes_q_id, quizes_a_id, checked, last_updated) VALUES (1, NOW(), 1, 1, NULL, 1, NOW())";
        $result = $sqlHelp->executeQuery($query);
        return Response::json($result[0], $result[1]);
    }

    function CreateQuiz($topicId, $usersId)
    {
        //Create a quiz for user first with set of questions for test
        $query = "SELECT qs.id AS id, q.id AS quiz_id, qs.question AS question FROM quizes_q qs INNER JOIN quizes q ON q.id = qs.quizes_id WHERE q.courses_topics_id = ? ORDER BY RAND() LIMIT 3";
        $stmt = $this->db->prepare($query);

        if ($stmt === false) {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Query preparation failed: ' . $this->db->error
            ]);
        }

        $stmt->bind_param('i', $topicId);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $questions = $result->fetch_all(MYSQLI_ASSOC);

            // Check if no questions were returned
            if (empty($questions)) {
                return Response::json(404, [
                    'status' => 'error',
                    'message' => 'No questions found for the specified topic.'
                ]);
            }

            //Assign the questions to user
            $this->db->begin_transaction();
            try {
                // print_r($questions);
                $uuid = $this->getUniqueUUID();
                foreach ($questions as $question) {
                    $response = $this->CreateQuizQuestions($usersId, $question['quiz_id'], $question['id'], $uuid);
                    if (!$response) {
                        $this->db->rollback();
                        return Response::json(500, [
                            'status' => 'error',
                            'message' => 'Failed to create quiz for question ID: ' . $question['id']
                        ]);
                    }
                }
                $this->db->commit();
                return Response::json(200, [
                    'status' => 'success',
                    'uuid' => $uuid,
                    'message' => 'Quiz created successfully!'
                ]);
            } catch (Exception $e) {
                // Rollback transaction on error
                $this->db->rollback();
                return Response::json(500, [
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
            }
        } else {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Failed to retrieve questions: ' . $stmt->error
            ]);
        }
    }

    function getUniqueUUID()
    {
        do {
            $uuid = $this->generateRandomCode();
        } while ($this->CheckIfUUIDExist($uuid));

        return $uuid;
    }

    function generateRandomCode($length = 16)
    {
        // Define the characters to choose from
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomCode = '';

        // Generate the random code
        for ($i = 0; $i < $length; $i++) {
            $randomCode .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomCode;
    }

    function CheckIfUUIDExist($uuid)
    {
        $query = "SELECT * FROM quizes_user WHERE uuid = ?";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $uuid);

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

    function CreateQuizQuestions($usersId, $quizId, $questionId, $uuid)
    {
        $sqlHelp = new SqlHelper();
        $query = "INSERT INTO quizes_user(`users_id`, `start_time`, `quizes_id`, `quizes_q_id`, uuid, `last_updated`) VALUES (?, NOW(), ?, ?, ?, NOW())";
        $result = $sqlHelp->executeQuery($query, 'iiis', array($usersId, $quizId, $questionId, $uuid));
        // echo "Result: $result[0]";
        return $result[0] == 200 ? true : false;
    }

    function SendAssignedQuestions($topicId, $usersId)
    {
        $query = "SELECT q.id AS uuid, q.question AS question 
            FROM quizes_q q 
            INNER JOIN quizes_user qu ON qu.quizes_q_id = q.id 
            INNER JOIN quizes qui ON qui.id = q.quizes_id 
            WHERE qu.users_id = ? 
              AND qui.id = ? 
              AND TIMESTAMPDIFF(MINUTE, qu.last_updated, NOW()) <= qui.time 
              AND qu.checked = 0 
            GROUP BY q.id 
            ORDER BY RAND() 
            LIMIT 1;";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ii', $usersId, $topicId);

        if ($stmt === false) {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Query preparation failed: ' . $this->db->error
            ]);
        }

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $question = $result->fetch_assoc();
                $response = $this->CheckQuestion($question["uuid"]);
                if ($response[0] == 200) {
                    return Response::json(200, [
                        'status' => 'success',
                        'uuid' => $question["uuid"],
                        'question' => $question["question"],
                        'options' => $this->GetOptions($question["uuid"])
                    ]);
                } else {
                    return Response::json(500, [
                        'status' => 'error',
                        'message' => 'Failed to check question'
                    ]);
                }
            } else {
                return Response::json(200, [
                    'status' => 'error',
                    'message' => 'No quizzes found.'
                ]);
            }
        } else {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Failed to retrieve quiz details.'
            ]);
        }
    }

    function GetOptions($id)
    {
        $query = "SELECT id AS uuid, answer AS `option` FROM quizes_a WHERE quizes_q_id = ?";
        $stmt = $this->db->prepare($query);

        if ($stmt === false) {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Query preparation failed: ' . $this->db->error
            ]);
        }
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $options = $result->fetch_all(MYSQLI_ASSOC);
            return $options;
        } else {
            return null;
        }
    }
    
    function CheckQuestion($questionId)
    {
        $sqlHelp = new SqlHelper();
        $query = "UPDATE quizes_user SET checked = 1 WHERE quizes_q_id = ?";
        $result = $sqlHelp->executeQuery($query, 'i', array($questionId));
        return $result;
    }

    function GetQuizInfo($id)
    {
        $query = "SELECT ct.topic_name AS name, q.time AS time, q.questions_count AS questions FROM quizes q INNER JOIN courses_topics ct ON ct.id = q.courses_topics_id WHERE q.courses_topics_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id);

        if ($stmt === false) {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Query preparation failed: ' . $this->db->error
            ]);
        }

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $quiz = $result->fetch_assoc();
                
                return Response::json(200, [
                    'status' => 'success',
                    'quiz' => $quiz
                ]);
            } else {
                return Response::json(200, [
                    'status' => 'error',
                    'message' => 'No quizzes found.'
                ]);
            }
        } else {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Failed to retrieve quiz details.'
            ]);
        }
    }

    function GetQuizDetails($id)
    {
        $query = "SELECT id AS uuid, grand_test AS gt, time FROM quizes WHERE courses_topics_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id);

        if ($stmt === false) {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Query preparation failed: ' . $this->db->error
            ]);
        }

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $quiz = $result->fetch_assoc();

                return Response::json(200, [
                    'status' => 'success',
                    'quiz' => $quiz,
                    'questions' => $this->GetAllQuestions($id)
                ]);
            } else {
                return Response::json(200, [
                    'status' => 'error',
                    'message' => 'No quizzes found.'
                ]);
            }
        } else {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Failed to retrieve quiz details.'
            ]);
        }
    }

    function StartQuiz($quizId, $usersId) {}

    function CompleteQuiz($quizId, $usersId) {}

    function ExitQuiz($quizUserId, $usersId)
    {
        $sqlHelp = new SqlHelper();
        $query = "UPDATE quizes_user SET checked = -1 WHERE uuid = ?";
        $result = $sqlHelp->executeQuery($query, 'i', array($quizUserId));

        if ($result[0] == 200) {
            return Response::json(200, [
                'status' => 'success',
                'message' => 'Quiz exited successfully'
            ]);
        } else {
            return Response::json($result[0], $result[1]);
        }
    }

    function GetQuestions($quizId) {}

    function GetQuestion($questionId)
    {
        $query = "SELECT id AS id, question FROM quizes_q WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $questionId);

        if ($stmt === false) {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Query preparation failed: ' . $this->db->error
            ]);
        }

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $question = $result->fetch_assoc();
                return Response::json(200, [
                    'status' => 'success',
                    'question' => [
                        "uuid" => $question['id'],
                        "value" => $question['question']
                    ]
                ]);
            } else {
                return Response::json(200, [
                    'status' => 'success',
                    'question' => []
                ]);
            }
        } else {
            return null;
            // return Response::json(500, [
            //     'status' => 'error',
            //     'message' => 'Failed to retrieve courses.'
            // ]);
        }
    }

    function GetAnswers($quizQId) {}

    function GetAnswer($uuid)
    {
        $query = "SELECT id AS id, answer, correct, explaination FROM quizes_a WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $uuid);

        if ($stmt === false) {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Query preparation failed: ' . $this->db->error
            ]);
        }

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $question = $result->fetch_assoc();
                return Response::json(200, [
                    'status' => 'success',
                    'answer' => [
                        "uuid" => $question['id'],
                        "value" => $question['answer'],
                        "correct" => $question['correct'] == 1 ? true : false,
                        "explaination" => $question['explaination'],
                    ]
                ]);
            } else {
                return Response::json(200, [
                    'status' => 'success',
                    'question' => []
                ]);
            }
        } else {
            return null;
            // return Response::json(500, [
            //     'status' => 'error',
            //     'message' => 'Failed to retrieve courses.'
            // ]);
        }
    }

    function CheckResult($quizData, $usersId)
    {
        $data = [];
        foreach ($quizData as $item) {
            $questionId = $item['questionId'];
            $selectedOption = $item['selectedOption'];
            $query = "SELECT question FROM quizes_q WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $questionId);

            if ($stmt === false) {
                return Response::json(500, [
                    'status' => 'error',
                    'message' => 'Query preparation failed: ' . $this->db->error
                ]);
            }

            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $question = $result->fetch_assoc();
                    $data[] = [
                        'question' => $question["question"],
                        'correct' => $this->CheckIfAnswerIsCorrect($questionId, $selectedOption),
                        'options' => $this->GetA($questionId, $selectedOption)
                    ];
                } else {
                    return Response::json(500, [
                        'status' => 'error',
                        'question' => 'Result is empty'
                    ]);
                }
            } else {
                return null;
                // return Response::json(500, [
                //     'status' => 'error',
                //     'message' => 'Failed to retrieve courses.'
                // ]);
            }
        }
        return Response::json(200, [
            'status' => 'success',
            'result' => [
                $data
            ]
        ]);
    }

    function GetA($questionId, $selectedOption)
    {
        $query = "SELECT a.id AS id, a.answer AS answer, a.correct AS correct, a.explaination AS `explain` FROM quizes_a a 
        INNER JOIN quizes_q q ON q.id = a.quizes_q_id 
        WHERE q.id = ?";

        $stmt = $this->db->prepare($query);

        if ($stmt === false) {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Query preparation failed: ' . $this->db->error
            ]);
        }
        $stmt->bind_param('i', $questionId);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $answers = $result->fetch_all(MYSQLI_ASSOC);
            $data = [];
            foreach ($answers as $answer){
                $item = [
                    'answer' => $answer['answer'],
                    'correct' => $answer['correct'],
                    'explaination' => $answer['explain']
                ];

                if ($answer['id'] == $selectedOption) {
                    $item['selected'] = true;
                }
            
                $data[] = $item;
            }
            return $data;
        } else {
            return null;
        }
    }

    function CheckIfAnswerIsCorrect($questionId, $answerId)
    {
        $query = "SELECT COUNT(*) as correct_count FROM quizes_a WHERE quizes_q_id = ? AND id = ? AND correct = 1;";
        $stmt = $this->db->prepare($query);

        if ($stmt === false) {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Query preparation failed: ' . $this->db->error
            ]);
        }

        $stmt->bind_param('ii', $questionId, $answerId);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $row = $result->fetch_assoc(); // Fetch a single row
            return $row['correct_count'] > 0; // Return true if count is greater than 0
        } else {
            return null; // You might want to handle the error differently
        }
    }

    function CheckIfQuizAllowed($topicId, $usersId, $returnJson = true)
    {
        //Calculation: Total sub-topics checked >= Total sub-topics
        $query = "SELECT ct.id AS `Topic Id`, q.time AS time,
                CASE 
                    WHEN 
                        (SELECT COUNT(*) FROM courses_sub_topics_user cstu 
                         INNER JOIN courses_sub_topics cst 
                         ON cst.id = cstu.courses_sub_topics_id 
                         WHERE cstu.video_checked_seconds >= (cst.duration - 20) 
                         AND cst.courses_topics_id = ct.id AND cstu.users_id = $usersId) >= 
                        (SELECT COUNT(*) FROM courses_sub_topics cst
                         WHERE cst.courses_topics_id = ct.id)
                    THEN TRUE
                    ELSE FALSE
                END AS Result
           FROM courses_topics ct
           LEFT JOIN quizes q ON q.courses_topics_id = ct.id
           WHERE ct.id = $topicId";

        $stmt = $this->db->prepare($query);
        // $stmt->bind_param('iii', $usersId, $usersId, $topicId);
        if ($stmt === false) {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Query preparation failed: ' . $this->db->error
            ]);
        }

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $quizAllowed = $result->fetch_assoc();
                if ($returnJson) {
                    return Response::json(200, [
                        'status' => 'success',
                        'allowed' => $quizAllowed['Result'] == 1 ? true : false,
                        'time' => $quizAllowed['time']
                    ]);
                } else {
                    return $quizAllowed['Result'] == 1 ? true : false;
                }
            } else {
                if ($returnJson) {
                    return Response::json(200, [
                        'status' => 'success',
                        'message' => 'Quiz not found'
                    ]);
                } else {
                    return null;
                }
            }
        } else {
            if ($returnJson) {
                return Response::json(500, [
                    'status' => 'error',
                    'message' => 'Failed to retrieve courses.'
                ]);
            } else {
                return null;
            }
        }
    }
    //Admin section
    function AddQuiz($topic, $gt, $time)
    {
        $sqlHelp = new SqlHelper();
        $query = "INSERT INTO quizes(`courses_topics_id`, `grand_test`, `time`) VALUES (?, ?, ?)";
        $result = $sqlHelp->executeQuery($query, 'iss', array($topic, $gt, $time));
        return Response::json($result[0], $result[1]);
    }

    function UpdateQuiz($id, $gt, $time)
    {
        $sqlHelp = new SqlHelper();
        $query = "UPDATE quizes SET `grand_test` = ?, `time` = ? WHERE id = ?";
        $result = $sqlHelp->executeQuery($query, 'iii', array($gt, $time, $id));
        return Response::json($result[0], $result[1]);
    }

    function DeleteQuiz($id)
    {
        $sqlHelp = new SqlHelper();
        $query = "DELETE FROM quizes WHERE id = ?";
        $result = $sqlHelp->executeQuery($query, 'i', array($id));
        return Response::json($result[0], $result[1]);
    }

    function AddQuestion($quizId, $question)
    {
        $sqlHelp = new SqlHelper();
        $query = "INSERT INTO quizes_q(`quizes_id`, `question`) VALUES (?, ?)";
        $result = $sqlHelp->executeQuery($query, 'is', array($quizId, $question));
        return Response::json($result[0], $result[1]);
    }

    function UpdateQuestion($id, $question)
    {
        $sqlHelp = new SqlHelper();
        $query = "UPDATE quizes_q SET `question` = ? WHERE id = ?";
        $result = $sqlHelp->executeQuery($query, 'si', array($question, $id));
        return Response::json($result[0], $result[1]);
    }

    function DeleteQuestion($id)
    {
        $sqlHelp = new SqlHelper();
        $query = "DELETE FROM quizes_q WHERE id = ?";
        $result = $sqlHelp->executeQuery($query, 'i', array($id));
        return Response::json($result[0], $result[1]);
    }
    function AddAnswer($questionId, $answer, $correct, $explaination, $subtopicId)
    {
        $sqlHelp = new SqlHelper();
        $query = "INSERT INTO quizes_a(`quizes_q_id`, `answer`, `correct`, `explaination`, `courses_sub_topics_id`) VALUES (?, ?, ?, ?, ?)";
        $result = $sqlHelp->executeQuery($query, 'isisi', array($questionId, $answer, $correct, $explaination, $subtopicId));
        return Response::json($result[0], $result[1]);
    }

    function UpdateAnswer($id, $answer, $correct, $explaination)
    {
        $sqlHelp = new SqlHelper();
        $query = "UPDATE quizes_a SET `answer` = ?, `correct` = ?, `explaination` = ? WHERE id = ?";
        $result = $sqlHelp->executeQuery($query, 'sisi', array($answer, $correct, $explaination, $id));
        return Response::json($result[0], $result[1]);
    }

    function DeleteAnswer($id)
    {
        $sqlHelp = new SqlHelper();
        $query = "DELETE FROM quizes_a WHERE id = ?";
        $result = $sqlHelp->executeQuery($query, 'i', array($id));
        return Response::json($result[0], $result[1]);
    }

    function GetAllQuestions($id)
    {
        $query = "SELECT qs.id AS uuid, qs.question AS question FROM quizes_q qs INNER JOIN quizes q ON q.id = qs.quizes_id WHERE q.courses_topics_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id);

        if ($stmt === false) {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Query preparation failed: ' . $this->db->error
            ]);
        }

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $topics = $result->fetch_all(MYSQLI_ASSOC);
            return $topics;

            // return Response::json(200, [
            //     'status' => 'success',
            //     'questions' => $topics
            // ]);
        } else {
            return null;
            // return Response::json(500, [
            //     'status' => 'error',
            //     'message' => 'Failed to retrieve courses.'
            // ]);
        }
    }

    function GetAnswersInfo($question)
    {
        $query = "SELECT id AS uuid, answer, correct, explaination FROM quizes_a WHERE quizes_q_id = ?";
        $stmt = $this->db->prepare($query);

        if ($stmt === false) {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Query preparation failed: ' . $this->db->error
            ]);
        }
        $stmt->bind_param('i', $question);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $answers = $result->fetch_all(MYSQLI_ASSOC);

            return Response::json(200, [
                'status' => 'success',
                'answers' => $answers
            ]);
        } else {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Failed to retrieve courses.'
            ]);
        }
    }
}
