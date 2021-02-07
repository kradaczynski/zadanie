<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__.'/database.php';

use chillerlan\QRCode\QRCode;

/**
 * Main class to get and output all user data.
 */
class User {

  /**
   * Stores all user data.
   * @var array $userData
   */
  private $userData;

  /**
   * Stores all URL's in an associative array to get user data.
   * @var array URLS
   */
  private const URLS = [
    'user'       => 'https://jsonplaceholder.typicode.com/users/1',
    'userPosts'  => 'https://jsonplaceholder.typicode.com/users/1/posts',
    'userAlbums' => 'https://jsonplaceholder.typicode.com/users/1/albums',
    'userTodos'  => 'https://jsonplaceholder.typicode.com/users/1/todos'
  ];

  /**
   * Stores curl options.
   * @var array CURL_OPTIONS
   */
  private const CURL_OPTIONS = [
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_FAILONERROR => true
  ];

  /**
   * Retrieves user data by curling into all URL's stored in a constant and adds the results
   * into the private $userData array variable.
   * @return void
   */
  public function getUserData() {

    $this->showMethodName(__METHOD__);

    foreach (self::URLS as $key => $value) {

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $value);
      curl_setopt_array($ch, self::CURL_OPTIONS);
      $result = curl_exec($ch);

      if (curl_errno($ch)) {

        $error_msg = curl_error($ch);
      }

      curl_close($ch);

      if (isset($error_msg)) {

        echo 'Error while getting user information: '.$error_msg;
        die();

      } else {

        $this->userData[$key] = json_decode($result);
      }
    }
  }

  /**
   * Outputs all user data.
   * @return void
   */
  public function outputUserData() {

    $this->showMethodName(__METHOD__);

    foreach ($this->userData as $key => $value) {

      echo '<h3>'.$key.'</h3>';
      echo '<pre>'.json_encode($value, JSON_PRETTY_PRINT).'</pre>';
    }
  } 

  /**
   * Outputs e-mail domain only from user object.
   * @return void
   */
  public function getDomain() {

    $this->showMethodName(__METHOD__);

    $domain = explode("@", $this->userData['user']->email)[1];
    echo '<div>'.$domain.'</div>';
  }

  /**
   * Outputs user object as a JSON and generates a QR code from that JSON.
   * @return void
   */
  public function getPersonData() {

    $this->showMethodName(__METHOD__);

    echo '<pre>'.$encodedPersonData = json_encode($this->userData['user'], JSON_PRETTY_PRINT).'</pre>';
    echo '<div><img src="'.(new QRCode)->render($encodedPersonData).'" alt="QR Code" /></div>';
  }

  /**
   * Stores user email in a database table by initiating external Database class, if the e-mail 
   * domain is found it increments the occurrence counter.
   * @return void
   */
  public function saveEmailIntoDB() {

    $this->showMethodName(__METHOD__);

    $email = $this->userData['user']->email;
    
    $databaseClass = new Database();
    $databaseClass->saveEmailIntoDatabase($email);
  }

  /**
   * Outputs which method is currently executing.
   * @param string $method Method name.
   * @return void
   */

  private function showMethodName($method) {

    echo '<h4>Executing method '.$method.'</h4>';
  }

}

$user = new User();
$user->getUserData();
$user->outputUserData();
$user->getDomain();
$user->getPersonData();
$user->saveEmailIntoDB();

?>