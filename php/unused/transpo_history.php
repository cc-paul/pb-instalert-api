<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$wid 	  = $_POST["wid"];
	$error    = false;
    $message  = "";
    $response = array();
    $result   = array(); 

    $stmt = $pdo->prepare("
		SELECT 
			a.* 
		FROM (
				SELECT
					null AS ref,
					'in' AS flow,
					CONCAT('Payment : ',b.location,' to ',c.location) AS label,
					REPLACE(FORMAT(a.total,2),'.00','') AS amount,
					DATE_FORMAT(a.dateCreated,'%b %d, %Y') AS date,
					TIME_FORMAT(a.dateCreated, '%h:%i %p') AS time,
					a.dateCreated,
					0 AS isPrintable
				FROM	
					fc_transport_history a
				INNER JOIN
					fc_location b 
				ON
					a.idTo = b.id
				INNER JOIN
					fc_location c
				ON
					a.idTo = c.id
				WHERE 
					a.walletIDTo  = :walletID

				UNION ALL
					
				SELECT
					null AS ref,
					'out' AS flow,
					CONCAT('Deduction : ',b.location,' to ',c.location) AS label,
					REPLACE(FORMAT(a.total,2),'.00','') AS amount,
					DATE_FORMAT(a.dateCreated,'%b %d, %Y') AS date,
					TIME_FORMAT(a.dateCreated, '%h:%i %p') AS time,
					a.dateCreated,
					0 AS isPrintable
				FROM	
					fc_transport_history a
				INNER JOIN
					fc_location b 
				ON
					a.idFrom = b.id
				INNER JOIN
					fc_location c
				ON
					a.idTo = c.id
				WHERE 
					a.walletIDFrom  = :walletID
		) a
		ORDER BY
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