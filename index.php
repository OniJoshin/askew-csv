<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Askews CRM Data</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            padding: 2rem;
        }
        .upload-card {
            max-width: 500px;
            margin: 0 auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

<div class="upload-card">
    <h2 class="text-center mb-4">Upload Askews CRM Data</h2>

    <form action="process.php" method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="leads" class="form-label">Leads CSV</label>
            <input type="file" class="form-control" name="leads" id="leads" accept=".csv" required>
        </div>

        <div class="mb-3">
            <label for="emails_askews" class="form-label">Askews Email Summary CSV</label>
            <input type="file" class="form-control" name="emails_askews" id="emails_askews" accept=".csv" required>
        </div>

        <div class="mb-3">
            <label for="emails_debtclaims" class="form-label">Debt-Claims Email Summary CSV</label>
            <input type="file" class="form-control" name="emails_debtclaims" id="emails_debtclaims" accept=".csv" required>
        </div>

        <div class="mb-3">
            <label for="closed_lost" class="form-label">Closed Lost CSV</label>
            <input type="file" class="form-control" name="closed_lost" id="closed_lost" accept=".csv" required>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Generate Report</button>
        </div>
    </form>
</div>

</body>
</html>
