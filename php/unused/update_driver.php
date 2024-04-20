<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$walletID       = $_POST["walletID"];
    $vehicleName    = $_POST["vehicleName"];
    $plateNumber    = $_POST["plateNumber"];
    $conductorsName = $_POST["conductorsName"];


	$response = array();
    $error    = false;
    $message  = "";

    $stmt_insert = $pdo->prepare("UPDATE fc_registration SET vehicleName=:vehicleName, plateNumber=:plateNumber, conductor=:conductor WHERE walletID=:walletID");
    $stmt_insert->bindParam(":vehicleName",$vehicleName,PDO::PARAM_STR);
    $stmt_insert->bindParam(":plateNumber",$plateNumber,PDO::PARAM_STR);
    $stmt_insert->bindParam(":conductor",$conductorsName,PDO::PARAM_STR);
    $stmt_insert->bindParam(":walletID",$walletID,PDO::PARAM_STR);

    if ($stmt_insert->execute()) {
        $error   = false; 
        $message = "Driver details has been updated";
    } else {
        $error   = true;
        $message = "Error updating driver details";
    }

    $response["error"]   = $error;
    $response["message"] = $message;
    
    echo json_encode($response);
?>