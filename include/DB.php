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

    public function get_docs(){
      $query = $this->connection->prepare("SELECT * FROM docs;");

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

    public function get_statistics(){
      $result = [];

      $categories = ['pendulum', 'ball', 'suspension', 'aircraft'];
      foreach($categories as $item){
        $query = $this->connection->prepare("SELECT COUNT(ip) FROM statistics WHERE page LIKE :page;");
        $query->bindParam(":page", $item);
        $query->execute();
        $result[$item] = $query->fetchAll()[0][0];
      }

      return $result;
    }

    public function pendulum_set_values($position, $angle){
      $query = $this->connection->prepare("UPDATE pendulum SET position = :position, angle = :angle WHERE id LIKE 1;");
      $query->bindParam(":position", $position);
      $query->bindParam(":angle", $angle);

      $query->execute();
    }

    public function pendulum_get_values(){
      $query = $this->connection->prepare("SELECT * FROM pendulum WHERE id LIKE 1;");

      $query->execute();

      return $query->fetchAll();
    }

    public function ball_set_values($position, $angle){
      $query = $this->connection->prepare("UPDATE ball SET position = :position, angle = :angle WHERE id LIKE 1;");
      $query->bindParam(":position", $position);
      $query->bindParam(":angle", $angle);

      $query->execute();
    }

    public function ball_get_values(){
      $query = $this->connection->prepare("SELECT * FROM ball WHERE id LIKE 1;");

      $query->execute();

      return $query->fetchAll();
    }

  };


 ?>
