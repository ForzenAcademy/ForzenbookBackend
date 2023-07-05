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

$target_dir = "./upload/";
$target_file = $target_dir . basename(generateRandomString());
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($_FILES["fileToUpload"]["name"],PATHINFO_EXTENSION));

// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) {
  $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
  if($check !== false) {
  } else {
    header("HTTP/1.1 400 Bad Request");
    die("{\"reason\":\"not an image\"}");
  }
}

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

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
  header("HTTP/1.1 400 Bad Request");
  die();
} else {
  if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], 
$target_file.".".$imageFileType)) {
    echo "{\"reason\":\"success\"}";
  } else {
    header("HTTP/1.1 500 Internal Server Error");
    die();
  }
}
?>

<br><br>
<a href="index.php">Back</a>
