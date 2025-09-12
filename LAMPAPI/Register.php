<?php
    // --- Headers ---
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: PUT, GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    header("Content-Type: application/json; charset=UTF-8");

    $inData = getRequestInfo();

    $conn = new mysqli("localhost", "vogt", "password", "ContactManager");

    if( $conn->connect_error )
    {
        returnWithError($conn->connect_error);
    }
    else
    {
        // Check if user already exists
        $stmt = $conn->prepare("SELECT ID FROM Users WHERE Login=?");
        $stmt->bind_param("s", $inData["login"]);
        $stmt->execute();
        $result = $stmt->get_result();

        if( $result->num_rows > 0 )
        {
            // Username already taken
            returnWithError("User already exists");
        }
        else
        {
            // Insert new user to DB
            $stmt = $conn->prepare("INSERT INTO Users (Login, Password, FirstName, LastName) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $inData["login"], $inData["password"], $inData["firstName"], $inData["lastName"]);
            
            if ($stmt->execute())
            {
                returnWithInfo("Account registered successfully!");
            }
            else
            {
                returnWithError("Error creating account");
            }
        }

        $stmt->close();
        $conn->close();
    }

    function getRequestInfo()
    {
        return json_decode(file_get_contents('php://input'), true);
    }

    function sendResultInfoAsJson( $obj )
    {
        echo $obj;
    }

    function returnWithError( $err )
    {
        $retValue = '{"error":"' . $err . '"}';
        sendResultInfoAsJson( $retValue );
    }

    function returnWithInfo( $msg )
    {
        $retValue = '{"error":"", "message":"' . $msg . '"}';
        sendResultInfoAsJson( $retValue );
    }
?>
