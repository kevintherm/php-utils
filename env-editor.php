<?php
/**
 * .env Editor Helper Tool
 * Allows setting or updating environment variables from the command line.
 * 
 * Usage: php env-editor.php set KEY VALUE [path/to/.env]
 * Example: php env-editor.php set APP_ENV local
 */

if (php_sapi_name() !== 'cli') {
    die("This is a CLI-only script.\n");
}

if ($argc < 3) {
    echo "Usage: php env-editor.php set KEY VALUE [path/to/.env]\n";
    echo "Example: php env-editor.php set DB_PASSWORD 'secret'\n";
    exit(1);
}

$action = $argv[1];
$key = strtoupper($argv[2]);
$value = $argv[3];
$envPath = isset($argv[4]) ? $argv[4] : getcwd() . '/.env';

if ($action !== 'set') {
    die("Unknown action: $action. Use 'set'.\n");
}

if (!file_exists($envPath)) {
    echo "Warning: .env file not found at $envPath. Creating a new one.\n";
    touch($envPath);
}

$content = file_get_contents($envPath);
$lines = explode("\n", $content);
$found = false;

$newLine = "{$key}={$value}";

foreach ($lines as $index => $line) {
    if (preg_match("/^$key=/", trim($line))) {
        $lines[$index] = $newLine;
        $found = true;
        break;
    }
}

if (!$found) {
    $lines[] = $newLine;
}

$finalContent = implode("\n", array_map('trim', $lines));
// Ensure only one trailing newline if any
$finalContent = rtrim($finalContent) . "\n";

if (file_put_contents($envPath, $finalContent) !== false) {
    echo "Successfully " . ($found ? "updated" : "added") . " $key in $envPath\n";
} else {
    echo "Error: Could not write to $envPath\n";
    exit(1);
}
