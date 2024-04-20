<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$userID       = $_POST["userID"];
	$error        = false;
    $message      = "Location has been retrieved";
    $response     = array();
    $result       = array();  

    $stmt = $pdo->prepare("
        SELECT
            a.id,
            a.originLatLong,
            a.hospitalLatLong
        FROM
            ar_responses a 
        WHERE
            a.userID = :userID OR a.notifiedUserID = :notifiedUserID
    ");

    $stmt->bindParam(":userID",$userID,PDO::PARAM_STR);
    $stmt->bindParam(":notifiedUserID",$userID,PDO::PARAM_STR);

    if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $count = 0;
        
        foreach($rcrd AS $row) {
            $temp = array();
                
            $temp["id"]              = $row["id"];     
            $temp["originLatLong"]   = $row["originLatLong"];  
            $temp["hospitalLatLong"] = $row["hospitalLatLong"];    
            
            array_push($result, $temp);
            $count++;
        }
        
        if ($count == 0) {
            $error   = true;
            $message = "Location does not exist";
        }
    } else {
        $error = true;
        $message = "Error retreiving location";
    }


    $response["error"]   = $error;
    $response["message"] = $message;
    $response["result"]  = $result; 
    
    echo json_encode($response);
?>