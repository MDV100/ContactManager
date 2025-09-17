<?php

	$inData = getRequestInfo();
	
	$id = 0;
	$firstName = "";
	$lastName = "";

	error_log("Received data: " . json_encode($inData));
	error_log("Login value: '" . ($inData["login"] ?? 'NULL') . "'");
	error_log("Password value: '" . ($inData["password"] ?? 'NULL') . "'");

	$conn = new mysqli("localhost", "vogt", "password", "ContactManager");
	if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		if( !isset($inData["login"]) || !isset($inData["password"]) || 
		    empty(trim($inData["login"])) || empty(trim($inData["password"])) )
		{
			returnWithError("Umm you forgot to put something...");
		}
		else
		{
			$stmt = $conn->prepare("SELECT ID, firstName, lastName FROM Users WHERE Login=? AND Password=?");
			$stmt->bind_param("ss", $inData["login"], $inData["password"]);
			$stmt->execute();
			$result = $stmt->get_result();

			if( $row = $result->fetch_assoc() )
			{
				returnWithInfo( $row['firstName'], $row['lastName'], $row['ID'] );
			}
			else
			{
				returnWithError("WOMP WOMP: wrong username or password");
			}

			$stmt->close();
		}
		
		$conn->close();
	}
	
	function getRequestInfo()
	{
		$input = file_get_contents('php://input');
		error_log("Raw input: " . $input); // Debug line - remove in production
		$decoded = json_decode($input, true);
		
		if (json_last_error() !== JSON_ERROR_NONE) {
			error_log("JSON decode error: " . json_last_error_msg());
			return [];
		}
		
		return $decoded;
	}

	function sendResultInfoAsJson( $obj )
	{
		header('Content-type: application/json');
		echo $obj;
	}
	
	function returnWithError( $err )
	{
		$retValue = '{"id":0,"firstName":"","lastName":"","error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}
	
	function returnWithInfo( $firstName, $lastName, $id )
	{
		$retValue = '{"id":' . $id . ',"firstName":"' . $firstName . '","lastName":"' . $lastName . '","error":""}';
		sendResultInfoAsJson( $retValue );
	}
	
?>