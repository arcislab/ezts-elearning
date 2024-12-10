<?php
require_once __DIR__ .'/../helpers/MySqlCommands.php';

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