
<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$servername = "localhost";
$username = "worker";
$password = "swiffcat";
$db = "socialmedia";

// If not GET or POST, 403
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // If email not present in query string, 403
    if (!isset($_GET["email"])) {
        header("HTTP/1.1 403 Forbidden");
        die();
    }

    // Create connection
    $conn = new mysqli($servername, $username, $password, $db);

    // Check connection
    if ($conn->connect_error) {
        header("HTTP/1.1 500 Internal Server Error");
        die();
    }

    // If email not registered, die
    $email = $_GET["email"];
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $a1);
    $a1 = $email;
    $stmt->execute();
    $stmt->store_result();
    $count = $stmt->num_rows;
    if ($count === 0) die();

    // Make a key and store in DB
    $code = random_int(100000, 999999);

    $stmt = $conn->prepare("REPLACE INTO codes (email, code) VALUES (?,?)");
    $stmt->bind_param("ss", $a1, $a2);
    $a1 = $email;
    $a2 = $code;
    $stmt->execute();

    // Send email to user's email with the key
    $to = $email;
    $subject = "Forzen Academy Login Code";
    $txt = "Here is your login code: " . $code;
    $headers = "From: forzenacademy@gmail.com";
    $sent = mail($to, $subject, $txt, $headers);
    if (!$sent) {
        header("HTTP/1.1 503 Internal Server Error");
    }
    // Return 200
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // If code or email not present in post, 403
    if (!isset($_POST["code"]) || !isset($_POST["email"])) {
        header("HTTP/1.1 403 Forbidden");
        die();
    }

    // Create connection
    $conn = new mysqli($servername, $username, $password, $db);

    // Check connection
    if ($conn->connect_error) {
        header("HTTP/1.1 501 Internal Server Error");
        die();
    }

    // If check if codes match
    $code = $_POST["code"];
    $email = $_POST["email"];
    $stmt = $conn->prepare("SELECT code FROM codes WHERE email = ?");
    $stmt->bind_param("s", $a1);
    $a1 = $email;
    $stmt->execute();
    $result = $stmt->get_result();
    $match = False;
    foreach ($result as $row) {
        if ($row['code'] == $code) $match = True;
    }
    if ($match) {
        // Generate "unique" token
        $length = 64;
        $chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $auth = "";

        for ($i = 0; $i < $length; $i++) {
            $auth .= $chars[mt_rand(0, strlen($chars) - 1)];
        }

        // Get the user_id
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $a1);
        $a1 = $email;
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

        // Put in DB
        $stmt = $conn->prepare("REPLACE INTO auth (user_id, email, token) VALUES (?,?,?)");
        $stmt->bind_param("sss", $a0, $a1, $a2);
        $a0 = $user_id;
        $a1 = $email;
        $a2 = $auth;
        $stmt->execute();

        // Erase login code
        $stmt = $conn->prepare("DELETE FROM codes WHERE email = ?");
        $stmt->bind_param("s", $a1);
        $a1 = $email;
        $stmt->execute();

        echo "{\"token\":\"" . $auth . "\"}";
    } else {
        header("HTTP/1.1 401 Unauthorized, You gave:" . $code);
        die();
    }
} else {
    header("HTTP/1.1 403 Forbidden");
    die();
}
?>
