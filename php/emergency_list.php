<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$userID	  = $_POST["userID"];
	$error    = false;
    $message  = "";
    $response = array();
    $result   = array(); 

    $stmt = $pdo->prepare("
    	SELECT
			a.id,
			a.originLatLong,
			a.hospitalLatLong,
			CONCAT(b.firstName,' ',b.middleName,' ',b.lastName) AS requestor,
			a.dateCreated,
			b.mobileNumber,
			IF(a.isResponded = 0,'Pending',IF(a.respondedBy = a.notifiedUserID,'Responded by You','Responded by Others')) AS `status`
		FROM
			ar_responses a
		INNER JOIN
			ar_user_registation b
		ON
			a.userID = b.id
		WHERE 
			a.notifiedUserID = :userID
		AND
			DATE(a.dateCreated) = DATE(:dateCreated)
		ORDER BY
			a.dateCreated DESC;
    ");

    $stmt->bindParam(":userID",$userID,PDO::PARAM_STR);
    $stmt->bindParam(":dateCreated",$global_date,PDO::PARAM_STR);

	if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        foreach($rcrd AS $row) {
            $temp = array();
                
            $temp["id"]               = $row["id"];    
            $temp["originLatLong"]    = $row["originLatLong"];
            $temp["hospitalLatLong"]  = $row["hospitalLatLong"];
            $temp["requestor"]        = $row["requestor"];
            $temp["dateCreated"]      = time_ago_in_php($row["dateCreated"]);
            $temp["mobileNumber"]     = $row["mobileNumber"];
            $temp["status"]           = $row["status"];

            
            array_push($result, $temp);
        }
    } else {
        $error = true;
        $message = "Error getting emergency history";
    }


    function time_ago_in_php($timestamp){
  
	  date_default_timezone_set("Asia/Manila");         
	  $time_ago        = strtotime($timestamp);
	  $current_time    = time();
	  $time_difference = $current_time - $time_ago;
	  $seconds         = $time_difference;
	  
	  $minutes = round($seconds / 60); // value 60 is seconds  
	  $hours   = round($seconds / 3600); //value 3600 is 60 minutes * 60 sec  
	  $days    = round($seconds / 86400); //86400 = 24 * 60 * 60;  
	  $weeks   = round($seconds / 604800); // 7*24*60*60;  
	  $months  = round($seconds / 2629440); //((365+365+365+365+366)/5/12)*24*60*60  
	  $years   = round($seconds / 31553280); //(365+365+365+365+366)/5 * 24 * 60 * 60
	                
	  if ($seconds <= 60){

	    return "Just Now";

	  } else if ($minutes <= 60){

	    if ($minutes == 1){

	      return "One minute ago";

	    } else {

	      return "$minutes minutes ago";

	    }

	  } else if ($hours <= 24){

	    if ($hours == 1){

	      return "an hour ago";

	    } else {

	      return "$hours hrs ago";

	    }

	  } else if ($days <= 7){

	    if ($days == 1){

	      return "Yesterday";

	    } else {

	      return "$days days ago";

	    }

	  } else if ($weeks <= 4.3){

	    if ($weeks == 1){

	      return "A week ago";

	    } else {

	      return "$weeks weeks ago";

	    }

	  } else if ($months <= 12){

	    if ($months == 1){

	      return "A month ago";

	    } else {

	      return "$months months ago";

	    }

	  } else {
	    
	    if ($years == 1){

	      return "One year ago";

	    } else {

	      return "$years years ago";

	    }
	  }
	}


    $response["error"]   = $error;
    $response["message"] = $message;
    $response["result"]  = $result; 
    
    echo json_encode($response);
?>