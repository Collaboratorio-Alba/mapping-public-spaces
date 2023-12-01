<?php

require __DIR__ . "/vendor/Uploader.php";
require __DIR__ . "/vendor/Image.php";

$image = new CoffeeCode\Uploader\Image("images", "places", false); //No year and month folders

if ($_FILES) {
    try {
        $upload = $image->upload($_FILES['image'], $_POST['filename']);
        $data = [ 'name' => $upload];
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);

    } catch (Exception $e) {
        echo "<p>(!) {$e->getMessage()}</p>";
    }
}
