<?php

namespace GFUnit\apps\reservation_report;
use GFBiz\units\UnitController;

class Controller extends UnitController
{
    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function ping()
    {
        echo "bong";
    }

    // === MAIN CSV FUNCTIONS ===

    public function generateAlleShopsCSVReport()
    {
        $report = $this->getReservationReport('Alle Shops', null);
        $this->outputCSVReport($report, 'alle_shops_rapport_' . date('Y-m-d_H-i-s') . '.csv');
    }

    public function generateShopborgCSVReport()
    {
        $report = $this->getReservationReport('Shopborg', 'cardshop');
        $this->outputCSVReport($report, 'shopborg_rapport_' . date('Y-m-d_H-i-s') . '.csv');
    }

    public function generateValgshopCSVReport()
    {
        $report = $this->getReservationReport('Valgshop', 'non-cardshop');
        $this->outputCSVReport($report, 'valgshop_rapport_' . date('Y-m-d_H-i-s') . '.csv');
    }

    // === MAIN DATA FUNCTION ===

    private function getReservationReport($reportType, $shopFilter)
    {
        $shopCondition = "";

        if ($shopFilter == 'cardshop') {
            $shopCondition = "AND present_reservation.shop_id IN (SELECT DISTINCT shop_id FROM cardshop_settings WHERE language_code = 1)";
        } elseif ($shopFilter == 'non-cardshop') {
            $shopCondition = "AND present_reservation.shop_id NOT IN (SELECT DISTINCT shop_id FROM cardshop_settings)";
        }

        // Hent reservationer med shop_id for sporing - filtrer slettet gaver ud
        $sql = "SELECT
            present_model.model_present_no,
            present_model.model_name,
            present_reservation.quantity,
            present_reservation.shop_id
        FROM present_reservation
        INNER JOIN shop ON shop.id = present_reservation.shop_id
        INNER JOIN present_model ON present_model.model_id = present_reservation.model_id
        INNER JOIN present ON present.id = present_model.present_id
        WHERE
            shop.shop_mode = 1
            AND shop.reservation_state = 1
            AND shop.final_finished = 0
            AND present_model.language_id = 1
            AND present_model.is_deleted = 0
            AND present_reservation.quantity > 0
            AND present.shop_id >= 0
            AND present.deleted = 0
            " . $shopCondition . "
        ORDER BY
            present_model.model_present_no";

        $reservations = \PresentReservation::find_by_sql($sql);

        $finalItems = array();
        $totalQuantity = 0;

        foreach ($reservations as $reservation) {
            $varenr = $reservation->model_present_no;
            $quantity = $reservation->quantity;
            $modelName = $reservation->model_name;
            $shopId = $reservation->shop_id;

            if ($this->isSAMNumber($varenr)) {
                $components = $this->getSAMComponents($varenr);

                foreach ($components as $component) {
                    $componentVarenr = $component['item_no'];
                    $componentDescription = $component['description'];
                    $componentQuantity = $component['quantity_per'] * $quantity;

                    if (!isset($finalItems[$componentVarenr])) {
                        $finalItems[$componentVarenr] = array(
                            'varenr' => $componentVarenr,
                            'description' => $componentDescription,
                            'quantity' => 0,
                            'sam_sources' => array(),
                            'shop_quantities' => array() // Changed from shop_ids to shop_quantities
                        );
                    }

                    $finalItems[$componentVarenr]['quantity'] += $componentQuantity;

                    // Track shop_id with quantity
                    if (!isset($finalItems[$componentVarenr]['shop_quantities'][$shopId])) {
                        $finalItems[$componentVarenr]['shop_quantities'][$shopId] = 0;
                    }
                    $finalItems[$componentVarenr]['shop_quantities'][$shopId] += $componentQuantity;

                    $samFound = false;
                    foreach ($finalItems[$componentVarenr]['sam_sources'] as &$samSource) {
                        if ($samSource['sam_nr'] == $varenr && $samSource['quantity_per'] == $component['quantity_per']) {
                            $samSource['sam_quantity'] += $componentQuantity;
                            $samFound = true;
                            break;
                        }
                    }

                    if (!$samFound) {
                        $finalItems[$componentVarenr]['sam_sources'][] = array(
                            'sam_nr' => $varenr,
                            'quantity_per' => $component['quantity_per'],
                            'sam_quantity' => $componentQuantity
                        );
                    }

                    $totalQuantity += $componentQuantity;
                }
            } else {
                // Direkte varenummer
                if (!isset($finalItems[$varenr])) {
                    $finalItems[$varenr] = array(
                        'varenr' => $varenr,
                        'description' => $modelName,
                        'quantity' => 0,
                        'sam_sources' => array(),
                        'shop_quantities' => array() // Changed from shop_ids to shop_quantities
                    );
                }

                $finalItems[$varenr]['quantity'] += $quantity;

                // Track shop_id with quantity
                if (!isset($finalItems[$varenr]['shop_quantities'][$shopId])) {
                    $finalItems[$varenr]['shop_quantities'][$shopId] = 0;
                }
                $finalItems[$varenr]['shop_quantities'][$shopId] += $quantity;

                $totalQuantity += $quantity;
            }
        }

        $items = array_values($finalItems);
        usort($items, function($a, $b) {
            return strcmp($a['varenr'], $b['varenr']);
        });

        return array(
            'report_type' => $reportType,
            'total_unique_items' => count($items),
            'items' => $items,
            'summary' => array(
                'total_items_count' => $totalQuantity,
                'unique_items' => count($items)
            )
        );
    }

    // === SAM FUNCTIONS ===

    private function isSAMNumber($varenr)
    {
        // Søg i parent_item_no for både language_id 1 og 4, kun aktive (deleted IS NULL)
        $samCheck = \NavisionBomitem::find('first', array(
            'conditions' => array(
                'parent_item_no = ? AND deleted IS NULL AND language_id IN (1, 4)',
                $varenr
            ),
            'select' => 'id'
        ));

        return !empty($samCheck);
    }

    private function getSAMComponents($samNumber)
    {
        // Hent kun komponenter for language_id 1 for at undgå dubletter
        $components = \NavisionBomitem::find('all', array(
            'select' => 'no, description, quantity_per, unit_of_measure_code',
            'conditions' => array(
                'parent_item_no = ? AND deleted IS NULL AND language_id = 1',
                $samNumber
            ),
            'order' => 'no ASC'
        ));

        $result = array();
        foreach ($components as $component) {
            $result[] = array(
                'item_no' => $component->no,
                'description' => $component->description,
                'quantity_per' => $component->quantity_per,
                'unit' => $component->unit_of_measure_code
            );
        }

        return $result;
    }

    // === OUTPUT FUNCTION ===

    private function outputCSVReport($report, $filename)
    {
        header('Content-Type: text/csv; charset=Windows-1252');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        $fixDanishEncoding = function($text) {
            if (empty($text)) return $text;

            $fixes = array(
                'trÃ¦' => 'træ', 'TrÃ¦' => 'Træ', 'Ã¦' => 'æ', 'Ã†' => 'Æ',
                'Ã¸' => 'ø', 'Ã˜' => 'Ø', 'Ã¥' => 'å', 'Ã…' => 'Å',
                'â„¢' => '™', 'BrÃ¸d' => 'Brød', 'GrÃ¸n' => 'Grøn',
                'blÃ¥' => 'blå', 'BlÃ¥' => 'Blå', 'rÃ¸d' => 'rød'
            );

            $fixed = str_replace(array_keys($fixes), array_values($fixes), $text);
            return mb_convert_encoding($fixed, 'Windows-1252', 'UTF-8');
        };

        // Headers - 6 kolonner
        fputcsv($output, array(
            $fixDanishEncoding('Varenr'),
            $fixDanishEncoding('Beskrivelse'),
            $fixDanishEncoding('Antal'),
            $fixDanishEncoding('SAM Kilder'),
            $fixDanishEncoding('Fundet i SAM'),
            $fixDanishEncoding('Shop IDs')
        ), ';');

        // Data rows
        foreach ($report['items'] as $item) {
            $samSources = 'Direkte vare';
            $foundinSAM = 'N/A';

            if (!empty($item['sam_sources'])) {
                $samParts = array();
                $samQuantities = array();

                foreach ($item['sam_sources'] as $sam) {
                    $samParts[] = $sam['sam_nr'] . ' (' . $sam['quantity_per'] . ')';
                    $samQuantities[] = $sam['sam_nr'] . ' (' . $sam['sam_quantity'] . ')';
                }

                $samSources = implode(', ', $samParts);
                $foundinSAM = implode(', ', $samQuantities);
            }

            // Format shop IDs med antal i parentes, adskilt af kolon
            $shopParts = array();
            foreach ($item['shop_quantities'] as $shopId => $shopQuantity) {
                $shopParts[] = $shopId . '(' . $shopQuantity . ')';
            }
            $shopIds = implode(':', $shopParts);

            fputcsv($output, array(
                $fixDanishEncoding($item['varenr']),
                $fixDanishEncoding($item['description']),
                $item['quantity'],
                $fixDanishEncoding($samSources),
                $fixDanishEncoding($foundinSAM),
                $shopIds
            ), ';');
        }

        // Summary
        fputcsv($output, array(), ';');
        fputcsv($output, array(
            $fixDanishEncoding('RAPPORT TYPE'),
            $report['report_type'],
            '',
            '',
            '',
            ''
        ), ';');
        fputcsv($output, array(
            $fixDanishEncoding('TOTAL ANTAL VARER'),
            '',
            $report['summary']['total_items_count'],
            '',
            '',
            ''
        ), ';');

        fclose($output);
        exit();
    }
}
?>