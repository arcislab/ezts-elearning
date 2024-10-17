<?php
require_once './api/config/database.php';
require_once './api/helpers/Response.php';

class Autofill
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    function AutoFillDB()
    {
        $this->ResetTables();
        $this->AddUsers();
        $this->AddCourse();
    }

    function ResetTables()
    {
        $sql = "SHOW TABLES";
        $result = $this->db->query($sql);
        $tables = [];
        $message = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_array()) {
                $tables[] = $row[0]; //Collect all table names
            }
        }

        foreach ($tables as $table) {
            //Disable foreign key checks temporarily
            if (!$this->db->query("SET FOREIGN_KEY_CHECKS=0")) {
                $message[] = "Error disabling foreign key checks: " . $this->db->error;
                continue;
            }

            //Delete all rows in the table
            if (!$this->db->query("DELETE FROM `$table`")) {
                $message[] = "Error deleting rows from `$table`: " . $this->db->error;
                continue;
            }

            //Reset the auto-increment for the table (primary key reset)
            if (!$this->db->query("ALTER TABLE `$table` AUTO_INCREMENT = 1")) {
                $message[] = "Error resetting auto-increment on `$table`: " . $this->db->error;
                continue;
            }

            //Enable foreign key checks again
            if (!$this->db->query("SET FOREIGN_KEY_CHECKS=1")) {
                $message[] = "Error enabling foreign key checks: " . $this->db->error;
                continue;
            }
        }
        $message[] = "All tables cleared and primary keys reset.";
        return $message;
    }

    function AddUsers()
    {
        $array = [
            'INSERT INTO `users`(`first_name`, `last_name`, `mobile`, `city`, `email`, `otp_verified`) VALUES ("John", "Doe", "1234567890", "New York", "john.doe@example.com", 1)',
            'INSERT INTO `users`(`first_name`, `last_name`, `mobile`, `city`, `email`, `otp_verified`) VALUES ("Jane", "Smith", "0987654321", "Los Angeles", "jane.smith@example.com", 0)',
            'INSERT INTO `users`(`first_name`, `last_name`, `mobile`, `city`, `email`, `otp_verified`) VALUES ("Robert", "Johnson", "1112223333", "Chicago", "robert.j@example.com", 1)',
            'INSERT INTO `users`(`first_name`, `last_name`, `mobile`, `city`, `email`, `otp_verified`) VALUES ("Emily", "Davis", "4445556666", "Houston", "emily.davis@example.com", 1)',
            'INSERT INTO `users`(`first_name`, `last_name`, `mobile`, `city`, `email`, `otp_verified`) VALUES ("Michael", "Brown", "7778889999", "Phoenix", "michael.brown@example.com", 0)'
        ];
        $successCount = 0;
        $errorMessages = [];

        foreach ($array as $query) {
            if ($this->db->query($query) === TRUE) {
                $successCount += $this->db->affected_rows;
            } else {
                $errorMessages[] = 'Failed to execute query: ' . $this->db->error;
            }
        }

        if ($successCount > 0) {
            $response = [
                'status' => 'success',
                'message' => $successCount . ' total Users added successfully!'
            ];
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Failed to autofill.',
                'errors' => $errorMessages
            ];
        }

        return $response;
    }

    function AddCourseType()
    {
        $array = [
            'INSERT INTO `courses_types`(`type`) VALUES ("Programming")',
            'INSERT INTO `courses_types`(`type`) VALUES ("Architecture")',
            'INSERT INTO `courses_types`(`type`) VALUES ("Data Science")',
            'INSERT INTO `courses_types`(`type`) VALUES ("Cybersecurity")',
            'INSERT INTO `courses_types`(`type`) VALUES ("Artificial Intelligence")',
            'INSERT INTO `courses_types`(`type`) VALUES ("Business Management")',
            'INSERT INTO `courses_types`(`type`) VALUES ("Graphic Design")',
            'INSERT INTO `courses_types`(`type`) VALUES ("Marketing")',
        ];
        $successCount = 0;
        $errorMessages = [];

        foreach ($array as $query) {
            if ($this->db->query($query) === TRUE) {
                $successCount += $this->db->affected_rows;
            } else {
                $errorMessages[] = 'Failed to execute query: ' . $this->db->error;
            }
        }

        if ($successCount > 0) {
            $response = [
                'status' => 'success',
                'message' => $successCount . ' total Users added successfully!'
            ];
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Failed to autofill.',
                'errors' => $errorMessages
            ];
        }

        return $response;
    }

    function AddCourse()
    {
        $dummy = new AutoFillDummy();
        $data = $dummy->getCourseData('Programming');
        foreach($data as $course){
            echo '<pre>';
            print_r($course);
            echo '</pre>';
        }
    }
}

class AutoFillDummy
{
    private $coursesData;

    public function __construct()
    {
        $this->coursesData = [
            'Programming' => $this->generateCourseData('Programming'),
            'Architecture' => $this->generateCourseData('Architecture'),
            'Data Science' => $this->generateCourseData('Data Science'),
            'Cybersecurity' => $this->generateCourseData('Cybersecurity'),
            'Artificial Intelligence' => $this->generateCourseData('Artificial Intelligence'),
            'Business Management' => $this->generateCourseData('Business Management'),
            'Graphic Design' => $this->generateCourseData('Graphic Design'),
            'Marketing' => $this->generateCourseData('Marketing'),
        ];
    }

    private function generateCourseData($courseName)
    {
        $courses = [];
        for ($h = 1; $h <= 5; $h++) {
            $course = "Course $h of $courseName";
            $topics = [];
            for ($i = 1; $i <= 5; $i++) {
                $topic = "Topic $i of $course";
                $subtopics = [];
                for ($j = 1; $j <= 5; $j++) {
                    $subtopic = "Subtopic $j of $topic";
                    $questions = [];
                    for ($k = 1; $k <= 5; $k++) {
                        $question = "Question $k for $subtopic?";
                        $answer = "Answer to question $k for $subtopic.";
                        $questions[] = ['question' => $question, 'answer' => $answer];
                    }
                    $subtopics[] = ['subtopic' => $subtopic, 'questions' => $questions];
                }
                $topics[] = ['topic' => $topic, 'subtopics' => $subtopics];
            }
            $courses[]  = ['course' => $course, 'topics' => $topics];
        }
        // return $courses;
        $courseData = $this->generateCourseData($courseName);
        return json_encode($courseData, JSON_PRETTY_PRINT);
    }
    
    public function getCourseData($courseName)
    {
        if (array_key_exists($courseName, $this->coursesData)) {
            return $this->coursesData[$courseName];
        } else {
            return "Course not found.";
        }
    }
}
