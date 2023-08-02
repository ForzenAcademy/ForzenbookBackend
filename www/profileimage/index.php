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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // make sure there is a user id in the post
    if (!isset($_POST["id"])) {
        header("HTTP/1.1 403 Forbidden");
        die();
    }

    // make sure there is an image
    if (!isset($_FILES["profileImage"])) {
        header("HTTP/1.1 403 Forbidden");
        die();
    }

    // make sure the id matches the accesors id so that no illegal writing by the wrong user is done
    $user_id = $_POST["id"];
    if ($user_id != $accessor_id) {
        header("HTTP/1.1 403 Forbidden");
        die();
    }

    $file_prefix = "pp";
    $target_dir = "../upload/";
    $imageFileType = strtolower(pathinfo($_FILES["profileImage"]["name"], PATHINFO_EXTENSION));

    // Check file size
    if ($_FILES["profileImage"]["size"] > 2000000) {
        header("HTTP/1.1 400 Bad Request");
        die();
    }

    // Allow certain file formats
    if (
        $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif"
    ) {
        header("HTTP/1.1 400 Bad Request");
        die();
    }

    $image = $target_dir . $file_prefix . "_" . $user_id . "." . $imageFileType;
    if (move_uploaded_file($_FILES["profileImage"]["tmp_name"], $image)) {
        // success
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        die();
    }
    $image = substr($image, 3);

    $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE user_id = ?");
    $stmt->bind_param("ss", $a1, $a2);
    $a1 = $image;
    $a2 = $user_id;
    $stmt->execute();

    $outJson = array();
    $outJson["image"] = $image;

    echo json_encode($outJson);

    mysqli_close($conn);
} else {
    header("HTTP/1.1 403 Forbidden");
    die();
}
