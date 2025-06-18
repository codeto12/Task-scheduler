<?php
require_once 'functions.php';

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_task':
                if (!empty($_POST['task'])) {
                    if (addTask($_POST['task'])) {
                        $message = "Task added successfully!";
                    } else {
                        $error = "Failed to add task or task already exists.";
                    }
                }
                break;
                
            case 'toggle_task':
                if (isset($_POST['task_index'])) {
                    if (toggleTaskStatus($_POST['task_index'])) {
                        $message = "Task status updated!";
                    } else {
                        $error = "Failed to update task status.";
                    }
                }
                break;
                
            case 'delete_task':
                if (isset($_POST['task_index'])) {
                    if (deleteTask($_POST['task_index'])) {
                        $message = "Task deleted successfully!";
                    } else {
                        $error = "Failed to delete task.";
                    }
                }
                break;
                
            case 'subscribe':
                if (!empty($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                    if (addPendingSubscription($_POST['email'])) {
                        $message = "Please check your email to verify your subscription!";
                    } else {
                        $error = "Failed to process subscription.";
                    }
                } else {
                    $error = "Please enter a valid email address.";
                }
                break;
        }
    }
}

$tasks = getTasks();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Scheduler</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .task-list {
            list-style: none;
            padding: 0;
        }
        .task-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .task-item:last-child {
            border-bottom: none;
        }
        .task-text {
            flex-grow: 1;
            margin: 0 10px;
        }
        .completed {
            text-decoration: line-through;
            color: #6c757d;
        }
        form {
            margin: 20px 0;
        }
        input[type="text"],
        input[type="email"] {
            padding: 8px;
            width: 100%;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            padding: 8px 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .delete-btn {
            background-color: #dc3545;
        }
        .delete-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Task Scheduler</h1>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <h2>Add New Task</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add_task">
            <input type="text" name="task" placeholder="Enter task description" required>
            <button type="submit">Add Task</button>
        </form>
        
        <h2>Task List</h2>
        <?php if (empty($tasks)): ?>
            <p>No tasks yet. Add one above!</p>
        <?php else: ?>
            <ul class="task-list">
                <?php foreach ($tasks as $index => $task): ?>
                    <li class="task-item">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="toggle_task">
                            <input type="hidden" name="task_index" value="<?php echo $index; ?>">
                            <button type="submit">
                                <?php echo $task['completed'] ? '✓' : '○'; ?>
                            </button>
                        </form>
                        <span class="task-text <?php echo $task['completed'] ? 'completed' : ''; ?>">
                            <?php echo htmlspecialchars($task['task']); ?>
                        </span>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="delete_task">
                            <input type="hidden" name="task_index" value="<?php echo $index; ?>">
                            <button type="submit" class="delete-btn">Delete</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        
        <h2>Subscribe to Reminders</h2>
        <form method="POST">
            <input type="hidden" name="action" value="subscribe">
            <input type="email" name="email" placeholder="Enter your email address" required>
            <button type="submit">Subscribe</button>
        </form>
    </div>
</body>
</html> 