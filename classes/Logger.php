<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
class Logger {

    private $logDirectory;

    // Constructor to initialize log directory
    public function __construct($logDirectory = null) {
        $this->logDirectory = $logDirectory ?? __DIR__ . DIRECTORY_SEPARATOR . 'logs';

        // Create the log directory if it doesn't exist
        if (!file_exists($this->logDirectory)) {
            mkdir($this->logDirectory, 0777, true);
        }
    }

    // Method to log messages
    public function log($log_msg) {
        $logFile = $this->logDirectory . '/log_' . date('d-M-Y') . '.log';
        
        // Check directory is writable
        if (!is_writable($this->logDirectory)) {
            error_log("Directory not writable: " . $this->logDirectory);
            return false;
        }
    
        // Attempt to write with error checking
        $result = @file_put_contents($logFile, $log_msg . PHP_EOL, FILE_APPEND);
        
        if ($result === false) {
            $error = error_get_last();
            error_log("Log write failed: " . print_r($error, true));
            return false;
        }
        
        return true;
    }
}
