<?php
include 'functions.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="expenses_export.csv"');

$output = fopen('php://output', 'w');

// Write CSV header
fputcsv($output, array('Date', 'Amount', 'Category', 'Description'));

// Write data
$expenses = getExpenses();
while($expense = $expenses->fetch_assoc()) {
    fputcsv($output, array(
        $expense['expense_date'],
        $expense['amount'],
        $expense['category'],
        $expense['description']
    ));
}

fclose($output);
exit();
?>