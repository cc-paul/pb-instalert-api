<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$id       = $_POST["rowID"];
	$respondedBy = $_POST["respondedBy"];
	$error    = false;
    $message  = "";
    $response = array();

    /* Check 1st if the data still exist */
    $stmt = $pdo->prepare("SELECT * FROM ar_responses WHERE id=:id");
    $stmt->bindParam(":id",$id,PDO::PARAM_STR);

    if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $count = 0;
        
        foreach($rcrd AS $row) {
            $count++;
        }
        
        if ($count == 0) {
            $error   = true;
            $message = "Request does not exist anymore";
        } else {
        	/* Check if the request has been responded */
        	$stmt = $pdo->prepare("SELECT * FROM ar_responses WHERE userID IN (SELECT userID FROM ar_responses WHERE id=:id) AND isResponded = 1");
		    $stmt->bindParam(":id",$id,PDO::PARAM_STR);

		    if ($stmt->execute()) {
		        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
		        $stmt->closeCursor();
		        $count = 0;
		        
		        foreach($rcrd AS $row) {
		            $count++;
		        }
		        
		        if ($count == 0) {
		        	$stmt_insert = $pdo->prepare("UPDATE ar_responses SET isResponded = 1,respondedBy=:respondedBy,dateUpdated=:dateUpdated WHERE id=:id");
					$stmt_insert->bindParam(":id",$id,PDO::PARAM_STR);
					$stmt_insert->bindParam(":dateUpdated",$global_date,PDO::PARAM_STR);
					$stmt_insert->bindParam(":respondedBy",$respondedBy,PDO::PARAM_STR);

					if ($stmt_insert->execute()) {
						$error   = false;
					   	$message = "Thanks for your response. We will inform them right away";
					} else {
					    $error   = true;
					    $message = "Error responding to incident";
					}
		        } else {
		        	/* Check if the request has been responded by you or someone*/
		        	$stmt = $pdo->prepare("SELECT IFNULL(respondedBy,'') AS respondedBy,notifiedUserID FROM ar_responses WHERE id=:id");
				    $stmt->bindParam(":id",$id,PDO::PARAM_STR);
				 

				    if ($stmt->execute()) {
				        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
				        $stmt->closeCursor();
				        $error = false;
				        
				        foreach($rcrd AS $row) {
				            if ($row["respondedBy"] == $row["notifiedUserID"]) {
				            	$message = "Thanks for your alertness. We already informed them that you responded to their medical request";
				            } else {
				            	$message = "Thanks for your alertness. Someone already responded to this request and its on the way";
				            }
				        }
				    } else {
				        $error = true;
				        $message = "Error checking if you or others responded to this request. Please contact support";
				    }
		        }
		    } else {
		        $error = true;
		        $message = "Error checking if this request is already responded";
		    }
        }
    } else {
        $error = true;
        $message = "Oops something went wrong. Kindly report this to support";
    }

    $response["error"]   = $error;
    $response["message"] = $message;
    
    echo json_encode($response);
?>