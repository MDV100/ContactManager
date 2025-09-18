<?php
	$inData = getRequestInfo();
	
	$firstName = $inData["firstName"];
	$lastName  = $inData["lastName"];
	$phone     = $inData["phone"];
	$email     = $inData["email"];
	$userId    = $inData["userId"];
    $contactID = $inData["ID"];

	$conn = new mysqli("localhost", "lampapi", "password", "ContactManager");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
    	$stmt = $conn->prepare("UPDATE Contacts SET FirstName=?, LastName=?, Phone=?, Email=? WHERE ID=? AND UserID=?");
		$stmt->bind_param("ssssss", $firstName, $lastName, $phone, $email, $contactID, $userId);
		$stmt->execute();
		$stmt->close();
		$conn->close();
		returnWithError("");
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