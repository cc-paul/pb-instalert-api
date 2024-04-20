<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);


	$ref              = $_POST["ref"];
	$locationIDFrom   = $_POST["locationIDFrom"];
	$locationIDTo     = $_POST["locationIDTo"];
	$total            = $_POST["total"];
	$ntotal           = "-" . $total;
	$walletIDSender   = $_POST["walletIDSender"];
	$walletIDReceiver = $_POST["walletIDReceiver"];
	$fin              = 'Fare In';
	$fout             = 'Fare Out';
	
	$error    = false;
    $message  = "";
    $response = array();


    $stmt_insert = $pdo->prepare("INSERT INTO fc_transport_history (ref,walletIDFrom,walletIDTo,idFrom,idTo,total,dateCreated) VALUES (:ref,:walletIDFrom,:walletIDTo,:idFrom,:idTo,:total,:dateCreated)");
    $stmt_insert->bindParam(":ref",$ref,PDO::PARAM_STR);
    $stmt_insert->bindParam(":walletIDFrom",$walletIDSender,PDO::PARAM_STR);
    $stmt_insert->bindParam(":walletIDTo",$walletIDReceiver,PDO::PARAM_STR);
    $stmt_insert->bindParam(":idFrom",$locationIDFrom,PDO::PARAM_STR);
    $stmt_insert->bindParam(":idTo",$locationIDTo,PDO::PARAM_STR);
    $stmt_insert->bindParam(":total",$total,PDO::PARAM_STR);
    $stmt_insert->bindParam(":dateCreated",$global_date,PDO::PARAM_STR);

    if ($stmt_insert->execute()) {
        
    	$stmt_insert = $pdo->prepare("INSERT INTO fc_amount_history (refNumber,transactionType,amount) VALUES (:refNumber,:transactionType,:amount)");
    	$stmt_insert->bindParam(":refNumber",$ref,PDO::PARAM_STR);
    	$stmt_insert->bindParam(":transactionType",$fin,PDO::PARAM_STR);
    	$stmt_insert->bindParam(":amount",$total,PDO::PARAM_STR);
   
    	if ($stmt_insert->execute()) {
        
	    	$stmt_insert = $pdo->prepare("INSERT INTO fc_amount_history (refNumber,transactionType,amount) VALUES (:refNumber,:transactionType,:amount)");
	    	$stmt_insert->bindParam(":refNumber",$ref,PDO::PARAM_STR);
	    	$stmt_insert->bindParam(":transactionType",$fout,PDO::PARAM_STR);
	    	$stmt_insert->bindParam(":amount",$ntotal,PDO::PARAM_STR);
	   
	    	if ($stmt_insert->execute()) {
	        
		    	$error = false;
		    	$message = "Fare has been credited to your account";

		    } else {
		        $error   = true;
		        $message = "Payment details has been saved but theres no deduction happened to the passenger. Please contact support";
		    }

	    } else {
	        $error   = true;
	        $message = "Payment details has been saved but the fare doesnt credited. Please contact support";
	    }

    } else {
        $error   = true;
        $message = "Error saving payment details";
    }


	$response["error"]   = $error;
    $response["message"] = $message;
    
    echo json_encode($response);
?>