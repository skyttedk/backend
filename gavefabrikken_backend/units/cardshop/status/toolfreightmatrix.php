<?php

namespace GFUnit\cardshop\status;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\FreightCalculator;

class ToolFreightMatrix
{


    public function showMatrix()
    {

        ?><h2>Danske fragt matricer</h2>
        <table style="width: 100%;"><tr><td style="vertical-align: top; width: 50%;"><?php

        $dkDrom = FreightCalculator::$drommeGavekortMatrix;
        echo $this->generateFreightMatrixTable($dkDrom,"Drømmegavekort");

        ?></td><td style="vertical-align: top; width: 50%;"><?php

        $dkOther = FreightCalculator::$otherDKMatrix;
        echo $this->generateFreightMatrixTable($dkOther,"Andre DK gavekort");

        ?></td></tr></table><br><br><?php

        ?><h2>Norske fragt matricer</h2><table style="width: 100%;"><tr><td style="vertical-align: top; width: 50%;"><?php

                echo $this->displayNorwegianPostalZones();

                ?></td><td style="vertical-align: top; width: 50%;"><?php

                echo $this->displayFreightMatrix(FreightCalculator::getNOJGK300TO400MatrixContent(),"JGK 300 og 400");

                ?></td></tr><tr><td style="vertical-align: top; width: 50%;"><?php

                echo $this->displayFreightMatrix(FreightCalculator::getNOJGK600TO800MatrixContent(),"JGK 600 og 800");

                ?></td><td style="vertical-align: top; width: 50%;"><?php

                echo $this->displayFreightMatrix(FreightCalculator::getNOGuld1000TO1200MatrixContent(),"GULL");

                ?></td></tr></table><br><br><?php






    }


    private function generateFreightMatrixTable(array $matrix, string $title = 'Fragtmatrix'): string
    {
        $html = '<div class="freight-matrix-container">';
        $html .= '<h2>' . htmlspecialchars($title) . '</h2>';
        $html .= '<table class="freight-matrix-table">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Antal fra</th>';
        $html .= '<th>Antal til</th>';
        $html .= '<th>Pris (DKK)</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        foreach ($matrix as $row) {
            $html .= '<tr>';
            $html .= '<td>' . $row['min'] . '</td>';
            $html .= '<td>' . ($row['max'] == PHP_INT_MAX ? 'Ubegrænset' : $row['max']) . '</td>';
            $html .= '<td class="price">' . number_format($row['price'], 2, ',', '.') . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';

        // CSS til at style tabellen
        $html .= '<style>
        .freight-matrix-container {
            margin: 20px 0;
            font-family: Arial, sans-serif;
        }
        .freight-matrix-container h2 {
            margin-bottom: 15px;
            color: #333;
        }
        .freight-matrix-table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .freight-matrix-table th,
        .freight-matrix-table td {
            padding: 10px 15px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .freight-matrix-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            color: #333;
        }
        .freight-matrix-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .freight-matrix-table tr:hover {
            background-color: #f1f1f1;
        }
        .freight-matrix-table .price {
            text-align: right;
            font-weight: bold;
        }
    </style>';

        return $html;
    }

    /**
     * Genererer en HTML-tabel fra norske postnummerintervaller
     *
     * @return string HTML output med postnummerzonerne
     */
    function displayNorwegianPostalZones(): string
    {
        $areas = FreightCalculator::getNOAreaList();

        $html = '<div class="postal-zones-container">';
        $html .= '<h2>Norske Postnummerzoner</h2>';
        $html .= '<table class="postal-zones-table">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Zone</th>';
        $html .= '<th>Postnummer intervaller</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        foreach ($areas as $zone => $intervals) {
            $html .= '<tr>';
            $html .= '<td class="zone-number">Zone ' . $zone . '</td>';
            $html .= '<td class="zone-intervals">';

            $formattedIntervals = [];
            foreach ($intervals as $interval) {
                // Sørg for at postnumre har 4 cifre med foranstillede nuller
                $from = str_pad($interval[0], 4, '0', STR_PAD_LEFT);
                $to = str_pad($interval[1], 4, '0', STR_PAD_LEFT);

                // Hvis fra og til er ens, vis kun ét postnummer
                if ($from === $to) {
                    $formattedIntervals[] = $from;
                } else {
                    $formattedIntervals[] = $from . ' - ' . $to;
                }
            }

            // Vis intervaller med komma imellem
            $html .= implode('<br>', $formattedIntervals);

            $html .= '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';

        // CSS til at style tabellen
        $html .= '<style>
        .postal-zones-container {
            margin: 20px 0;
            font-family: Arial, sans-serif;
            max-width: 800px;
        }
        .postal-zones-container h2 {
            margin-bottom: 15px;
            color: #333;
        }
        .postal-zones-table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .postal-zones-table th,
        .postal-zones-table td {
        color: #333;
            padding: 12px 15px;
            border: 1px solid #ddd;
        }
        .postal-zones-table th {
            background-color: #f0f7fc;
            font-weight: bold;
            text-align: left;
        }
        .postal-zones-table tr:nth-child(even) {
            background-color: #f8f8f8;
        }
        .postal-zones-table tr:hover {
            background-color: #f1f7fb;
        }
        .zone-number {
            font-weight: bold;
            text-align: center;
            background-color: #eef5fa;
            width: 80px;
        }
        .zone-intervals {
            line-height: 1.5;
        }
    </style>';

        return $html;
    }

    /**
     * Genererer en HTML-tabel fra rå fragtmatrix data
     *
     * @param string $rawMatrix Rå matrix data med tabs og linjeskift
     * @param string $title Titel på tabellen
     * @return string HTML output med tabellen
     */
    function displayFreightMatrix(string $rawMatrix, string $title = 'Fragtpriser'): string
    {
        // Behandl hver linje
        $lines = explode("\n", $rawMatrix);
        $tableData = [];

        foreach ($lines as $line) {
            if (trim($line) != "") {
                $tableData[] = explode("\t", trim($line));
            }
        }

        // Opbyg HTML tabellen
        $html = '<div class="freight-matrix-container">';
        $html .= '<h2>' . htmlspecialchars($title) . '</h2>';
        $html .= '<table class="freight-matrix-table">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Antal fra</th>';
        $html .= '<th>Antal til</th>';

        // Tilføj overskrifter for postzoner
        for ($i = 1; $i <= 5; $i++) {
            $html .= '<th>Postzone ' . $i . '</th>';
        }

        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        // Tilføj rækker
        foreach ($tableData as $row) {
            $html .= '<tr>';
            // Antal fra og til
            $html .= '<td>' . $row[0] . '</td>';
            $html .= '<td>' . $row[1] . '</td>';

            // Tilføj priser for hver postzone
            for ($i = 2; $i < count($row); $i++) {
                // Formater prisen med tusindtalsseparator og decimaler
                $price = number_format((float)$row[$i], 2, ',', '.');
                $html .= '<td class="price">' . $price . '</td>';
            }

            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';

        // CSS til at style tabellen
        $html .= '<style>
        .freight-matrix-container {
            margin: 20px 0;
            font-family: Arial, sans-serif;
        }
        .freight-matrix-container h2 {
            margin-bottom: 15px;
            color: #333;
        }
        .freight-matrix-table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .freight-matrix-table th,
        .freight-matrix-table td {
            padding: 8px 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .freight-matrix-table th {
            background-color: #f0f7fc;
            font-weight: bold;
            position: sticky;
            top: 0;
        }
        .freight-matrix-table tr:nth-child(even) {
            background-color: #f8f8f8;
        }
        .freight-matrix-table tr:hover {
            background-color: #f1f7fb;
        }
        .price {
            text-align: right;
            font-weight: normal;
        }
        @media screen and (max-width: 768px) {
            .freight-matrix-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>';

        return $html;
    }


}