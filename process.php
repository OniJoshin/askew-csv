<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = __DIR__ . '/uploads/';
    $jsonDir = __DIR__ . '/data/';
    if (!is_dir($uploadDir)) mkdir($uploadDir);
    if (!is_dir($jsonDir)) mkdir($jsonDir);

    $leadsPath = $uploadDir . 'leads.csv';
    $emailsAskewsPath = $uploadDir . 'emails_askews.csv';
    $emailsDebtPath = $uploadDir . 'emails_debtclaims.csv';

    move_uploaded_file($_FILES['leads']['tmp_name'], $leadsPath);
    move_uploaded_file($_FILES['emails_askews']['tmp_name'], $emailsAskewsPath);
    move_uploaded_file($_FILES['emails_debtclaims']['tmp_name'], $emailsDebtPath);

    function parseCsv($path) {
        $rows = array_map('str_getcsv', file($path));
        $header = array_map('trim', $rows[0]);

        // Remove BOM if present
        if (str_starts_with($header[0], "\xEF\xBB\xBF")) {
            $header[0] = substr($header[0], 3);
        }

        $data = [];
        foreach (array_slice($rows, 1) as $row) {
            $assoc = array_combine($header, $row);
            $data[] = $assoc;
        }
        return $data;
    }

    function filterEmailCsv($rows) {
        $filtered = [];
        $headers = array_keys($rows[0] ?? []);
        foreach ($rows as $row) {
            $values = array_values($row);
            $slice = array_slice($values, 5);
            $allZero = count(array_unique(array_map('trim', $slice))) === 1 && trim($slice[0]) === '0';
            if (!$allZero) $filtered[] = $row;
        }
        return $filtered;
    }

    $emailAskews = parseCsv($emailsAskewsPath);
    $emailDebt = parseCsv($emailsDebtPath);

    $emailAskews = filterEmailCsv($emailAskews);
    $emailDebt = filterEmailCsv($emailDebt);


    $parsedData = [
        'leads' => parseCsv($leadsPath),
        'emails_askews' => $emailAskews,
        'emails_debtclaims' => $emailDebt,
    ];

    foreach ($parsedData['leads'] as &$lead) {
        $dept = trim($lead['Askews Department'] ?? '');
        if ($dept === '') {
            $lead['Askews Department'] = trim($lead['Department Individual'] ?? 'Unknown');
        }
    }
    unset($lead); // break reference


    $leadsData = $parsedData['leads'];

    $departmentCounts = [];
    foreach ($leadsData as $lead) {
        $dept = trim($lead['Askews Department'] ?? 'Unknown');
        if ($dept !== '') {
            $departmentCounts[$dept] = ($departmentCounts[$dept] ?? 0) + 1;
        }
    }

    $parsedData['departmentCounts'] = $departmentCounts;

    $jsonFile = 'report_' . date('Ymd_His') . '.json';
    file_put_contents($jsonDir . $jsonFile, json_encode($parsedData));

    header("Location: view-report.php?data=" . urlencode($jsonFile));
    exit;
}
