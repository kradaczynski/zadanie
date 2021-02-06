<?php

class Database {

  private $config;

  private $connection;

  private const DATABASE_NAME = 'zadanie';

  private const CREATE_DATABASE = "CREATE DATABASE IF NOT EXISTS zadanie DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci";

  private const CREATE_TABLE = 'CREATE TABLE IF NOT EXISTS domain( '.
    'id INT NOT NULL AUTO_INCREMENT, '.
    'email VARCHAR(100) NULL, '.
    'count INT NULL DEFAULT 1, '.
    'primary key ( id ))';

  public function __construct() {

    require __DIR__.'/db_config.php';

    $this->config = $dbConfig;

    $this->initializeDatabase();
  }

  private function initializeDatabase() {

    $connection = @mysqli_connect($this->config['host'], $this->config['user'], $this->config['password']);

    if (mysqli_connect_errno()) {
      
      echo "Connection failed: ".mysqli_connect_error();
      die();
    }
      
    $this->connection = $connection;
    $this->executeQuery(self::CREATE_DATABASE);
    $this->executeQuery(self::CREATE_TABLE, true);
  }

  private function executeQuery($query, $selectDatabase = false) {

    if ($selectDatabase) {

      mysqli_select_db($this->connection, self::DATABASE_NAME);
    }

    if (!$result = mysqli_query($this->connection, $query)) {

      echo "Error ! ".mysqli_error($this->connection);
      die();

    } else {

      return $result;
    }
  }

  public function saveEmailIntoDatabase($email) {

    $domain = explode("@", $email)[1];

    $query = 'SELECT * FROM domain WHERE domain.email LIKE "%'.$domain.'%"';

    $result = $this->executeQuery($query, true);

    if (mysqli_num_rows($result) > 0) {

      while($row = mysqli_fetch_object($result)) {
        
        echo '<div>Found domain '.$domain.' in the database, current counter: '.$row->count.', incrementing counter.</div>';
        $this->updateRow((int) $row->id, (int) $row->count);
      }
    } else {

      echo '<div>Could not find domain '.$domain.' in the database, adding new row.</div>';
      $insert = "INSERT INTO domain (email) values ('".$email."')";
      $result = $this->executeQuery($insert, true);
    }

    mysqli_close($this->connection);
  }

  private function updateRow($id, $count) {

    $sql = "UPDATE domain SET count = count + 1 WHERE id = '".$id."'";
    $result = $this->executeQuery($sql, true);
    echo '<div>Counter incremented, current counter: '.++$count.'</div>';
  }
}

?>