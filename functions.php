<?php
$conn = new mysqli('localhost', 'root', '', 'expense_tracker');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function addExpense($amount, $category, $description, $date) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO expenses (amount, category, description, expense_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("dsss", $amount, $category, $description, $date);
    $stmt->execute();
    $stmt->close();
}

function getExpenses($month = null) {
    global $conn;
    $query = "SELECT * FROM expenses";
    if ($month) {
        $query .= " WHERE MONTH(expense_date) = $month AND YEAR(expense_date) = YEAR(CURRENT_DATE())";
    }
    $query .= " ORDER BY expense_date DESC";
    return $conn->query($query);
}

function getMonthlySummary() {
    global $conn;
    return $conn->query("
        SELECT MONTHNAME(expense_date) as month, 
        SUM(amount) as total 
        FROM expenses 
        GROUP BY MONTH(expense_date)
    ");
}

function getCategories() {
    global $conn;
    return $conn->query("SELECT DISTINCT category FROM expenses");
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_expense'])) {
        addExpense(
            $_POST['amount'],
            $_POST['category'],
            $_POST['description'],
            $_POST['expense_date']
        );
        header("Location: index.php");
        exit();
    }
}
?>