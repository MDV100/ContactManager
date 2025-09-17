<?php
    // CORS & JSON headers (kept as-is)
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: PUT, GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    header("Content-Type: application/json; charset=UTF-8");

    // Early exit for CORS preflight so the frontend doesn't hang
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    $inData = getRequestInfo();
    if (!is_array($inData)) {
        returnWithError("INVALID_JSON: Body must be valid JSON."); exit;
    }
    if (!isset($inData["login"], $inData["password"])) {
        returnWithError("VALIDATION_ERROR: 'login' and 'password' are required."); exit;
    }

    $id = 0;
    $firstName = "";
    $lastName = "";

    $conn = @new mysqli("localhost", "vogt", "password", "ContactManager");
    if ($conn->connect_error) {
        http_response_code(503);
        returnWithError("DB_CONNECT_ERROR: " . $conn->connect_error); exit;
    }

    // Force UTF-8 (optional but nice)
    if (!$conn->set_charset("utf8mb4")) {
        // Not fatal, but let the client know
        // (Do not exit; continue.)
        // You can also log this server-side if you want.
    }

    // Prepare statement
    $stmt = $conn->prepare("SELECT ID AS id, FirstName AS firstName, LastName AS lastName FROM Users WHERE Login=? AND Password=?");
    if (!$stmt) {
        http_response_code(500);
        returnWithError("DB_PREPARE_FAILED: " . $conn->error); 
        $conn->close(); 
        exit;
    }

    // Bind params
    if (!$stmt->bind_param("ss", $inData["login"], $inData["password"])) {
        http_response_code(500);
        returnWithError("DB_BIND_FAILED: " . $stmt->error); 
        $stmt->close(); $conn->close(); 
        exit;
    }

    // Execute
    if (!$stmt->execute()) {
        http_response_code(500);
        returnWithError("DB_EXECUTE_FAILED: " . $stmt->error); 
        $stmt->close(); $conn->close(); 
        exit;
    }

    // Fetch result (covers mysqlnd not installed)
    $result = @$stmt->get_result();
    if ($result === false) {
        // Fall back message that points at common cause
        http_response_code(500);
        returnWithError("DB_RESULT_FAILED: get_result() unavailable (is mysqlnd installed?) or query error."); 
        $stmt->close(); $conn->close(); 
        exit;
    }

    if ($row = $result->fetch_assoc()) {
        // Use the aliased keys
        returnWithInfo($row['firstName'], $row['lastName'], (int)$row['id']);
    } else {
        http_response_code(401);
        returnWithError("NO_RECORDS: Invalid login or password.");
    }

    $stmt->close();
    $conn->close();

    // ---- Helpers (kept, with small safety tweaks) ----
    function getRequestInfo()
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') return null;
        return json_decode($raw, true);
    }

    function sendResultInfoAsJson($obj)
    {
        echo $obj;
    }

    function returnWithError($err)
    {
        $retValue = json_encode([
            "id" => 0,
            "firstName" => "",
            "lastName" => "",
            "error" => $err
        ], JSON_UNESCAPED_SLASHES);
        sendResultInfoAsJson($retValue);
    }

    function returnWithInfo($firstName, $lastName, $id)
    {
        $retValue = json_encode([
            "id" => $id,
            "firstName" => $firstName,
            "lastName" => $lastName,
            "error" => ""
        ], JSON_UNESCAPED_SLASHES);
        sendResultInfoAsJson($retValue);
    }
?>