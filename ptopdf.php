<?php

require('vendor/fpdf.php');

/**
 * This function reads a particular row from an SQLite table and generates a PDF layout using the FPDF library.
 *
 * @param string $database The path to the SQLite database file.
 * @param string $table The name of the table from which to read the row.
 * @param int $rowId The ID of the row to read from the table.
 * @param string $outputFile The path to the output PDF file.
 *
 * @throws Exception If the SQLite database file does not exist or if the table or row does not exist.
 */
function generatePdfFromRow($database, $table, $rowId) {
    // Check if the SQLite database file exists
    if (!file_exists($database)) {
        $txt = "SQLite database file does not exist.";
        $myfile = file_put_contents('logs.txt', $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
    }

    // Connect to the SQLite database
    $db = new SQLite3($database, SQLITE3_OPEN_READONLY);

    // Check if the table exists
    $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='{$table}'");
    if (!$result->fetchArray()) {
        $txt = "Table '{$table}' does not exist in the database.";
        $myfile = file_put_contents('logs.txt', $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
    }

    // Fetch the row from the table
    $result = $db->query("SELECT * FROM {$table} WHERE id={$rowId}");
    $row = $result->fetchArray(SQLITE3_ASSOC);

    // Check if the row exists
    if (!$row) {
        $txt = "Row with ID '{$rowId}' does not exist in the table '{$table}'.";
        $myfile = file_put_contents('logs.txt', $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
    }

    // Close the database connection
    $db->close();

    // Generate the PDF layout using FPDF
    $pdf = new FPDF();
    $pdf->AddPage();
    // Arial 10
    $pdf->SetFont('Arial','',10);
    // Layout the row data in the PDF
    foreach ($row as $key => $value) {
        $pdf->Cell(70, 10, $key, 1);
        $str = iconv('UTF-8', 'windows-1252', $value);
        $pdf->Write(10, $str);
        $pdf->Ln();
        if ($key === 'name') {
            $outputFile = str_replace(' ','_',$value).'.pdf';
        }
    }
    // Output the PDF to a file
    $pdf->Output($outputFile, 'D');
}

try {
    // Specify the SQLite database file, table, row ID, and output PDF file
    $database = './api/spaces.db';
    $table = 'spaces';
    $rowId = $_GET['id'];

    // Generate the PDF layout from the specified row
    generatePdfFromRow($database, $table, $rowId);

} catch (Exception $e) {
    $txt = "Error: {$e->getMessage()}";
    $myfile = file_put_contents('logs.txt', $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
}

?>
