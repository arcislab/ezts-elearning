<?php
require_once './api/helpers/MySqlCommands.php';
class Quiz
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    function GetQuiz($courseId)
    {
        $query = "SELECT qs.id AS id, qs.question AS question FROM quizes_q qs INNER JOIN quizes q ON q.id = qs.quizes_id WHERE q.courses_topics_id = ? ORDER BY RAND() LIMIT 1";
        $stmt = $this->db->prepare($query);

        if ($stmt === false) {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Query preparation failed: ' . $this->db->error
            ]);
        }

        $stmt->bind_param('i', $courseId);

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
                    'questions' => []
                ]);
            }
        } else {
            return Response::json(500, [
                'status' => 'error',
                'message' => 'Failed to retrieve questions.'
            ]);
        }
    }

    function StartQuiz($quizId, $usersId) {}

    function CompleteQuiz($quizId, $usersId) {}

    function ExitQuiz($quizId, $usersId) {}

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

    function SaveAnswer($quizQId, $quizAId, $usersId) {}

    function CheckResult($quizId, $usersId) {}

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
