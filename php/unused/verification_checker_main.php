<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$wid 	  = $_POST["wid"];
	$error    = false;
    $message  = "";
    $response = array();
    $result   = array();  

	$stmt = $pdo->prepare("
        SELECT
            a.isVerified,
            a.pin,
            IFNULL(`status`,'Not Sent') AS `status`
        FROM
            fc_registration a
        LEFT JOIN 
            fc_approval b
        ON
            a.walletID = b.walletID
        WHERE
            a.walletID = :walletID
        GROUP BY
            b.id
        ORDER BY
            b.id DESC
        LIMIT 1;
    ");
	$stmt->bindParam(":walletID",$wid,PDO::PARAM_STR);

	if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        foreach($rcrd AS $row) {
            $temp = array();

            $temp["isVerified"] = $row["isVerified"];
            $temp["pin"]        = $row["pin"];
            $temp["status"]     = $row["status"];
            
            array_push($result, $temp);
        }
    } else {
    	$error = true;
    	$message = "Something went wrong checking account. Please try again later.";
    }

    $response["error"]   = $error;
    $response["message"] = $message;
    $response["result"]  = $result; 
    
    echo json_encode($response);
?>