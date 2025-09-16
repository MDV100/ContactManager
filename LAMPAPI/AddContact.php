<?php
	$inData = getRequestInfo();
	
	$firstName = $inData["firstName"];
	$lastName  = $inData["lastName"];
	$phone     = $inData["phone"];
	$email     = $inData["email"];
	$userId    = $inData["userId"];

	$conn = new mysqli("localhost", "vogt", "password", "ContactManager");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
		$checkStmt = $conn->prepare("SELECT ID FROM Contacts WHERE UserID=? AND Phone=?");
		$checkStmt->bind_param("ss", $userId, $phone);
		$checkStmt->execute();
		$result = $checkStmt->get_result();
		$checkStmt->close();

		
		if ($result->num_rows > 0) {
			$conn->close();
    		returnWithError("Phone number already exists for this user.");
		}
		else{
			$stmt = $conn->prepare("INSERT INTO Contacts (UserID, FirstName, LastName, Phone, Email) VALUES (?, ?, ?, ?, ?)");
			$stmt->bind_param("sssss", $userId, $firstName, $lastName, $phone, $email);
			$stmt->execute();
			$stmt->close();
			$conn->close();
			returnWithError("");
		}
	}

	function getRequestInfo()
	{
		return json_decode(file_get_contents('php://input'), true);
	}

	function sendResultInfoAsJson( $obj )
	{
		header('Content-type: application/json');
		echo $obj;
	}
	
	function returnWithError( $err )
	{
		$retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}
	
?>