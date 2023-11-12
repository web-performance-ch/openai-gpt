<?php
/**
 * web-performance.ch 2023-11-12
 * This PHP script sends a request to the OpenAI API to generate an image based on a prompt.
 * It utilizes cURL to make a POST request and retrieve the generated image URL.
 */

 // Obtain the secret that is required.
 $apiKey = ''; 

/**
 * Sends a request to the OpenAI API endpoint with the provided data.
 *
 * @param string $endpoint The API endpoint URL
 * @param string $apiKey The API key for authentication
 * @param array $data The data to be sent in the request
 * @return string The response from the API endpoint
 */
function sendOpenAIRequest($endpoint, $apiKey, $data) {
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ];

    $ch = curl_init($endpoint);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        return 'Error: ' . curl_error($ch);
    } else {
        curl_close($ch);
        return $response;
    }
}


$img = '';
$prompt = '';
// Handle form submission for the prompt text
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $prompt = $_POST['prompt'];

    $apiEndpoint = 'https://api.openai.com/v1/images/generations';
    $data = [
        "prompt" => $prompt,
        "n" => 1,
        "size" => "1024x1024"
    ];

    $response = sendOpenAIRequest($apiEndpoint, $apiKey, $data);
    $json = json_decode($response);
    $img = '<img src="' . $json->data[0]->url . '">';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OpenAI Prompt Image Generation</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Generate Image from Prompt</h1>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <div class="form-group">
                <label for="prompt">Enter Prompt Text:</label>
                <textarea class="form-control" id="prompt" name="prompt" rows="3" required><?php echo $prompt; ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Generate Image</button>
        </form>
    </div>
    <div class="container mt-5">
        <?php echo $img; ?>
    </div>
    <div class="text-center p-4" style="background-color: rgba(0, 0, 0, 0.05);">
        <footer class="mt-5">
            <p class="text-muted">web-performance.ch | Page render time: <span class="text-primary"><?php echo number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 3); ?> seconds</span></p>
        </footer>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
