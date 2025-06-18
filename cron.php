<?php
require_once 'functions.php';

// Log file for tracking CRON job execution
$logFile = __DIR__ . '/cron.log';

// Log the start of the CRON job
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Starting task reminder CRON job\n", FILE_APPEND);

try {
    // Send reminders to all subscribers
    if (sendReminders()) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Successfully sent reminders\n", FILE_APPEND);
    } else {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Failed to send some reminders\n", FILE_APPEND);
    }
} catch (Exception $e) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n", FILE_APPEND);
}

// Log the end of the CRON job
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Completed task reminder CRON job\n", FILE_APPEND); 