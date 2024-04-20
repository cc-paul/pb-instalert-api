<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$walletID = $_POST["walletID"];
	$fName    = $_POST["fName"];    
	$lName    = $_POST["lName"];
	$mobile   = $_POST["mobile"];
	$address  = $_POST["address"];
    $tid      = "TID" . date('mdy-his', time());
	$query    = str_replace('tid',$tid,$_POST["query"]);

	$response  = array();
    $error     = false;
    $message   = "";

    $stmt_insert = $pdo->prepare("INSERT INTO fc_approval (walletID,fName,lName,mobile,address,dateCreated,tid) VALUES (:walletID,:fName,:lName,:mobile,:address,:dateCreated,:tid)");
    $stmt_insert->bindParam(":walletID",$walletID,PDO::PARAM_STR);
    $stmt_insert->bindParam(":fName",$fName,PDO::PARAM_STR);
    $stmt_insert->bindParam(":lName",$lName,PDO::PARAM_STR);
    $stmt_insert->bindParam(":mobile",$mobile,PDO::PARAM_STR);
    $stmt_insert->bindParam(":address",$address,PDO::PARAM_STR);
    $stmt_insert->bindParam(":dateCreated",$global_date,PDO::PARAM_STR);
    $stmt_insert->bindParam(":tid",$tid,PDO::PARAM_STR);

    if ($stmt_insert->execute()) {
    	$stmt_insert = $pdo->prepare("INSERT INTO fc_approval_item (walletID,imgURL,tid) VALUES ". $query);
    	if ($stmt_insert->execute()){
			$error   = false;
			$message = "Info and Documents has been sent. Kindly wait for the approval. Usually it takes 2 to 3 days";
		} else {
			$error   = true;
			$message = "Info has been saved but the images failed to upload. Kindly contact support";
		}

    } else {
    	$error   = true;
        $message = "Error recording your approval details";
    }

    $response["error"]   = $error;
    $response["message"] = $message;
    echo json_encode($response);
?>