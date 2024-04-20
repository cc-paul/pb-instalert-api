<?php
	include 'conn.php';
    include 'notification.php';
	$pdo = new PDO($dsn, $user, $passwd);

    $usersLatLong;
    $minRange;
    $maxRange;
    $emergencyUserID;    

    if (!empty($_GET['latLong'])) {
        $usersLatLong    = explode(',', $_GET['latLong']);
        $minRange        = 0;
        $maxRange        = 2000;
        $emergencyUserID = 10;
    } else {
        $usersLatLong = explode(',', $_POST["usersLatLong"]);
        $minRange        = $_POST["range"] == 2000 ? 0 : $_POST["range"] - 2000;
        $maxRange        = $_POST["range"];
        $userID          = $_POST["userID"];
        $emergencyUserID = $_POST["id"];
    }


	$error        = false;
    $message      = "Nearby Hospital Detected";
    $response     = array();
    $result       = array();  
    $countNotifiedHospital = 0; 
    $arrCollectedDistance = array();

    if ($minRange == 0) {
        $stmt_insert = $pdo->prepare("DELETE FROM ar_responses WHERE userID = :userID");
        $stmt_insert->bindParam(":userID",$emergencyUserID,PDO::PARAM_STR);
        $stmt_insert->execute();    
    }

    $stmt = $pdo->prepare("
        SELECT
            a.id,
            a.hospitalName,
            a.latLong,
            a.fcmKey
        FROM
            ar_user_registation a
        WHERE
            a.isRegularUser = 0
    ");


    if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        foreach($rcrd AS $row) {
            $temp = array();
                
            $temp["hospitalName"]  = $row["hospitalName"];  
            $temp["latLong"]       = $row["latLong"];
            $arrCurrentHospitalLatlong = explode(',', $row["latLong"]);
            
            array_push($result, $temp);
            $count++;

            computeDistance($usersLatLong[0],$usersLatLong[1],$arrCurrentHospitalLatlong[0],$arrCurrentHospitalLatlong[1],$row["fcmKey"],$row["id"]);
        }
    } else {
        $error = true;
        $message = "Error checking map data";
    }

    function computeDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo,$fcmKey,$userID, $earthRadius = 6371000) {
        global $pdo;
        global $minRange,$maxRange,$countNotifiedHospital,$arrCollectedDistance,$emergencyUserID,$global_date;

        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        $distance = $angle * $earthRadius;

        array_push($arrCollectedDistance,$distance);

        if (($minRange <= $distance) && ($distance <= $maxRange)) {
            $countNotifiedHospital++;
            sendFCM("There is someone seeking medical attention nearby. Click here to open the app and check the details",$fcmKey,$userID,$latitudeFrom . "," . $longitudeFrom);


            $originLatLong   = $latitudeFrom .",". $longitudeFrom;  
            $hospitalLatLong = $latitudeTo .",". $longitudeTo;

            $stmt_insert = $pdo->prepare("INSERT INTO ar_responses (userID,originLatLong,hospitalLatLong,dateCreated,notifiedUserID) VALUES (:userID,:originLatLong,:hospitalLatLong,:dateCreated,:notifiedUserID)");
            $stmt_insert->bindParam(":userID",$emergencyUserID,PDO::PARAM_STR);
            $stmt_insert->bindParam(":originLatLong",$originLatLong,PDO::PARAM_STR);
            $stmt_insert->bindParam(":hospitalLatLong",$hospitalLatLong,PDO::PARAM_STR);
            $stmt_insert->bindParam(":dateCreated",$global_date,PDO::PARAM_STR);
            $stmt_insert->bindParam(":notifiedUserID",$userID,PDO::PARAM_STR);
            $stmt_insert->execute();
        }
    }

    if ($countNotifiedHospital != 0) {
        $message .= " and we notified " . $countNotifiedHospital . " within the range of " . str_replace(".0", "", $maxRange);
    }


    $response["error"]   = $error;
    $response["message"] = $message;
    $response["countNotifiedHospital"] = $countNotifiedHospital;
    $response["usersLatLong"] = $usersLatLong;
    $response["minRange"] = $minRange;
    $response["maxRange"] = $maxRange;
    $response["result"]  = $result; 
    $response["collectedDistance"] = join(",",$arrCollectedDistance);
    
    echo json_encode($response);
?>