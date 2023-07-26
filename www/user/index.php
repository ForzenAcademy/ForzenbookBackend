<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$servername = "localhost";
$username = "worker";
$password = "swiffcat";
$db = "socialmedia";

$ABOUT_ME_DEFAULT = "ABOUT_ME_DEFAULT_VALUE";
$DEFAULT_ICON = "upload/pp_default.jpg";

// If not GET or POST, 403
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // If email not present in POST, 400
  if (
    !isset($_POST["email"]) || !isset($_POST["first_name"])
    || !isset($_POST["last_name"]) || !isset($_POST["birth_date"])
    || !isset($_POST["location"])
  ) {
    header("HTTP/1.1 400 Bad Request");
    die("{\"reason\":\"missing field\"}");
  }

  // Validate fields
  $email = $_POST["email"];
  if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 64) {
    header("HTTP/1.1 400 Bad Request");
    die("{\"reason\":\"invalid email\"}");
  }

  $firstName = $_POST["first_name"];
  if (!preg_match("/^[a-zA-Z-' ]*$/", $firstName) || strlen($firstName) > 20) {
    header("HTTP/1.1 400 Bad Request");
    die("{\"reason\":\"invalid first name\"}");
  }

  $lastName = $_POST["last_name"];
  if (!preg_match("/^[a-zA-Z-' ]*$/", $lastName) || strlen($lastName) > 20) {
    header("HTTP/1.1 400 Bad Request");
    die("{\"reason\":\"invalid last name\"}");
  }

  $location = $_POST["location"];
  if (strlen($location) > 64) {
    header("HTTP/1.1 400 Bad Request");
    die("{\"reason\":\"location too long\"}");
  }

  $birthDate = $_POST["birth_date"];
  $validdate = preg_match('#^(?P<year>\d{2}|\d{4})([- /.])(?P<month>\d{1,2})\2(?P<day>\d{1,2})$#', $birthDate, $matches)
    && checkdate($matches['month'], $matches['day'], $matches['year']);
  if (!$validdate) {
    header("HTTP/1.1 400 Bad Request");
    die("{\"reason\":\"invalid birth date format\"}");
  }
  if (strtotime($birthDate) > strtotime('-13 years')) {
    header("HTTP/1.1 400 Bad Request");
    die("{\"reason\":\"age too young\"}");
  }

  // Create connection
  $conn = new mysqli($servername, $username, $password, $db);

  // Check connection
  if ($conn->connect_error) {
    header("HTTP/1.1 500 Internal Server Error");
    die();
  }

  // If user exists, die
  $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
  $stmt->bind_param("s", $a1);
  $a1 = $email;
  $stmt->execute();
  $stmt->store_result();
  $count = $stmt->num_rows;
  if ($count > 0) {
    header("HTTP/1.1 409 Conflict");
    die("{\"reason\":\"conflict\"}");
  }

  // Add user to DB
  $stmt = $conn->prepare("INSERT INTO users (email, birth_date, first_name, last_name, location, about_me, profile_image) VALUES (?,?,?,?,?,?,?)");
  $stmt->bind_param("sssssss", $a1, $a2, $a3, $a4, $a5, $a6, $a7);
  $a1 = $email;
  $a2 = $birthDate;
  $a3 = $firstName;
  $a4 = $lastName;
  $a5 = $location;
  $a6 = $ABOUT_ME_DEFAULT;
  $a7 = $DEFAULT_ICON;
  $stmt->execute();
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  if (!isset($_GET["id"])) {
    header("HTTP/1.1 400 Bad Request");
    die("{\"reason\":\"missing field\"}");
  }
  $id = $_GET["id"];

  // Create connection
  $conn = new mysqli($servername, $username, $password, $db);

  // Check connection
  if ($conn->connect_error) {
    header("HTTP/1.1 500 Internal Server Error");
    die();
  }

  $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
  $stmt->bind_param("s", $a1);
  $a1 = $id;
  $stmt->execute();
  $result = $stmt->get_result();

  // Check if there's at least one row in the result
  if ($result->num_rows > 0) {
    // Fetch the data as an associative array
    $row = $result->fetch_assoc();

    // Convert the associative array to a JSON string
    $json = json_encode($row);

    // Print or use the JSON string as needed
    echo $json;
  } else {
    header("HTTP/1.1 401 Unauthorized");
    die("{\"reason\":\"user does not exist\"}");
  }

  // Close the statement
  $stmt->close();
} else {
  header("HTTP/1.1 403 Forbidden");
  die("{\"reason\":\"invalid type\"}");
}
