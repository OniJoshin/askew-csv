<!DOCTYPE html>
<html>
<head>
    <title>Upload CRM CSVs</title>
</head>
<body>
<h2>Upload CRM Files</h2>
<form action="process.php" method="post" enctype="multipart/form-data">
    <label>Leads CSV:</label><br>
    <input type="file" name="leads" accept=".csv" required><br><br>

    <label>Email Summary CSV:</label><br>
    <input type="file" name="emails" accept=".csv" required><br><br>

    <button type="submit">Generate Excel Report</button>
</form>
</body>
</html>
