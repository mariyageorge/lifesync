<?php
include 'connect.php';
session_start();

$database_name = "lifesync_db";
mysqli_select_db($conn, $database_name) or die("Database not found: " . mysqli_error($conn));

// Create expenses table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS expenses (
    expense_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(6) UNSIGNED,
    amount DECIMAL(10,2) NOT NULL,
    description VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL,
    date DATE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)";

if (!mysqli_query($conn, $sql)) {
    echo "Error creating table: " . mysqli_error($conn);
}

// Add a new expense
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_expense'])) {
    $user_id = $_SESSION['user_id'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $date = $_POST['date'];

    $add_sql = "INSERT INTO expenses (user_id, amount, description, category, date) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $add_sql);
    mysqli_stmt_bind_param($stmt, 'idsss', $user_id, $amount, $description, $category, $date);

    if (!mysqli_stmt_execute($stmt)) {
        echo "Error: " . mysqli_error($conn);
    }
}

// Edit an existing expense
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_expense'])) {
    $expense_id = $_POST['expense_id'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $date = $_POST['date'];

    $edit_sql = "UPDATE expenses SET amount=?, description=?, category=?, date=? WHERE expense_id=?";
    $stmt = mysqli_prepare($conn, $edit_sql);
    mysqli_stmt_bind_param($stmt, 'dsssi', $amount, $description, $category, $date, $expense_id);

    if (!mysqli_stmt_execute($stmt)) {
        echo "Error: " . mysqli_error($conn);
    }
}

// Delete an expense
if (isset($_GET['delete_expense'])) {
    $expense_id = $_GET['delete_expense'];

    $delete_sql = "DELETE FROM expenses WHERE expense_id=?";
    $stmt = mysqli_prepare($conn, $delete_sql);
    mysqli_stmt_bind_param($stmt, 'i', $expense_id);

    if (!mysqli_stmt_execute($stmt)) {
        echo "Error: " . mysqli_error($conn);
    }
}

// Fetch expenses for the user
$expenses_sql = "SELECT * FROM expenses WHERE user_id=?";
$stmt = mysqli_prepare($conn, $expenses_sql);
mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$expenses_result = mysqli_stmt_get_result($stmt);

// Calculate total expenses
$total_expenses = 0;
while ($expense = mysqli_fetch_assoc($expenses_result)) {
    $total_expenses += $expense['amount'];
}

// Reset the result pointer
mysqli_stmt_execute($stmt);
$expenses_result = mysqli_stmt_get_result($stmt);

// Group expenses by category
$expenses_by_category = [];
while ($expense = mysqli_fetch_assoc($expenses_result)) {
    $expenses_by_category[$expense['category']][] = $expense;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --color-primary: #6A5ACD;
            --color-secondary: #4CAF50;
            --color-accent: #FF6B6B;
            --color-background: #F0F4F8;
            --color-text: #333;
            --color-white: #FFFFFF;
        }

        body {
            background-color: var(--color-background);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--color-text);
        }

        .header {
            background: var(--color-white);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 0.8rem 1.5rem;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .logo-icon {
            background: var(--color-primary);
            color: white;
            padding: 0.5rem;
            border-radius: 6px;
        }

        .main-content {
            padding: 1.5rem;
        }

        .expense-form {
            background: var(--color-white);
            padding: 1rem;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
        }

        .expense-card {
            background: var(--color-white);
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .category-header {
            display: flex;
            align-items: center;
            padding: 0.8rem 1.2rem;
            background-color: var(--color-primary);
            color: white;
        }

        .category-icon {
            width: 24px;
            height: 24px;
            margin-right: 10px;
            filter: brightness(0) invert(1);
        }

        .expense-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.7rem 1rem;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
        }

        .expense-item:last-child {
            border-bottom: none;
        }

        .expense-actions {
            display: flex;
            gap: 0.5rem;
        }

        .expense-actions a, .expense-actions button {
            padding: 0.3rem 0.6rem;
            font-size: 0.8rem;
        }

        .total-expenses {
            background-color: var(--color-secondary);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            font-size: 1.2rem;
            font-weight: bold;
        }
        .chart-container {
    width: 100%;
    max-width: 600px;
    margin: 2rem auto;
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
    </header>
<br><br>
    <div class="main-content pt-5">
        <div class="container">
            <div class="expense-form">
                <form method="POST" class="row g-2">
                    <div class="col-md-3">
                        <input type="number" name="amount" placeholder="Amount" step="0.01" required class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="description" placeholder="Description" required class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <select name="category" class="form-select form-select-sm" required>
                            <option value="Food">Food</option>
                            <option value="Transportation">Transportation</option>
                            <option value="Utilities">Utilities</option>
                            <option value="Entertainment">Entertainment</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="date" required class="form-control form-control-sm">
                    </div>
                    <div class="col-12">
                        <button type="submit" name="add_expense" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-plus"></i> Add Expense
                        </button>
                    </div>
                </form>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <?php foreach ($expenses_by_category as $category => $category_expenses): ?>
                        <div class="expense-card">
                            <div class="category-header">
                                <?php if ($category === 'Food'): ?>
                                    <img src="https://cdn-icons-png.flaticon.com/512/2997/2997819.png" class="category-icon" alt="Food">
                                <?php elseif ($category === 'Transportation'): ?>
                                    <img src="https://cdn-icons-png.flaticon.com/512/2919/2919600.png" class="category-icon" alt="Transportation">
                                <?php elseif ($category === 'Utilities'): ?>
                                    <img src="https://cdn-icons-png.flaticon.com/512/2901/2901028.png" class="category-icon" alt="Utilities">
                                <?php elseif ($category === 'Entertainment'): ?>
                                    <img src="https://cdn-icons-png.flaticon.com/512/3556/3556897.png" class="category-icon" alt="Entertainment">
                                <?php else: ?>
                                    <img src="https://cdn-icons-png.flaticon.com/512/2799/2799591.png" class="category-icon" alt="Other">
                                <?php endif; ?>
                                <h5 class="m-0"><?php echo htmlspecialchars($category); ?></h5>
                            </div>
                            <?php if (count($category_expenses) > 0): ?>
                                <?php foreach ($category_expenses as $expense): ?>
                                    <div class="expense-item">
                                        <div>
                                            <strong>$<?php echo number_format($expense['amount'], 2); ?></strong>
                                            <br>
                                            <span><?php echo htmlspecialchars($expense['description']); ?></span>
                                        </div>
                                        <div class="expense-actions">
                                            <a href="?delete_expense=<?php echo $expense['expense_id']; ?>" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></a>
                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $expense['expense_id']; ?>"><i class="fas fa-edit"></i></button>
                                        </div>
                                    </div>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal<?php echo $expense['expense_id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Expense</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form method="POST">
                                                        <input type="hidden" name="expense_id" value="<?php echo $expense['expense_id']; ?>">
                                                        <input type="number" name="amount" value="<?php echo $expense['amount']; ?>" step="0.01" required class="form-control form-control-sm mb-2">
                                                        <input type="text" name="description" value="<?php echo htmlspecialchars($expense['description']); ?>" required class="form-control form-control-sm mb-2">
                                                        <select name="category" class="form-select form-select-sm mb-2" required>
                                                            <option value="Food" <?php echo $expense['category'] === 'Food' ? 'selected' : ''; ?>>Food</option>
                                                            <option value="Transportation" <?php echo $expense['category'] === 'Transportation' ? 'selected' : ''; ?>>Transportation</option>
                                                            <option value="Utilities" <?php echo $expense['category'] === 'Utilities' ? 'selected' : ''; ?>>Utilities</option>
                                                            <option value="Entertainment" <?php echo $expense['category'] === 'Entertainment' ? 'selected' : ''; ?>>Entertainment</option>
                                                            <option value="Other" <?php echo $expense['category'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                                        </select>
                                                        <input type="date" name="date" value="<?php echo $expense['date']; ?>" required class="form-control form-control-sm mb-2">
                                                        <button type="submit" name="edit_expense" class="btn btn-primary btn-sm">Update Expense</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-3 text-muted">No expenses in this category</div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="col-md-4">
                    <div class="total-expenses">
                        Total Expenses: $<?php echo number_format($total_expenses, 2); ?>
                    </div>
                    <div class="chart-container">
    <canvas id="expenseChart"></canvas>
</div>                </div>
            </div>
        </div>
    </div>
<script>// Get the expense data grouped by category
const expenseData = [
    <?php
        $categoryData = [];
        foreach ($expenses_by_category as $category => $expenses) {
            $categoryExpenses = 0;
            foreach ($expenses as $expense) {
                $categoryExpenses += $expense['amount'];
            }
            $categoryData[] = "'" . $category . "', " . $categoryExpenses;
        }
        echo implode(', ', $categoryData);
    ?>
];

// Create the chart
const ctx = document.getElementById('expenseChart').getContext('2d');
const expenseChart = new Chart(ctx, {
    type: 'pie',
    data: {
        labels: expenseData.filter((_, index) => index % 2 === 0),
        datasets: [{
            label: 'Expenses',
            data: expenseData.filter((_, index) => index % 2 !== 0).map(Number),
            backgroundColor: [
                '#6A5ACD',
                '#4CAF50',
                '#FF6B6B',
                '#FFA500',
                '#9370DB'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            title: {
                display: true,
                text: 'Expenses by Category'
            }
        }
    }
});</script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script></body>
</html>