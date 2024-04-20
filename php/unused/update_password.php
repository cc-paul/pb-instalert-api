<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$id       = $_POST["id"];
    $password = $_POST["password"];
	$response = array();
    $error    = false;
    $message  = "";

    $stmt_insert = $pdo->prepare("UPDATE fc_registration SET `password` = MD5(:password) WHERE id = :id");
    $stmt_insert->bindParam(":id",$id,PDO::PARAM_STR);
    $stmt_insert->bindParam(":password",$password,PDO::PARAM_STR);

    if ($stmt_insert->execute()) {
        $error   = false;
        $message = "Password has been reset.";
    } else {
        $error   = true;
        $message = "Error changing password";
    }

    $response["error"]   = $error;
    $response["message"] = $message;
    
    echo json_encode($response);
?>