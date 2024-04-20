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
            IFNULL(`status`,'Not Sent') AS `status`,
            IF(IFNULL(a.firstName,'') = '',username,CONCAT(a.firstName,' ',a.lastName)) AS fullName
        FROM
            fc_registration a
        LEFT JOIN 
            fc_approval b
        ON
            a.walletID = b.walletID AND b.`status` != 'Rejected'
        WHERE
            a.walletID = :walletID
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
            $temp["fullName"]   = $row["fullName"];
            
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