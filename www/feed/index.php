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

// Retrieve data from the posts table
$sql = "SELECT * FROM posts ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

// Check if there are any results
if (mysqli_num_rows($result) > 0) {
    // Create an array to store the result data
    $output = array();

    // Fetch the data from the result object and store it in the array
    while($row = mysqli_fetch_assoc($result)) {
        $output[] = $row;
    }

    // Encode the array as a JSON string
    $json_output = json_encode($output);

    // Output the JSON string
    echo $json_output;
} else {
    echo "[]";
}

// Close the database connection
mysqli_close($conn);
?>