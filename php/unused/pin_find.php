<?php
    include 'conn.php';
    $pdo = new PDO($dsn, $user, $passwd);

    $wid      = $_POST["wid"];
    $pin      = $_POST["pin"];
    $error    = false;
    $message  = "";
    $response = array();
    $result   = array();  

    $stmt = $pdo->prepare("SELECT * FROM fc_registration WHERE walletID = :walletID AND pin = MD5(:pin)");
    $stmt->bindParam(":walletID",$wid,PDO::PARAM_STR);
    $stmt->bindParam(":pin",$pin,PDO::PARAM_STR);

    if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $count = 0;

        foreach($rcrd AS $row) {
            $count++;
        }

        if ($count == 0) {
            $error   = true;
            $message = "Incorrect PIN";
        } else {
            $error   = false;
            $message = "Welcome to Filicash Wallet";
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