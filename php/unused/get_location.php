<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$from = $_POST["from"];
	$to = $_POST["to"];
	$error    = false;
    $message  = "";
    $response = array();
    $result   = array(); 

    $stmt = $pdo->prepare("
    	SELECT
			a.id,
			a.location,
			REPLACE(FORMAT(a.price,2),'.00','') AS price
		FROM
			fc_location a
		WHERE
			a.isActive = 1
		AND
			a.location NOT IN (:from,:to)
		ORDER BY
			a.location ASC
    ");

    $stmt->bindParam(":from",$from,PDO::PARAM_STR);
    $stmt->bindParam(":to",$to,PDO::PARAM_STR);

	if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        foreach($rcrd AS $row) {
            $temp = array();
                
            $temp["id"]        = $row["id"];    
            $temp["location"]  = $row["location"];
            $temp["price"]     = $row["price"];
            
            array_push($result, $temp);
        }
    } else {
        $error = true;
        $message = "Error getting location";
    }

    $response["error"]   = $error;
    $response["message"] = $message;
    $response["result"]  = $result; 
    
    echo json_encode($response);
?>