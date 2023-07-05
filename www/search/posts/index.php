<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Connect to the database (replace hostname, username, password, and dbname with your own)
$conn = mysqli_connect("localhost", "worker", "swiffcat", "socialmedia");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
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

if(!isset($_GET["uid"]) && !isset($_GET["q"])) {
    header("HTTP/1.1 400 Bad Request");
    die("{\"reason\":\"missing field\"}");
}


if(isset($_GET["uid"])) {
    $uid = $_GET["uid"];

    // Retrieve data from the posts table
    $stmt = $conn->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $a1);
    $a1 = $uid;
    $stmt->execute();
    $result = $stmt->get_result();
    $posts = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }
    }

    $json_posts = json_encode($posts);
    echo $json_posts;

    // Close the database connection
    mysqli_close($conn);
} else { // posts
    $q = "%".$_GET["q"]."%";

    // Retrieve data from the posts table
    $stmt = $conn->prepare("SELECT * FROM posts WHERE body LIKE ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $a1);
    $a1 = $q;
    $stmt->execute();
    $result = $stmt->get_result();
    $posts = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }
    }

    $json_posts = json_encode($posts);
    echo $json_posts;

    // Close the database connection
    mysqli_close($conn);
}

?>