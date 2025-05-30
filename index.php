<!DOCTYPE html>
<html>
<head>
    <title>Upload Askews CRM Data</title>
</head>
<body>
<h2>Upload CSV Files</h2>
<form action="process.php" method="post" enctype="multipart/form-data">
    <label>Leads CSV:</label><br>
    <input type="file" name="leads" accept=".csv" required><br><br>

    <label>Askews Email Summary CSV:</label><br>
    <input type="file" name="emails_askews" accept=".csv" required><br><br>

    <label>Debt-Claims Email Summary CSV:</label><br>
    <input type="file" name="emails_debtclaims" accept=".csv" required><br><br>

    <button type="submit">Generate Report</button>
</form>

</body>
</html>