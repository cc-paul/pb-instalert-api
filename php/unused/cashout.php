<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$walletID  = $_POST["walletID"];
	$amount    = $_POST["amount"];
	$refNumber = "CO" . date('mdyhis', time());
	$date      = date_create($global_date);

	$response  = array();
    $error     = false;
    $message   = "";

    $stmt_insert = $pdo->prepare("INSERT INTO fc_cashout (userID,refNumber,amount,dateCreated) VALUES ((SELECT id FROM fc_registration WHERE walletID = :walletID),:refNumber,:amount,:dateCreated)");
	$stmt_insert->bindParam(":walletID",$walletID,PDO::PARAM_STR);
	$stmt_insert->bindParam(":refNumber",$refNumber,PDO::PARAM_STR);
	$stmt_insert->bindParam(":amount",$amount,PDO::PARAM_STR);
	$stmt_insert->bindParam(":dateCreated",$global_date,PDO::PARAM_STR);

	if ($stmt_insert->execute()) {
        $error = false;
        $trans_type = "Cash Out";
        $amount = "-" . $amount;

        $stmt_insert = $pdo->prepare("INSERT INTO fc_amount_history (refNumber,transactionType,amount) VALUES (:refNumber,:transactionType,:amount)");
        $stmt_insert->bindParam(":refNumber",$refNumber,PDO::PARAM_STR);
        $stmt_insert->bindParam(":transactionType",$trans_type,PDO::PARAM_STR);
        $stmt_insert->bindParam(":amount",$amount,PDO::PARAM_STR);

        if ($stmt_insert->execute()) {
        	$error = false;
        } else {
        	$error = true;
        	$message = "Cash out has been recorded but not deducted. Please report to support";
        }
    } else {
        $error   = true;
        $message = "Error recording your cash out";
    }

    $response["error"]   = $error;
    $response["message"] = $message;
    $response["ref"]     = $refNumber;
    $response["date"]    = date_format($date,"M d Y, h:i A");
    $response["amount"]  = str_replace(".00","",number_format($amount, 2, '.', ','));
    
    echo json_encode($response);
?>
