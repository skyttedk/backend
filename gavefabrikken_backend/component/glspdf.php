<?php

// Opret en CSV fil til at gemme resultaterne
$file = fopen('pdf_filnavne.csv', 'w');

// Sæt den korrekte encoding for at håndtere specialtegn
fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

// Definer CSV format med semikolon som separator
$delimiter = ';';

// Skriv header til CSV filen
fputcsv($file, ['Filnavn', 'Base64'], $delimiter);

// Generer 1700 rækker
for ($i = 1; $i <= 1700; $i++) {
    // Generer filnavn
    $filename = "Gavefabrikken - Retur labels-{$i}.pdf";

    // Lav base64 encoding af filnavnet
    $base64 = base64_encode($filename);

    // Skriv til CSV fil med semikolon som separator
    fputcsv($file, [$filename, $base64], $delimiter);
}

// Luk filen
fclose($file);

// Læs og vis indholdet for at verificere
echo "De første 5 rækker af den genererede fil:\n\n";
$handle = fopen('pdf_filnavne.csv', 'r');
$count = 0;

// Vis de første 5 rækker
while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE && $count < 5) {
    echo "Filnavn: " . $data[0] . "\n";
    echo "Base64: " . $data[1] . "\n\n";
    $count++;
}

fclose($handle);

// Vis bekræftelse
echo "Generering fuldført!\n";
echo "Filen 'pdf_filnavne.csv' er blevet oprettet med 1700 rækker.\n";
echo "CSV-filen bruger ';' som separator.\n";

// Alternative version der returnerer et array i stedet for at skrive til fil
function generatePDFArray() {
    $result = [];

    for ($i = 1; $i <= 1700; $i++) {
        $filename = "Gavefabrikken - Retur labels-{$i}.pdf";
        $base64 = base64_encode($filename);

        $result[] = [
            'filename' => $filename,
            'base64' => $base64
        ];
    }

    return $result;
}

// Eksempel på brug af array-versionen:
/*
$data = generatePDFArray();
foreach (array_slice($data, 0, 5) as $row) {
    echo "Filnavn: " . $row['filename'] . "\n";
    echo "Base64: " . $row['base64'] . "\n\n";
}
*/
?>