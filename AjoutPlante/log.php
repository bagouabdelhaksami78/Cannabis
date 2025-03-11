<?php
function writeLog($message) {
    $file = __DIR__ . "/logs.txt"; // Chemin vers le fichier logs.txt
    $log = date("Y-m-d H:i:s") . " - " . $message . "\n";
    file_put_contents($file, $log, FILE_APPEND | LOCK_EX);
}
?>
