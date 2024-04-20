<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$wid_from    = $_POST["wid_from"];
	$wid_to      = "WID" . $_POST["wid_to"];
	$amount	     = $_POST["amount"];
	$ref         = "TR" . date('mdyhis', time());
	$trans_type1 = "Transfer In";
	$trans_type2 = "Transfer Out";
	$date        = $global_date;
	$n_amount    = "-" . $amount;
	$remarks     = $_POST["remarks"];

	$error    = false;
    $message  = "";
    $response = array();
    $result   = array(); 

    $stmt = $pdo->prepare("SELECT * FROM fc_registration WHERE walletID = :walletID");
    $stmt->bindParam(":walletID",$wid_to,PDO::PARAM_STR);

    if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $count = 0;

        foreach($rcrd AS $row) {
            $count++;
        }

        if ($count == 0) {
        	$error = true;
        	$message = "Wallet ID does not exist";
        } else {
        	$stmt_insert = $pdo->prepare("INSERT INTO fc_transfer (refNumber,wid_from,wid_to,amount,dateCreated,message) VALUES (:refNumber,:wid_from,:wid_to,:amount,:dateCreated,:message)");
			$stmt_insert->bindParam(":refNumber",$ref,PDO::PARAM_STR);
			$stmt_insert->bindParam(":wid_from",$wid_from,PDO::PARAM_STR);
			$stmt_insert->bindParam(":wid_to",$wid_to,PDO::PARAM_STR);
			$stmt_insert->bindParam(":amount",$amount,PDO::PARAM_STR);
			$stmt_insert->bindParam(":dateCreated",$date,PDO::PARAM_STR);
			$stmt_insert->bindParam(":message",$remarks,PDO::PARAM_STR);

			if ($stmt_insert->execute()) {
				$stmt_insert = $pdo->prepare("INSERT INTO fc_amount_history (refNumber,transactionType,amount) VALUES (:refNumber,:transactionType1,:amount),(:refNumber,:transactionType2,:n_amount)");
				$stmt_insert->bindParam(":refNumber",$ref,PDO::PARAM_STR);
				$stmt_insert->bindParam(":transactionType1",$trans_type1,PDO::PARAM_STR);
				$stmt_insert->bindParam(":transactionType2",$trans_type2,PDO::PARAM_STR);
				$stmt_insert->bindParam(":amount",$amount,PDO::PARAM_STR);
				$stmt_insert->bindParam(":n_amount",$n_amount,PDO::PARAM_STR);

				if ($stmt_insert->execute()) {
					$error = false;
				} else {
					$error = true;
					$message = "Transaction has been recorded but the amount does not reflected. Please report to support";
				}
			} else {
				$error = true;
				$message = "Error recording transaction.";
			}
        }
    } else {
    	$error   = true;
    	$message = "Error checking receivers Wallet ID"; 
    }

    $response["error"]    = $error;
    $response["message"]  = $message;
    $response["date"]     = date_format(date_create($date),"M d Y, h:i A");
    $response["amount"]   = str_replace(".00","",number_format($amount, 2, '.', ','));
    $response["ref"]      = $ref;

    echo json_encode($response);
?>