<?php
session_start();
include 'functions.php';
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    if ($username === 'ellen' && $password === '12345') {
        $_SESSION['user'] = $username;
    } else {
        $error = "Invalid login credentials!";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

if (isset($_POST['add_expense'])) {
    addExpense($_POST['amount'], $_POST['category'], $_POST['description'], $_POST['expense_date']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Expense Tracker</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-image: url('background.jpg'); /* replace with your image */
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: red;
            text-align: center;
            font-style: italic;
            margin-bottom: 20px;
        }
        .expense-form, .login-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            margin-bottom: 5px;
            font-weight: 500;
        }
        .form-group input, .form-group select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button, .export-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
        }
        button:hover, .export-btn:hover {
            background: #2980b9;
        }
        .summary-section {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .chart-container {
            height: 300px;
            margin: 20px 0;
        }
        .export-section {
            text-align: right;
        }
        .expense-list {
            margin-top: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: 600;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
<div class="container">
    <?php if (!isset($_SESSION['user'])): ?>
        <h1>Login</h1>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="POST" class="login-form">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" name="login">Login</button>
        </form>
    <?php else: ?>
        <h1>Expense Tracker</h1>
        <div style="text-align: right;">
            <a href="?logout=true" class="export-btn">Logout</a>
        </div>

        <form method="POST" class="expense-form">
            <div class="form-group">
                <label>Amount:</label>
                <input type="number" step="0.01" name="amount" required>
            </div>
            <div class="form-group">
                <label>Category:</label>
                <select name="category" required>
                    <option value="Food">Food</option>
                    <option value="Transport">Transport</option>
                    <option value="Housing">Housing</option>
                    <option value="Entertainment">Entertainment</option>
                    <option value="Utilities">Utilities</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label>Description:</label>
                <input type="text" name="description">
            </div>
            <div class="form-group">
                <label>Date:</label>
                <input type="date" name="expense_date" required value="<?= date('Y-m-d') ?>">
            </div>
            <button type="submit" name="add_expense">Add Expense</button>
        </form>

        <div class="summary-section">
            <h2>Monthly Summary</h2>
            <button onclick="showChart()" class="export-btn">Show Histogram</button>
            <div class="chart-container" id="chartArea" style="display: none;">
                <canvas id="summaryChart"></canvas>
            </div>
            <div class="export-section">
                <a href="export.php" class="export-btn">Export to CSV</a>
            </div>
        </div>

        <div class="expense-list">
            <h2>Recent Expenses</h2>
            <table>
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Category</th>
                    <th>Description</th>
                </tr>
                </thead>
                <tbody>
                <?php $expenses = getExpenses(); ?>
                <?php while ($expense = $expenses->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('M j, Y', strtotime($expense['expense_date'])) ?></td>
                        <td>Rs<?= number_format($expense['amount'], 2) ?></td>
                        <td><?= $expense['category'] ?></td>
                        <td><?= $expense['description'] ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php if (isset($_SESSION['user'])): ?>
<script>
    function showChart() {
        document.getElementById('chartArea').style.display = 'block';
    }

    const ctx = document.getElementById('summaryChart').getContext('2d');
    const summaryData = <?php
        $result = getMonthlySummary();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
    ?>;

    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: summaryData.map(item => item.month),
            datasets: [{
                label: 'Monthly Expenses',
                data: summaryData.map(item => item.total),
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
<?php endif; ?>
</body>
</html>
