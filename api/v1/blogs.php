<?php
require_once './api/helpers/MySqlCommands.php';

class Blogs{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    function GetBlogs(){
        
    }
}

?>