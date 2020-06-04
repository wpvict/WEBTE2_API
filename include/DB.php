<?php

  class DB{
    protected $connection;

    function __construct($host, $database_name, $user, $password){
      try{
        $this->connection = new PDO("mysql:host=$host;dbname=$database_name;", $user, $password);
      } catch(PDOException $e){
        die();
      }
    }

    public function add_log($commands, $success, $error, $date){
      $query = $this->connection->prepare("INSERT INTO log (commands, success, error, date) VALUES (:commands, :success, :error, :date);");
      $query->bindParam(":commands", $commands);
      $query->bindParam(":success", $success);
      $query->bindParam(":error", $error);
      $query->bindParam(":date", $date);

      $query->execute();
    }

    public function get_log(){
      $query = $this->connection->prepare("SELECT * FROM log;");

      if($query->execute()){
        return $query->fetchAll();
      }
    }

    public function add_statistics($page, $ip, $date){
      $query = $this->connection->prepare("INSERT INTO statistics (page, ip, date) VALUES (:page, :ip, :date);");
      $query->bindParam(":page", $page);
      $query->bindParam(":ip", $ip);
      $query->bindParam(":date", $date);

      $query->execute();
    }

  };


 ?>
