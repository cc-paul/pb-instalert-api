<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$id          = $_POST["id"];
	$error       = false;
	$message     = "";
	$response    = array();
	$result      = array();  

	$stmt = $pdo->prepare("
        SELECT
			CONCAT(b.firstName,' ',b.middleName,' ',b.lastName) AS fullName,
			b.hospitalName,
			b.hospitalMobileNumber,
			b.address,
			a.originLatLong,
			a.hospitalLatLong
		FROM
			ar_responses a
		INNER JOIN
			ar_user_registation b
		ON
			a.respondedBy = b.id
		WHERE
			a.userID = :id
		AND
			a.isResponded = 1
		AND
			DATE(a.dateUpdated) = DATE(:date)
		ORDER BY
			IFNULL(a.dateUpdated,'') ASC
		LIMIT
			1
    ");

    $stmt->bindParam(":id",$id,PDO::PARAM_STR);
    $stmt->bindParam(":date",$global_date,PDO::PARAM_STR);

    if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $count = 0;
        
        foreach($rcrd AS $row) {
            $temp = array();
                
            $temp["fullName"]             = $row["fullName"];  
            $temp["hospitalName"]         = $row["hospitalName"];    
            $temp["hospitalMobileNumber"] = $row["hospitalMobileNumber"];
            $temp["address"]              = $row["address"];
            $temp["originLatLong"]        = $row["originLatLong"];
            $temp["hospitalLatLong"]      = $row["hospitalLatLong"];
            
            array_push($result, $temp);
            $count++;
        }
        
        if ($count != 0) {
            $error = false;
        }
    } else {
        $error = true;
        $message = "Error requesting emergency request";
    }


    $response["error"]   = $error;
    $response["message"] = $message;
    $response["result"]  = $result; 
    
    echo json_encode($response);
?>