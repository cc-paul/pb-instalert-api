<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$isRegularUser        = $_POST["isRegularUser"];
	$firstName            = $_POST["firstName"];
	$middleName           = $_POST["middleName"];
	$lastName             = $_POST["lastName"];
	$mobileNumber         = $_POST["mobileNumber"];
	$username             = $_POST["username"];
	$emailAddress         = $_POST["emailAddress"];
	$password             = $_POST["password"];
	$hospitalName         = $_POST["hospitalName"];
	$address              = $_POST["address"];
	$hospitalMobileNumber = $_POST["hospitalMobileNumber"];
	$latLong              = $_POST["latLong"];
    $fcmKey               = $_POST["fcmKey"];

	$response = array();
    $result   = array();
    $error    = false;
    $message  = "";

    $stmt_find = $pdo->prepare("SELECT * FROM ar_user_registation WHERE emailAddress = :emailAddress");
    $stmt_find->bindParam(":emailAddress",$emailAddress,PDO::PARAM_STR);

    if ($stmt_find->execute()) {
        $row = $stmt_find->fetch(PDO::FETCH_ASSOC);
        $stmt_find->closeCursor();

        if ($row) {
            $error = true;
            $message = "Email already exist";
        } else {
            $stmt_find = $pdo->prepare("SELECT * FROM ar_user_registation WHERE username = :username");
            $stmt_find->bindParam(":username",$username,PDO::PARAM_STR);

            if ($stmt_find->execute()) {
                $row = $stmt_find->fetch(PDO::FETCH_ASSOC);
                $stmt_find->closeCursor();

                if ($row) {
                    $error = true;
                    $message = "Username already exist";
                } else {
                    $stmt_insert = $pdo->prepare("INSERT INTO ar_user_registation 
                    	(isRegularUser,firstName,middleName,lastName,mobileNumber,username,emailAddress,password,hospitalName,address,hospitalMobileNumber,latLong,dateCreated,fcmKey) 
                    	VALUES 
                    	(:isRegularUser,:firstName,:middleName,:lastName,:mobileNumber,:username,:emailAddress,MD5(:password),:hospitalName,:address,:hospitalMobileNumber,:latLong,:dateCreated,:fcmKey) ");
                    $stmt_insert->bindParam(":isRegularUser",$isRegularUser,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":firstName",$firstName,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":middleName",$middleName,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":lastName",$lastName,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":mobileNumber",$mobileNumber,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":username",$username,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":emailAddress",$emailAddress,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":password",$password,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":hospitalName",$hospitalName,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":address",$address,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":hospitalMobileNumber",$hospitalMobileNumber,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":latLong",$latLong,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":dateCreated",$global_date,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":fcmKey",$fcmKey,PDO::PARAM_STR);


                    if ($stmt_insert->execute()) {
                        $error   = false;
                        $message = "Account has been saved. You can now login your account";
                    } else {
                        $error   = true;
                        $message = "Error saving account details";
                    }
                }
            } else {
                $error = true;
                $message = "Error checking account";
            }
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



















