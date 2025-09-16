<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: PUT, GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    header("Content-Type: application/json; charset=UTF-8");

    $inData = getRequestInfo();
    
    $contactId = $inData["contactId"];
    $userId = $inData["userId"];

    $conn = new mysqli("localhost", "root", "", "ContactManager");
    if ($conn->connect_error) 
    {
        returnWithError( $conn->connect_error );
    } 
    else
    {
        $stmt = $conn->prepare("DELETE FROM Contacts WHERE ID = ? AND UserID = ?");
        $stmt->bind_param("ii", $contactId, $userId);
        $stmt->execute();
        
        if( $stmt->affected_rows > 0 )
        {
            returnWithInfo("Contact deleted successfully");
        }
        else
        {
            returnWithError("Failed to delete contact or contact not found");
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
        header('Content-type: application/json');
        echo $obj;
    }
    
    function returnWithError( $err )
    {
        $retValue = '{"error":"' . $err . '"}';
        sendResultInfoAsJson( $retValue );
    }
    
    function returnWithInfo( $msg )
    {
        $retValue = '{"error":"","message":"' . $msg . '"}';
        sendResultInfoAsJson( $retValue );
    }
    
?>
