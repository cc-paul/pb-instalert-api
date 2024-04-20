<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$email        = $_POST["emailuser"];
    $username     = $_POST["emailuser"];
	$password     = $_POST["password"];
    $fcmKey       = $_POST["fcmKey"];
	$error        = false;
    $message      = "Successfully Loged In";
    $response     = array();
    $result       = array();  

    $stmt = $pdo->prepare("
        SELECT
            id, 
            emailAddress,
            CONCAT(firstName,' ',middleName,' ',lastName) AS fullName,
            isRegularUser
        FROM 
            ar_user_registation 
        WHERE 
            (emailAddress = :email OR username = :username) AND password = MD5(:password) AND fcmKey = :fcmKey
    ");

    $stmt->bindParam(":email",$email,PDO::PARAM_STR);
    $stmt->bindParam(":username",$username,PDO::PARAM_STR);
    $stmt->bindParam(":password",$password,PDO::PARAM_STR);
    $stmt->bindParam(":fcmKey",$fcmKey,PDO::PARAM_STR);


    if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $count = 0;
        
        foreach($rcrd AS $row) {
            $temp = array();
                
            $temp["id"]            = $row["id"];  
            $temp["emailAddress"]  = $row["emailAddress"];    
            $temp["fullName"]      = $row["fullName"];
            $temp["isRegularUser"] = $row["isRegularUser"];
            
            array_push($result, $temp);
            $count++;
        }
        
        if ($count == 0) {
            $error   = true;
            $message = "Account does not exist";
        }
    } else {
        $error = true;
        $message = "Error checking account";
    }


    $response["error"]   = $error;
    $response["message"] = $message;
    $response["result"]  = $result; 
    
    echo json_encode($response);
?>