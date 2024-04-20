<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$wid 	  = $_POST["wid"];
	$error    = false;
    $message  = "";
    $response = array();
    $result   = array(); 

    $stmt = $pdo->prepare("
    	SELECT a.* FROM (	
			SELECT
				null AS ref,
				'out' AS flow,
				'Sent PHP' AS label,
				REPLACE(FORMAT(a.amount,2),'.00','') AS amount,
				DATE_FORMAT(a.dateCreated,'%b %d, %Y') AS date,
				TIME_FORMAT(a.dateCreated, '%h:%i %p') AS time,
				a.dateCreated,
				0 AS isPrintable
			FROM
				fc_cashout a
			INNER JOIN
				fc_registration b
			ON
				a.userID = b.id
			WHERE
				b.walletID = :walletID
				
			UNION ALL

			SELECT
				a.refNumber AS ref,
				'out' AS flow,
				'Sent PHP' AS label,
				REPLACE(FORMAT(a.amount,2),'.00','') AS amount,
				DATE_FORMAT(a.dateCreated,'%b %d, %Y') AS date,
				TIME_FORMAT(a.dateCreated, '%h:%i %p') AS time,
				a.dateCreated,
				IF(a.isQR = 1,1,0) AS isPrintable
			FROM
				fc_transfer a
			INNER JOIN
				fc_registration b
			ON
				a.wid_from = b.walletID
			WHERE
				b.walletID = :walletID
				
			UNION ALL
			
			SELECT
				null AS ref,
				'in' AS flow,
				'Received PHP' AS label,
				REPLACE(FORMAT(a.amount,2),'.00','') AS amount,
				DATE_FORMAT(a.dateCreated,'%b %d, %Y') AS date,
				TIME_FORMAT(a.dateCreated, '%h:%i %p') AS time,
				a.dateCreated,
				0 AS isPrintable
			FROM
				fc_topup a
			INNER JOIN
				fc_registration b
			ON
				a.userID = b.id
			WHERE
				b.walletID = :walletID
				
			UNION ALL
			
			SELECT
				null AS ref,
				'in' AS flow,
				'Received PHP' AS label,
				REPLACE(FORMAT(a.amount,2),'.00','') AS amount,
				DATE_FORMAT(a.dateCreated,'%b %d, %Y') AS date,
				TIME_FORMAT(a.dateCreated, '%h:%i %p') AS time,
				a.dateCreated,
				0 AS isPrintable
			FROM
				fc_transfer a
			INNER JOIN
				fc_registration b
			ON
				IFNULL(a.wid_to,'') = b.walletID
			WHERE
				b.walletID = :walletID
		) a
		ORDER BY
			a.flow ASC,
			a.dateCreated DESC
    ");

    $stmt->bindParam(":walletID",$wid,PDO::PARAM_STR);

	if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        foreach($rcrd AS $row) {
            $temp = array();
                
            $temp["flow"]        = $row["flow"];    
            $temp["label"]       = $row["label"];
            $temp["amount"]      = $row["amount"];
            $temp["date"]        = $row["date"];
            $temp["time"]        = $row["time"];
            $temp["dateCreated"] = $row["dateCreated"];
            $temp["isPrintable"] = $row["isPrintable"];
            
            array_push($result, $temp);
        }
    } else {
        $error = true;
        $message = "Error getting account history";
    }

    $response["error"]   = $error;
    $response["message"] = $message;
    $response["result"]  = $result; 
    
    echo json_encode($response);
?>