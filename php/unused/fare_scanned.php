<?php
    include 'conn.php';
    $pdo = new PDO($dsn, $user, $passwd);

    $ref      = $_POST["ref"];
    $error    = false;

    $stmt = $pdo->prepare("SELECT * FROM fc_transport_history WHERE ref = :ref");
    $stmt->bindParam(":ref",$ref,PDO::PARAM_STR);

    if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $count = 0;

        foreach($rcrd AS $row) {
            $count++;
        }

        if ($count != 0) {
            $error = false;
        } else {
            $error = true;
        }
    } else {
        $error = true;
    }

    $response["error"] = $error;
    
    echo json_encode($response);
?>