<?php
// 2025 tax brackets for single filers (example, not official!)
// Source provided: moneychimp.com
$brackets = [
    [11600, 0.10],      // 10% up to $11,600
    [47150, 0.12],      // 12% up to $47,150
    [100525, 0.22],     // 22% up to $100,525
    [191950, 0.24],     // 24% up to $191,950
    [243725, 0.32],     // 32% up to $243,725
    [609350, 0.35],     // 35% up to $609,350
    [PHP_INT_MAX, 0.37] // 37% above that
];

$standardDeduction = 15000;

function calculateTaxes($agi, $brackets) {
    $remaining = $agi;
    $previousCap = 0;
    $taxesByBracket = [];
    $totalTaxes = 0;

    foreach ($brackets as $bracket) {
        [$cap, $rate] = $bracket;
        if ($remaining <= 0) break;

        $taxable = min($remaining, $cap - $previousCap);
        $tax = $taxable * $rate;

        $taxesByBracket[] = [
            "range" => "$" . number_format($previousCap+1) . " - $" . number_format($cap),
            "rate" => ($rate * 100) . "%",
            "tax" => $tax
        ];

        $totalTaxes += $tax;
        $remaining -= $taxable;
        $previousCap = $cap;
    }

    return [$taxesByBracket, $totalTaxes];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Simple Tax Calculator</title>
</head>
<body>
<h1>Federal Income Tax Calculator (Example Only)</h1>
<p><strong>Disclaimer:</strong> This is a simplified example for practice. Do NOT use for real tax purposes.</p>

<form method="post">
    <label>Name: <input type="text" name="name" required></label><br><br>
    <label>Gross Income: <input type="text" name="income" required></label><br><br>
    <label>Total Deductions: <input type="text" name="deductions" required></label><br><br>
    <input type="submit" value="Calculate Taxes">
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = htmlspecialchars($_POST["name"]);
    $income = $_POST["income"];
    $deductions = $_POST["deductions"];

    if (!is_numeric($income) || !is_numeric($deductions)) {
        echo "<p style='color:red;'>Please enter valid numeric values for income and deductions.</p>";
    } else {
        $income = floatval($income);
        $deductions = floatval($deductions);

        // Apply standard deduction if user deductions < standard
        if ($deductions < $standardDeduction) {
            $deductions = $standardDeduction;
        }

        $agi = max(0, $income - $deductions);

        list($taxesByBracket, $totalTaxes) = calculateTaxes($agi, $brackets);

        echo "<h2>Results for $name</h2>";
        echo "<p>Gross Income: $" . number_format($income, 2) . "</p>";
        echo "<p>Deductions Applied: $" . number_format($deductions, 2) . "</p>";
        echo "<p>Adjusted Gross Income (AGI): $" . number_format($agi, 2) . "</p>";

        echo "<h3>Taxes by Bracket:</h3><ul>";
        foreach ($taxesByBracket as $row) {
            echo "<li>{$row['rate']} on income {$row['range']}: $" . number_format($row['tax'], 2) . "</li>";
        }
        echo "</ul>";

        echo "<p><strong>Total Taxes Owed:</strong> $" . number_format($totalTaxes, 2) . "</p>";
        echo "<p>Taxes as % of Gross Income: " . ($income > 0 ? round(($totalTaxes / $income) * 100, 2) : 0) . "%</p>";
        echo "<p>Taxes as % of Adjusted Gross Income: " . ($agi > 0 ? round(($totalTaxes / $agi) * 100, 2) : 0) . "%</p>";
    }
}
?>
</body>
</html>
