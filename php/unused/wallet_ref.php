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
			SUM(a.amount) AS amount,
			(SELECT CONCAT(IFNULL(vehicleName,'-'),'~:',IFNULL(plateNumber,'-'),'~:',IFNULL(conductor,'-')) FROM fc_registration WHERE walletID = :walletID) AS driverDetails
		FROM (
			SELECT
				a.amount
			FROM
				fc_amount_history a
			INNER JOIN
				fc_topup b
			ON
				a.refNumber = b.refNumber AND a.transactionType = 'Top Up'
			INNER JOIN
				fc_registration c
			ON
				b.userID = c.id
			AND
				c.walletID = :walletID

			UNION ALL 

			SELECT
				a.amount
			FROM
				fc_amount_history a
			INNER JOIN
				fc_transfer b
			ON
				a.refNumber = b.refNumber AND a.transactionType = 'Transfer'
			INNER JOIN
				fc_registration c
			ON
				b.wid_from = c.walletid
			AND
				c.walletID = :walletID

			UNION ALL

			SELECT
				a.amount
			FROM
				fc_amount_history a
			INNER JOIN
				fc_transfer b
			ON
				a.refNumber = b.refNumber AND a.transactionType = 'QR Receive'
			INNER JOIN
				fc_registration c
			ON
				b.wid_to = c.walletid
			AND
				c.walletID = :walletID

			UNION ALL

			SELECT
				a.amount
			FROM
				fc_amount_history a
			INNER JOIN
				fc_transfer b
			ON
				a.refNumber = b.refNumber AND a.transactionType = 'Transfer In'
			INNER JOIN
				fc_registration c
			ON
				b.wid_to = c.walletid
			AND
				c.walletID = :walletID

			UNION ALL

			SELECT
				a.amount
			FROM
				fc_amount_history a
			INNER JOIN
				fc_transfer b
			ON
				a.refNumber = b.refNumber AND a.transactionType = 'Transfer Out'
			INNER JOIN
				fc_registration c
			ON
				b.wid_from = c.walletid
			AND
				c.walletID = :walletID

			UNION ALL

			SELECT
				a.amount
			FROM
				fc_amount_history a
			INNER JOIN
				fc_cashout b
			ON
				a.refNumber = b.refNumber AND a.transactionType = 'Cash Out'
			INNER JOIN
				fc_registration c
			ON
				b.userID = c.id
			AND
				c.walletID = :walletID

			UNION ALL

			SELECT
				a.amount
			FROM
				fc_amount_history a
			INNER JOIN
				fc_transport_history b
			ON
				a.refNumber = b.ref AND a.transactionType = 'Fare In'
			WHERE
				b.walletIDTo = :walletID

			UNION ALL

			SELECT
				a.amount
			FROM
				fc_amount_history a
			INNER JOIN
				fc_transport_history b
			ON
				a.refNumber = b.ref AND a.transactionType = 'Fare Out'
			WHERE
				b.walletIDFrom = :walletID
		) a
	");
	$stmt->bindParam(":walletID",$wid,PDO::PARAM_STR);

	if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        $temp = array();
        $amount = 0;
        $driverDetails = "-";

        foreach($rcrd AS $row) {
            $amount += $row["amount"];
            $driverDetails = $row["driverDetails"];
        }

		$temp["amount"] = str_replace(".00","",number_format($amount, 2, '.', ','));
		$temp["driverDetails"] = $driverDetails;
        array_push($result, $temp);
    } else {
    	$error = true;
    	$message = "Something went wrong while we retrieve your balance. Please try again later";
    }

    $response["error"]   = $error;
    $response["message"] = $message;
    $response["result"]  = $result; 
    
    echo json_encode($response);
?>