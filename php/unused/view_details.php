<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$walletID = $_POST["walletID"];
	$error    = false;
    $response = array();
    $result   = array();  

    $stmt = $pdo->prepare("
        SELECT
            a.walletID,
            a.firstName,
            a.lastName,
            a.username,
            a.email,
            IFNULL((SELECT mobile FROM fc_approval WHERE walletID = :walletID AND `status` = 'Approved'),'') as mobile,
            IFNULL((SELECT address FROM fc_approval WHERE walletID = :walletID AND `status` = 'Approved'),'') as address
        FROM
            fc_registration a
        WHERE
            a.walletID = :walletID
    ");

    $stmt->bindParam(":walletID",$walletID,PDO::PARAM_STR);

    if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        foreach($rcrd AS $row) {
            $temp = array();
                
            $temp["firstName"]    = $row["firstName"];    
            $temp["lastName"]     = $row["lastName"];
            $temp["username"]     = $row["username"];
            $temp["email"]        = $row["email"];
            $temp["mobile"]       = $row["mobile"];
            $temp["address"]      = $row["address"];
            $temp["userTypeList"] = getUserType();
            
            array_push($result, $temp);
        }
    } else {
        $error = true;
    }

    function getUserType() {
        global $pdo;

        $items = array();

        $stmt = $pdo->prepare("SELECT id,usertype,discount,isDriver FROM fc_usertype WHERE isActive = 1 ORDER BY usertype ASC");

        if ($stmt->execute()) {
            $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            $items = array();

            foreach($rcrd AS $row) {
                $temp = array();
                $temp["id"]       = $row["id"];
                $temp["usertype"] = $row["usertype"];
                $temp["discount"] = $row["discount"];
                $temp["isDriver"] = $row["isDriver"] == 1 ? true : false;
                array_push($items, $temp);
            }
        }

        return $items;
    }


    $response["error"]   = $error;
    $response["result"]  = $result; 
    
    echo json_encode($response);
?>