<?php

$token = md5('unique_salt' . $_POST['timestamp']);

if (!empty($_FILES) && $_POST['token'] == $token) {
    
    // IIS搭配PHP環境下$_SERVER['DOCUMENT_ROOT']是錯誤的，應該從$_SERVER['SCRIPT_FILENAME']推算回來
    $uploadify_dir = dirname($_SERVER['SCRIPT_FILENAME']);
    $tmp_folder = '../webdata/tmp';
    
    $time = round((microtime(true) * 1000));
    $fileParts  = pathinfo($_FILES['Filedata']['name']);
    $fileName = $time . '.' . $fileParts['extension'];
    $tempFile = $_FILES['Filedata']['tmp_name'];
    $targetPath = $uploadify_dir . '/' . $tmp_folder . '/';
    
    $targetFile =  $targetPath . $fileName;

    move_uploaded_file($tempFile, $targetFile);
    echo $fileName;
    //echo '1';
}
?>