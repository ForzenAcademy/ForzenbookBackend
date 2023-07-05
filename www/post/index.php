<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

function generateRandomString($length = 24) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

$servername = "localhost";
$username = "worker";
$password = "swiffcat";
$db = "socialmedia";

// If not POST, 403
if ($_SERVER['REQUEST_METHOD'] === 'POST'){

// If postType not present in POST, 400
if(!isset($_POST["postType"])) {
    header("HTTP/1.1 400 Bad Request");
    die("{\"reason\":\"missing field\"}");
}
$postType = $_POST["postType"];

$body = null;
// Validate fields
if($postType == "text") {
    // If body and postType not present in POST, 400
    if(!isset($_POST["body"])) {
        header("HTTP/1.1 400 Bad Request");
        die("{\"reason\":\"missing body\"}");
    }

    $body = $_POST["body"];
    if (strlen($body) > 256) {
        header("HTTP/1.1 400 Bad Request");
        die("{\"reason\":\"invalid post length\"}");
    }
}

// if($postType == "image") {
//     if(!isset($_FILES["fileToUpload"])) {
//         header("HTTP/1.1 400 Bad Request");
//         die("{\"reason\":\"missing image\"}");
//     }
// }

if ($postType != "text" && $postType != "image") {
header("HTTP/1.1 400 Bad Request");
  die("{\"reason\":\"invalid post type\"}");
}

// Create connection
$conn = new mysqli($servername, $username, $password, $db);

// Check connection
if ($conn->connect_error) {
    header("HTTP/1.1 500 Internal Server Error");
    die();
}

$headers = getallheaders();
if(!array_key_exists("token", $headers)){
  header("HTTP/1.1 401 Unauthorized");
  die("{\"reason\":\"token not included\"}");
}
$token = getallheaders()["token"];

// If invalid header token, die
$stmt = $conn->prepare("SELECT user_id FROM auth WHERE token = ?");
$stmt->bind_param("s", $a1);
$a1 = $token;
$stmt->execute();
$stmt->store_result();
$user_id = null;
$stmt->bind_result($user_id);
if ($stmt->fetch()) {
    // user_id is bound
} else {
    header("HTTP/1.1 401 Unauthorized");
    die();
}



if($postType == "image") {
    $target_dir = "../upload/";
    $target_file = $target_dir . basename(generateRandomString());
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($_FILES["fileToUpload"]["name"],PATHINFO_EXTENSION));

    // Check if file already exists
    if (file_exists($target_file)) {
        header("HTTP/1.1 400 Bad Request");
        die();
    }

    // Check file size
    if ($_FILES["fileToUpload"]["size"] > 2000000) {
        header("HTTP/1.1 400 Bad Request");
        die("{\"reason\":\"too large\"}");
    }
  
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        header("HTTP/1.1 400 Bad Request");
        die("{\"reason\":\"invalid img format\"}");
    }

    $body = $target_file.".".$imageFileType;
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $body)) {
        // success
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        die();
    }
    $body = substr($body, 3);
}

// Add post to DB
$stmt = $conn->prepare("INSERT INTO posts (user_id, body, post_type) VALUES (?,?,?)");
$stmt->bind_param("sss", $a1, $a2, $a3);
$a1 = $user_id;
$a2 = $body;
$a3 = $postType;
$stmt->execute();

}
else {
    header("HTTP/1.1 403 Forbidden");
    die("{\"reason\":\"invalid type\"}");
}
?>
