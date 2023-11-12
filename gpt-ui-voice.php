<?php
// Obtain the secret that is required.
// https://platform.openai.com/api-keys
$apiKey = ''; 

class TextToSpeech {
    private $apiKey;

    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
    }

    public function generateSpeech($inputText, $voice) {
        $ch = curl_init('https://api.openai.com/v1/audio/speech');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer {$this->apiKey}",
            "Content-Type: application/json"
        ));
        $data = json_encode(array(
            "model" => "tts-1",
            "input" => $inputText,
            "voice" => $voice
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $this->saveToFile($result);
        }
    }

    private function saveToFile($speechData) {
        if (!is_dir('tts')) {
            mkdir('tts', 0755, true);
        }
        $dateTime = new DateTime();
        $fileName = 'tts/' . $dateTime->format('Y-m-d_H-i-s') . '.mp3';
        file_put_contents($fileName, $speechData);
        return $fileName;
    }
}

$inputText = '';
$voice = '';

// index.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //require_once 'TextToSpeech.php'; // Assuming TextToSpeech class has been kept in a separate file.

    $inputText = $_POST['input'] ?? '';
    $voice = $_POST['voice'] ?? '';

    $tts = new TextToSpeech($apiKey);
    $tts->generateSpeech($inputText, $voice);
}

// Retrieve the latest file to play it in the Web UI.
$latestFile = "";
$path = 'tts/';
if (!is_dir($path)) {
    mkdir($path, 0777, true);
}
if ($handle = opendir($path)) {
    $files = array_diff(scandir($path), array('.', '..', '.gitkeep'));
    if (!empty($files)) {
        $latestFile = $path . end($files);
    }
    closedir($handle);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Text to Speech</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1>Text to Speech Generator</h1>
        <form action="" method="post">
            <div class="form-group">
                <label for="inputText">Input Text</label>
                <textarea class="form-control" id="inputText" name="input" rows="10" cols="50" ><?php echo $inputText; ?></textarea>
            </div>
            <div class="form-group">
                <label for="voice">Voice</label>
                <select class="form-control" id="voice" name="voice">
                    <option value="alloy" <?php if($voice == 'alloy') { echo 'selected';} ?>>Alloy</option>
                    <option value="echo" <?php if($voice == 'echo') { echo 'selected';} ?>>Echo</option>
                    <option value="fable" <?php if($voice == 'fable') { echo 'selected';} ?>>Fable</option>
                    <option value="onyx" <?php if($voice == 'onyx') { echo 'selected';} ?>>Onyx</option>
                    <option value="nova" <?php if($voice == 'nova') { echo 'selected';} ?>>Nova</option>
                    <option value="shimmer" <?php if($voice == 'shimmer') { echo 'selected';} ?>>Shimmer</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Generate</button>
        </form>
        <?php if (!empty($latestFile)): ?>
            <div class="mt-4">
                <h2>Playback Last Saved File</h2>
                <audio controls>
                    <source src="<?php echo htmlentities($latestFile); ?>" type="audio/mpeg">
                    Your browser does not support the audio tag.
                </audio>
            </div>
        <?php endif; ?>
    </div>
    <div class="text-center p-4" style="background-color: rgba(0, 0, 0, 0.05);">
    <footer class="mt-5">
        <p class="text-muted">web-performance.ch | Page render time: <span class="text-primary"><?php echo number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 3); ?> seconds</span></p>
    </footer>
</div>
</body>
</html>
