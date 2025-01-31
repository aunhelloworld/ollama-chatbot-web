<?php
header("Content-Type: application/json");

// recieve from AJAX
$data = json_decode(file_get_contents("php://input"), true);
$prompt = $data["message"] ?? "";

// check msg
if (!$prompt) {
    echo json_encode(["response" => "Please type your message!"]);
    exit;
}

// Settings Ollama API
$url = "http://127.0.0.1:11434/api/generate";
$requestData = [
    "model" => "llama3.2",
    "prompt" => $prompt,
    "stream" => false
];

// requests to Ollama API
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Check connection and response
if ($httpCode !== 200) {
    echo json_encode([
        "response" => "Sorry, there was an error connecting to AI. (HTTP Code: $httpCode)"
    ]);
    exit;
}

// Convert JSON and return to JavaScript.
$responseData = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        "response" => "Sorry, there was an error processing the data."
    ]);
    exit;
}

echo json_encode([
    "response" => $responseData["response"] ?? "Sorry, the answer could not be processed."
]);
?>
