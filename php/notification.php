<?php
	function sendFCM($mess,$id,$userID,$latLong) {
		$url = 'https://fcm.googleapis.com/fcm/send';
		$arrAdditionalData = array('userID' => $userID, 'latLong' => $latLong);

		$fields = array (
		        'to' => $id,
		        'notification' => array (
	                "body" => $mess,
	                "title" => "Installert Notification",
	                "icon" => "https://apps.project4teen.online/ar/images/hospital.png"
		        ),
		        "data" => $arrAdditionalData
		);
		$fields = json_encode ( $fields );
		$headers = array (
		        'Authorization: key=' . "AAAA2dK8W9s:APA91bGkyY-rLmpkmgU96a45Zr2ZAJY4YCqrwwmRk5WwA61JPg590AYu2Uj9nK77WizT2YBdTZwJmo_MzZoVeIJy8K6ORtolI4nj627Gcf8aFGYfwumqddEqJ8wOcnP7FSomwu7WBh8Q",      
		        'Content-Type: application/json'
		);

		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POST, true );
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );

		$result = curl_exec($ch);           
		echo curl_error($ch);
		if ($result === FALSE) {
		   die('Curl failed: ' . curl_error($ch));
		}
		curl_close($ch);
		return $result;
	}
?>