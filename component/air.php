<?php

function generateUniqueCodes($count = 1300, $length = 6) {
    $codes = [];
    $possible_numbers = range(0, 9);

    while (count($codes) < $count) {
        $code = '';
        // Sikrer at første ciffer ikke er 0
        $code .= mt_rand(1, 9);

        // Genererer resten af cifrene
        for ($i = 1; $i < $length; $i++) {
            $code .= mt_rand(0, 9);
        }

        // Tilføjer kun koden hvis den ikke allerede eksisterer
        if (!in_array($code, $codes)) {
            $codes[] = $code;
        }
    }

    return $codes;
}

// Genererer koderne
$unique_codes = generateUniqueCodes();

// Gemmer koderne i en fil
$file = fopen('unique_codes.txt', 'w');
foreach ($unique_codes as $code) {
    fwrite($file, $code . "\n");
}
fclose($file);

// Udskriver koderne på skærmen
echo "Generated " . count($unique_codes) . " unique codes:\n\n";
foreach ($unique_codes as $code) {
    echo $code . "<br>";
}

// Alternativ udskrift som CSV
$csv_file = fopen('unique_codes.csv', 'w');
foreach ($unique_codes as $code) {
    fputcsv($csv_file, [$code]);
}
fclose($csv_file);

?>