<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__.'/database.php';

use chillerlan\QRCode\QRCode;

class User {

  private $userData;

  private const URLS = [
    'user'       => 'https://jsonplaceholder.typicode.com/users/1',
    'userPosts'  => 'https://jsonplaceholder.typicode.com/users/1/posts',
    'userAlbums' => 'https://jsonplaceholder.typicode.com/users/1/albums',
    'userTodos'  => 'https://jsonplaceholder.typicode.com/users/1/todos'
  ];

  private const CURL_OPTIONS = [
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_FAILONERROR => true
  ];

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

  public function outputUserData() {

    $this->showMethodName(__METHOD__);

    foreach ($this->userData as $key => $value) {

      echo '<h3>'.$key.'</h3>';
      echo '<pre>'.json_encode($value, JSON_PRETTY_PRINT).'</pre>';
    }
  } 

  public function getDomain() {

    $this->showMethodName(__METHOD__);

    $domain = explode("@", $this->userData['user']->email)[1];
    echo '<div>'.$domain.'</div>';
  }

  public function getPersonData() {

    $this->showMethodName(__METHOD__);

    echo '<pre>'.$encodedPersonData = json_encode($this->userData['user'], JSON_PRETTY_PRINT).'</pre>';
    echo '<div><img src="'.(new QRCode)->render($encodedPersonData).'" alt="QR Code" /></div>';
  }

  public function saveEmailIntoDB() {

    $this->showMethodName(__METHOD__);

    $email = $this->userData['user']->email;
    
    $databaseClass = new Database();
    $databaseClass->saveEmailIntoDatabase($email);
  }

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