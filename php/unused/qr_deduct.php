<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$wid    = $_POST["wid"];
	$amount = $_POST["amount"];
	$ref    = "TR" . date('mdyhis', time());
	$isQR   = 1;
	$trans_type = "Transfer";

	$response = array();
    $error    = false;
    $message  = "";

    $stmt_insert = $pdo->prepare("INSERT INTO fc_transfer (refNumber,wid_from,amount,dateCreated,isQR) VALUES (:refNumber,:wid_from,:amount,:dateCreated,:isQR)");
    $stmt_insert->bindParam(":refNumber",$ref,PDO::PARAM_STR);
    $stmt_insert->bindParam(":wid_from",$wid,PDO::PARAM_STR);
    $stmt_insert->bindParam(":amount",$amount,PDO::PARAM_STR);
    $stmt_insert->bindParam(":dateCreated",$global_date,PDO::PARAM_STR);
    $stmt_insert->bindParam(":isQR",$isQR,PDO::PARAM_STR);

    if ($stmt_insert->execute()) {
    	$amount = "-" . $amount;
    	
        $stmt_insert = $pdo->prepare("INSERT INTO fc_amount_history (refNumber,transactionType,amount) VALUES (:refNumber,:transactionType,:amount)");
    	$stmt_insert->bindParam(":refNumber",$ref,PDO::PARAM_STR);
    	$stmt_insert->bindParam(":transactionType",$trans_type,PDO::PARAM_STR);
    	$stmt_insert->bindParam(":amount",$amount,PDO::PARAM_STR);

    	if ($stmt_insert->execute()) {
    		$message = "Transaction has been completed. QR Code has been generated";
    	} else {
    		$error = true;
    		$message = "Something went wrong. Please try again later";
    	}
    } else {
        $error   = true;
        $message = "Error recording transfer";
    }

    $response["error"]   = $error;
    $response["message"] = $message;
    $response["ref"]     = $ref;
    
    echo json_encode($response);
?>