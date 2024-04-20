<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$ref 	  = $_POST["ref"];
	$wid_from = $_POST["wid_from"];
	$wid_to   = $_POST["wid_to"];
	$amount	  = $_POST["amount"];
	$date     = $global_date;

	$error    = false;
    $message  = "";
    $response = array();
    $result   = array(); 

    $stmt = $pdo->prepare("SELECT IFNULL(wid_to,'-') AS receiver FROM fc_transfer WHERE refNumber = :refNumber AND wid_from = :wid_from AND amount = :amount");
    $stmt->bindParam(":refNumber",$ref,PDO::PARAM_STR);
    $stmt->bindParam(":wid_from",$wid_from,PDO::PARAM_STR);
    $stmt->bindParam(":amount",$amount,PDO::PARAM_STR);

    if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $count = 0;
        $receiver = "";

        foreach($rcrd AS $row) {
            $count++;
            $receiver = $row["receiver"];
        }

        if ($count == 0) {
        	$error = true;
    		$message = "QR Details does not exist";
        } else {
        	if ($receiver == "-") {
        		$stmt_insert = $pdo->prepare("UPDATE fc_transfer SET wid_to = :wid_to,dateUpdated = :dateUpdated WHERE refNumber = :refNumber");
			    $stmt_insert->bindParam(":wid_to",$wid_to,PDO::PARAM_STR);
			    $stmt_insert->bindParam(":dateUpdated",$date,PDO::PARAM_STR);
			    $stmt_insert->bindParam(":refNumber",$ref,PDO::PARAM_STR);

			    if ($stmt_insert->execute()) {
			    	$transactionType = "QR Receive";

			        $stmt_insert = $pdo->prepare("INSERT INTO fc_amount_history (refNumber,transactionType,amount) VALUES (:refNumber,:transactionType,:amount)");
			        $stmt_insert->bindParam(":refNumber",$ref,PDO::PARAM_STR);
				    $stmt_insert->bindParam(":transactionType",$transactionType,PDO::PARAM_STR);
				    $stmt_insert->bindParam(":amount",$amount,PDO::PARAM_STR);

				    if ($stmt_insert->execute()) {
				    	$error = false;
				    } else {
			    		$error 	 = true;
    					$message = "QR has been processed but the amount did not reflected. Please contact support";
				    }
			    } else {
			        $error   = true;
			        $message = "Error updating the receiver";
			    }
        	} else {
        		$error = true;
    			$message = "This QR has been received";
        	}
        }
    } else {
    	$error = true;
    	$message = "Unable to check QR. Please try again later";
    }

    $response["error"]    = $error;
    $response["message"]  = $message;
    $response["date"]     = date_format(date_create($date),"M d Y, h:i A");
    $response["amount"]   = str_replace(".00","",number_format($amount, 2, '.', ','));
    $response["wid_to"]   = $wid_to;
    $response["ref"]      = $ref;
    
    echo json_encode($response);
?>