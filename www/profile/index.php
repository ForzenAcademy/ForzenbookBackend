<?php

$servername = "localhost";
$username = "worker";
$password = "swiffcat";
$db = "socialmedia";

// Create connection
$conn = new mysqli($servername, $username, $password, $db);

// Check connection
if ($conn->connect_error) {
    header("HTTP/1.1 500 Internal Server Error");
    die();
}

$headers = getallheaders();
if (!array_key_exists("token", $headers)) {
    header("HTTP/1.1 401 Unauthorized");
    die();
}
$token = getallheaders()["token"];

// If invalid header token, die
$stmt = $conn->prepare("SELECT user_id FROM auth WHERE token = ?");
$stmt->bind_param("s", $a1);
$a1 = $token;
$stmt->execute();
$stmt->store_result();
$accessor_id = null;
$stmt->bind_result($accessor_id);
if ($stmt->fetch()) {
    // user_id is bound
} else {
    header("HTTP/1.1 401 Unauthorized");
    die();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = null;

    // allow an easy path for users to get their own profile
    if (!isset($_GET["id"])) {
        $user_id = $accessor_id;
    } else {
        $user_id = $_GET["id"];
    }

    // make sure the user exists and grab their data
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("s", $a1);
    $a1 = $user_id;
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->num_rows;
    if ($count == 0) {
        header("HTTP/1.1 403 Forbidden");
        die();
    }
    $row = $result->fetch_assoc();

    // Retrieve data from the posts table
    $stmt = $conn->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $a1);
    $a1 = $user_id;
    $stmt->execute();
    $result = $stmt->get_result();
    $posts = array();

    if ($result->num_rows > 0) {
        while ($post_row = $result->fetch_assoc()) {
            $posts[] = $post_row;
        }
    }

    // add posts to the values obtained earlier
    $row["posts"] = $posts;
    if ($user_id == $accessor_id) $row["owner"] = true;
    else $row["owner"] = false;

    echo json_encode($row);

    // Close the database connection
    mysqli_close($conn);
} else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $json = file_get_contents('php://input');
    $_PUT = json_decode($json, true);

    // make sure there is an id
    if (!isset($_PUT["id"])) {
        header("HTTP/1.1 403 Forbidden");
        die();
    }

    // make sure the id matches the accesors id so that no illegal writing by the wrong user is done
    $user_id = $_PUT["id"];
    if ($user_id != $accessor_id) {
        header("HTTP/1.1 403 Forbidden");
        die();
    }

    // if about me doesn't exist, die
    if (!isset($_PUT["aboutMe"])) {
        header("HTTP/1.1 400 Bad Request");
        die();
    }

    $about = $_PUT["aboutMe"];

    // if the about is too long die
    if (strlen($about) > 512) {
        header("HTTP/1.1 403 Bad Request");
        die();
    }

    // update the users about me with their new input
    $stmt = $conn->prepare("UPDATE users SET about_me = ? WHERE user_id = ?");
    $stmt->bind_param("ss", $a1, $a2);
    $a1 = $about;
    $a2 = $user_id;
    $stmt->execute();

    mysqli_close($conn);
} else {
    header("HTTP/1.1 403 Forbidden");
    die();
}
