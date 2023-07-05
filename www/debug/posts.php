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

// Retrieve data from the posts table
$sql = "SELECT user_id, body, post_type FROM posts";
$result = mysqli_query($conn, $sql);
?>

<html>
<head>
<style>
.styled-table {
    border-collapse: collapse;
    margin: 25px 0;
    font-size: 0.9em;
    font-family: sans-serif;
    min-width: 400px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
}
.styled-table thead tr {
    background-color: #009879;
    color: #ffffff;
    text-align: left;
}
.styled-table th,
.styled-table td {
    padding: 12px 15px;
}
.styled-table tbody tr {
    border-bottom: 1px solid #dddddd;
}

.styled-table tbody tr:nth-of-type(even) {
    background-color: #f3f3f3;
}

.styled-table tbody tr:last-of-type {
    border-bottom: 2px solid #009879;
}
</style>
</head>
<body>

<?php
// Print out the data in an HTML table
if (mysqli_num_rows($result) > 0) {
    echo "<table class='styled-table'>";
    echo "<tr><th>User ID</th><th>Body</th><th>Post Type</th></tr>";
    while($row = mysqli_fetch_assoc($result)) {
        $img = "";
        if($row["post_type"] == "image"){
            $img = "<br><img width='100' src='../".$row["body"]."'/>";
        }
        echo "<tr><td>".$row["user_id"]."</td><td>".$row["body"].$img."</td><td>".$row["post_type"]."</td></tr>";
    }
    echo "</table>";
} else {
    echo "No posts found";
}

// Close the database connection
mysqli_close($conn);
?>
</body>
</html>
