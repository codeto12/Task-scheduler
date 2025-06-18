<?php

// File paths
define('TASKS_FILE', __DIR__ . '/tasks.txt');
define('SUBSCRIBERS_FILE', __DIR__ . '/subscribers.txt');
define('PENDING_SUBSCRIPTIONS_FILE', __DIR__ . '/pending_subscriptions.txt');

// Task Management Functions
function addTask($task) {
    $tasks = getTasks();
    // Check for duplicates
    foreach ($tasks as $existingTask) {
        if ($existingTask['task'] === $task) {
            return false;
        }
    }
    
    $tasks[] = [
        'task' => $task,
        'completed' => false,
        'created_at' => time()
    ];
    
    return saveTasks($tasks);
}

function getTasks() {
    if (!file_exists(TASKS_FILE)) {
        return [];
    }
    $content = file_get_contents(TASKS_FILE);
    return $content ? json_decode($content, true) : [];
}

function saveTasks($tasks) {
    return file_put_contents(TASKS_FILE, json_encode($tasks));
}

function toggleTaskStatus($taskIndex) {
    $tasks = getTasks();
    if (isset($tasks[$taskIndex])) {
        $tasks[$taskIndex]['completed'] = !$tasks[$taskIndex]['completed'];
        return saveTasks($tasks);
    }
    return false;
}

function deleteTask($taskIndex) {
    $tasks = getTasks();
    if (isset($tasks[$taskIndex])) {
        array_splice($tasks, $taskIndex, 1);
        return saveTasks($tasks);
    }
    return false;
}

// Email Subscription Functions
function generateVerificationCode() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

function addPendingSubscription($email) {
    $code = generateVerificationCode();
    $pending = getPendingSubscriptions();
    $pending[$email] = [
        'code' => $code,
        'timestamp' => time()
    ];
    
    if (savePendingSubscriptions($pending)) {
        $verificationLink = "http://" . $_SERVER['HTTP_HOST'] . 
            dirname($_SERVER['PHP_SELF']) . 
            "/verify.php?email=" . urlencode($email) . 
            "&code=" . $code;
            
        $subject = "Verify your Task Scheduler subscription";
        $message = "
        <html>
        <body>
            <h2>Welcome to Task Scheduler!</h2>
            <p>Please click the link below to verify your email address:</p>
            <p><a href='{$verificationLink}'>{$verificationLink}</a></p>
            <p>This link will expire in 24 hours.</p>
        </body>
        </html>";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: Task Scheduler <noreply@taskscheduler.com>' . "\r\n";
        
        return mail($email, $subject, $message, $headers);
    }
    return false;
}

function getPendingSubscriptions() {
    if (!file_exists(PENDING_SUBSCRIPTIONS_FILE)) {
        return [];
    }
    $content = file_get_contents(PENDING_SUBSCRIPTIONS_FILE);
    return $content ? json_decode($content, true) : [];
}

function savePendingSubscriptions($pending) {
    return file_put_contents(PENDING_SUBSCRIPTIONS_FILE, json_encode($pending));
}

function verifySubscription($email, $code) {
    $pending = getPendingSubscriptions();
    if (isset($pending[$email]) && $pending[$email]['code'] === $code) {
        $subscribers = getSubscribers();
        $subscribers[] = $email;
        if (saveSubscribers($subscribers)) {
            unset($pending[$email]);
            savePendingSubscriptions($pending);
            return true;
        }
    }
    return false;
}

function getSubscribers() {
    if (!file_exists(SUBSCRIBERS_FILE)) {
        return [];
    }
    $content = file_get_contents(SUBSCRIBERS_FILE);
    return $content ? json_decode($content, true) : [];
}

function saveSubscribers($subscribers) {
    return file_put_contents(SUBSCRIBERS_FILE, json_encode($subscribers));
}

function unsubscribe($email) {
    $subscribers = getSubscribers();
    $key = array_search($email, $subscribers);
    if ($key !== false) {
        array_splice($subscribers, $key, 1);
        return saveSubscribers($subscribers);
    }
    return false;
}

function sendReminders() {
    $tasks = getTasks();
    $subscribers = getSubscribers();
    $pendingTasks = array_filter($tasks, function($task) {
        return !$task['completed'];
    });
    
    if (empty($pendingTasks)) {
        return true;
    }
    
    $subject = "Your Pending Tasks Reminder";
    $message = "
    <html>
    <body>
        <h2>Pending Tasks Reminder</h2>
        <p>Here are your pending tasks:</p>
        <ul>";
    
    foreach ($pendingTasks as $task) {
        $message .= "<li>" . htmlspecialchars($task['task']) . "</li>";
    }
    
    $message .= "</ul>";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: Task Scheduler <noreply@taskscheduler.com>' . "\r\n";
    
    $success = true;
    foreach ($subscribers as $email) {
        $unsubscribeLink = "http://" . $_SERVER['HTTP_HOST'] . 
            dirname($_SERVER['PHP_SELF']) . 
            "/unsubscribe.php?email=" . urlencode($email);
            
        $emailMessage = $message . "
        <p>To unsubscribe from these reminders, click here: 
        <a href='{$unsubscribeLink}'>{$unsubscribeLink}</a></p>
        </body>
        </html>";
        
        if (!mail($email, $subject, $emailMessage, $headers)) {
            $success = false;
        }
    }
    
    return $success;
} 