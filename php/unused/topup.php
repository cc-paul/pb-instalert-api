<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$userID    = $_POST["userID"];
	$refNumber = $_POST["refNumber"];
	$amount    = $_POST["amount"];
	$bankName  = $_POST["bankName"];
	$date      = date_create($global_date);

	$response  = array();
    $error     = false;
    $message   = "";

    $stmt = $pdo->prepare("SELECT * FROM fc_topup WHERE refNumber = :refNumber");
    $stmt->bindParam(":refNumber",$refNumber,PDO::PARAM_STR);

    if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $count = 0;

        foreach($rcrd AS $row) {
            $count++;
        }

        if ($count == 0) {
			$stmt_insert = $pdo->prepare("INSERT INTO fc_topup (userID,refNumber,amount,dateCreated,bankName) VALUES (:userID,:refNumber,:amount,:dateCreated,:bankName)");
			$stmt_insert->bindParam(":userID",$userID,PDO::PARAM_STR);
			$stmt_insert->bindParam(":refNumber",$refNumber,PDO::PARAM_STR);
			$stmt_insert->bindParam(":amount",$amount,PDO::PARAM_STR);
			$stmt_insert->bindParam(":dateCreated",$global_date,PDO::PARAM_STR);
			$stmt_insert->bindParam(":bankName",$bankName,PDO::PARAM_STR);

			if ($stmt_insert->execute()) {
		        $error   = false;
		        $trans_type = "Top Up";

		        $stmt_insert = $pdo->prepare("INSERT INTO fc_amount_history (refNumber,transactionType,amount) VALUES (:refNumber,:transactionType,:amount)");
		        $stmt_insert->bindParam(":refNumber",$refNumber,PDO::PARAM_STR);
		        $stmt_insert->bindParam(":transactionType",$trans_type,PDO::PARAM_STR);
		        $stmt_insert->bindParam(":amount",$amount,PDO::PARAM_STR);

		        if ($stmt_insert->execute()) {
			        $error   = false;
			        $message = "Amount has been added to you wallet";
			    } else {
					$errorm = $stmt_insert->errorInfo();

			    	$error   = true;
			        $message = "Transaction has been recorded but failed to add in you balance. Please contact support";
			    }
		    } else {
		        $error   = true;
		        $message = "Error recording your top up";
		    }
        } else {
        	$error   = true;
		    $message = "Reference number already exist";
        }
    } else {
		$error   = true;
		$message = "Error checking reference number";
    }

    $response["error"]   = $error;
    $response["message"] = $message;
    $response["date"]    = date_format($date,"M d Y, h:i A");
    $response["amount"]  = str_replace(".00","",number_format($amount, 2, '.', ','));
    
    echo json_encode($response);
?>