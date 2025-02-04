<?php
include 'connect.php';
session_start();

$database_name = "lifesync_db";
mysqli_select_db($conn, $database_name) or die("Database not found: " . mysqli_error($conn));

// Create tasks table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS tasks (
    task_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(6) UNSIGNED,
    description VARCHAR(255) NOT NULL,
    status ENUM('incomplete', 'complete') DEFAULT 'incomplete',
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)";

if (!mysqli_query($conn, $sql)) {
    echo "Error creating table: " . mysqli_error($conn);
}

// Add a new task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_task'])) {
    $user_id = $_SESSION['user_id']; // Assuming user_id is stored in session
    $description = $_POST['description'];
    $category = $_POST['category'];

    $add_sql = "INSERT INTO tasks (user_id, description, category) VALUES ('$user_id', '$description', '$category')";
    if (!mysqli_query($conn, $add_sql)) {
        echo "Error: " . mysqli_error($conn);
    }
}

// Edit an existing task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_task'])) {
    $task_id = $_POST['task_id'];
    $description = $_POST['description'];
    $category = $_POST['category'];

    $edit_sql = "UPDATE tasks SET description='$description', category='$category' WHERE task_id='$task_id'";
    if (mysqli_query($conn, $edit_sql)) {
        echo "Task updated successfully";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// Delete a task
if (isset($_GET['delete_task'])) {
    $task_id = $_GET['delete_task'];

    $delete_sql = "DELETE FROM tasks WHERE task_id='$task_id'";
    if (!mysqli_query($conn, $delete_sql)) {
        echo "Error: " . mysqli_error($conn);
    }
}

// Mark a task as complete
if (isset($_GET['complete_task'])) {
    $task_id = $_GET['complete_task'];

    $complete_sql = "UPDATE tasks SET status='complete' WHERE task_id='$task_id'";
    if (!mysqli_query($conn, $complete_sql)) {
        echo "Task marked as complete";
        echo "Error: " . mysqli_error($conn);
    }
}

// Fetch tasks for display
$tasks_sql = "SELECT * FROM tasks WHERE user_id='" . $_SESSION['user_id'] . "'";
$tasks_result = mysqli_query($conn, $tasks_sql);
?>

<!-- HTML for displaying tasks -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo List</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        

:root {
            --nude-100: #F5ECE5;
            --nude-200: #E8D5C8;
            --nude-300: #DBBFAE;
            --nude-400: #C6A792;
            --brown-primary: #8B4513;
            --brown-hover: #A0522D;
        }

        .header {
            width: 100%;
            background: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--brown-primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .logo-text {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--brown-primary);
            letter-spacing: 1px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .logout-btn {
            padding: 0.5rem 1.2rem;
            background: white;
            border: 2px solid var(--brown-primary);
            color: var(--brown-primary);
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: var(--brown-primary);
            color: white;
        }

    
        body {
               background-color: var(--nude-100);
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .task-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
            background-color: #fff;
        }
        .task-item.complete {
            background-color: #e9ecef;
            text-decoration: line-through;
        }
        .task-actions {
            display: flex;
            gap: 10px;
        }
        .modal-content {
            padding: 20px;
        }
    </style>
</head>
<body>
         <!-- Header -->
         <header class="header">
        <div class="logo-section">
            <div class="logo-icon">
                <i class="fas fa-infinity"></i>
            </div>
            <span class="logo-text">LIFE-SYNC</span>
        </div>
        
        <div class="header-right">
            <span>Welcome, <?php echo htmlspecialchars($username); ?></span>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </header><br><br>
<div class="container">
    <h1 class="mt-5 text-center">Todo List</h1>
    <form method="POST" class="mb-4 d-flex gap-2">
        <input type="text" name="description" placeholder="Task description" required class="form-control">
        <input type="text" name="category" placeholder="Category" class="form-control">
        <button type="submit" name="add_task" class="btn btn-primary"><i class="fas fa-plus"></i> Add Task</button>
    </form>

    <ul class="list-group">
        <?php while ($task = mysqli_fetch_assoc($tasks_result)): ?>
            <li class="list-group-item task-item <?php echo $task['status'] === 'complete' ? 'complete' : ''; ?>">
                <div>
                    <strong><?php echo $task['description']; ?></strong> - <span class="text-muted"><?php echo $task['category']; ?></span>
                </div>
                <div class="task-actions">
                    <a href="?complete_task=<?php echo $task['task_id']; ?>" class="btn btn-success btn-sm"><i class="fas fa-check"></i></a>
                    <a href="?delete_task=<?php echo $task['task_id']; ?>" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></a>
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $task['task_id']; ?>"><i class="fas fa-edit"></i></button>
                </div>
            </li>

            <!-- Edit Modal -->
            <div class="modal fade" id="editModal<?php echo $task['task_id']; ?>" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Task</h5>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form method="POST">
                                <input type="hidden" name="task_id" value="<?php echo $task['task_id']; ?>">
                                <input type="text" name="description" value="<?php echo $task['description']; ?>" required class="form-control mb-2">
                                <input type="text" name="category" value="<?php echo $task['category']; ?>" class="form-control mb-2">
                                <button type="submit" name="edit_task" class="btn btn-primary">Update Task</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </ul>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>


