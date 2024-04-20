<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$fcmKey       = $_POST["fcmKey"];
	$isRegistered = false;
    $response     = array();

    $stmt = $pdo->prepare("SELECT * FROM ar_user_registation WHERE fcmKey = :fcmKey");
    $stmt->bindParam(":fcmKey",$fcmKey,PDO::PARAM_STR);

    if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $count = 0;
        
        foreach($rcrd AS $row) {
            $count++;
        }
        
        if ($count != 0) {
            $isRegistered = true;
        }
    } else {
        $isRegistered = true;
    }


    $response["isRegistered"] = $isRegistered;
    
    echo json_encode($response);
?>