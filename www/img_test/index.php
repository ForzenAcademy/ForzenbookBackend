<!DOCTYPE html>
<html>

<body>

    <form action="upload.php" method="post" enctype="multipart/form-data">
        Select image to upload:
        <input type="file" name="fileToUpload" id="fileToUpload">
        <input type="submit" value="Upload Image" name="submit">
    </form>

    <?php
    $dir = "upload/";
    $allowed_extensions = array("jpg", "jpeg", "png", "gif");

    if (is_dir($dir)) {
        $files = scandir($dir);

        foreach ($files as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (in_array($ext, $allowed_extensions)) {
                echo "<img width='100' src='" . $dir . $file . "' alt='" . $file . "'>";
                echo "<br>";
            }
        }
    }
    ?>


</body>

</html>