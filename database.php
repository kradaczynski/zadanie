<?php

/**
 * Database class used for all operations on the database.
 */
class Database {

  /**
   * Stores connection configuration credentials.
   * @var array $config
   */
  private $config;

  /**
   * Stores mysqli connection object.
   * @var object|false $connection
   */
  private $connection;

  /**
   * Stores database name.
   * @var string DATABASE_NAME
   */
  private const DATABASE_NAME = 'zadanie';

  /**
   * Stores a SQL query for creating a database if it does not exists.
   * @var string CREATE_DATABASE
   */
  private const CREATE_DATABASE = "CREATE DATABASE IF NOT EXISTS zadanie DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci";

  /**
   * Stores a SQL query for creating a domain table if it does not exists.
   * @var string CREATE_TABLE
   */
  private const CREATE_TABLE = 'CREATE TABLE IF NOT EXISTS domain( '.
    'id INT NOT NULL AUTO_INCREMENT, '.
    'email VARCHAR(100) NULL, '.
    'count INT NULL DEFAULT 1, '.
    'primary key ( id ))';

  /**
   * Class constructor, stores connection configuration in a private variable and initializes the database.
   * @return void
   */
  public function __construct() {

    require __DIR__.'/db_config.php';

    $this->config = $dbConfig;

    $this->initializeDatabase();
  }

  /**
   * Connects to the database, stores the connection object in a private
   * variable and creates a database and a table.
   * @return void
   */
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

  /**
   * Executes a database query.
   * @param string $query
   * @param bool $selectDatabase
   * @return object
   */
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

  /**
   * Checks if a given e-mail domain exists in a database, if not inserts a new row with that email,
   * otherwise increments the occurence counter in that row.
   * @param string $email
   * @return void
   */

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

  /**
   * Updates a row by incrementing its counter.
   * @param int $id ID of the row to update.
   * @param int $count Counter number to increment.
   * @return void
   */
  private function updateRow($id, $count) {

    $sql = "UPDATE domain SET count = count + 1 WHERE id = '".$id."'";
    $result = $this->executeQuery($sql, true);
    echo '<div>Counter incremented, current counter: '.++$count.'</div>';
  }
}

?>