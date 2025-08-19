<?php

namespace GFUnit\reservation\balancecompare;

use GFBiz\units\UnitController;
use GFCommon\Model\Navision\ReservationWS;



class SearchNav2 extends UnitController
{

    // Define properties
    private $itemno;
    private $language;
    private $location;

    private $error = "";


    /**
     * @var balancelocal[]
     */
    private $localList;

    /**
     * @var balancenav[]
     */
    private $navlist;

    /**
     * @var balancerow[]
     */
    private $mergedrows = array();

    public function __construct($itemno,$language,$location)
    {
        // Set properties
        $this->itemno = trim($itemno);
        $this->language = intval($language);
        $this->location = $location;

        if($this->itemno == "") {
            $this->setError("Item number is missing");
            return;
        }

        if(!in_array($this->language,array(1,4,5))) {
            $this->setError("Language is missing");
            return;
        }


    }
    public function output() {
        $navisionClient = new ReservationWS($this->language);
        $navisionItems = $navisionClient->getByItemNo($this->itemno, 5000);

        if (!is_array($navisionItems)) {
            echo "<tr><td colspan='8'>Ingen reservationer fundet</td></tr>";
            return;
        }

        // Debug info
        echo "<tr class='debug-section' style='display: none;'><td colspan='8'><h3>Debug Information</h3></td></tr>";
        echo "<tr class='debug-section' style='display: none;'><td colspan='8'><button id='toggle-debug'>Vis/Skjul Debug Info</button></td></tr>";
        echo "<tr class='debug-section' style='display: none;'><td colspan='8' id='debug-output'></td></tr>";

        // Filtrér efter lokation hvis specificeret
        if ($this->location !== "") {
            $navisionItems = array_filter($navisionItems, function($item) {
                return $item->getLocationCode() === $this->location;
            });
        }

        // Sortér efter lokationskode for at gruppere
        usort($navisionItems, function($a, $b) {
            return $a->getLocationCode() <=> $b->getLocationCode();
        });

        // Gruppér efter lokation
        $locationGroups = [];
        foreach ($navisionItems as $item) {
            $locationGroups[$item->getLocationCode()][] = $item;
        }

        // For hver lokation
        $locationCounter = 0;
        foreach ($locationGroups as $locationCode => $locationItems) {
            $locationId = "loc" . $locationCounter++;

            // Beregn lokationens totale balance
            $locationTotal = 0;
            foreach ($locationItems as $item) {
                $locationTotal += $item->getAdjustment();
            }

            // Bestem om lokationen skal være foldet ud fra start
            $locationCollapsed = ($locationTotal == 0) ? 'collapsed' : '';

            // Vis lokationsheader med fold-knap
            echo "<tr class='location-header {$locationCollapsed}' data-location-id='{$locationId}'>
            <td colspan='8'><h1><i class='toggle-icon'></i> Lokation: {$locationCode} <span class='location-total-badge'>Total: {$locationTotal}</span></h1></td>
        </tr>";

            // Kolonne-overskrifter (del af location-content)
            echo "<tr class='location-content column-headers' data-location='{$locationId}' " . ($locationTotal == 0 ? "style='display: none;'" : "") . ">
            <th>Entryno</th>
            <th>Lokation</th>
            <th>Dato</th>
            <th>Antal</th>
            <th>Beregnet saldo</th>
            <th>NAV saldo</th>
            <th>Note</th>
            <th></th>
        </tr>";

            // Gruppér efter kunde
            $customerGroups = [];
            foreach ($locationItems as $item) {
                $noteParts = explode(": ", $item->getNote(), 2);
                $customerName = count($noteParts) > 1 ? $noteParts[0] : 'Ukendt kunde';
                $sellerNote = count($noteParts) > 1 ? $noteParts[1] : $item->getNote();

                // Gem både item og den bearbejdede note
                $item->_parsedNote = $sellerNote;
                $item->_customerName = $customerName;

                $customerGroups[$customerName][] = $item;
            }

            // For hver kunde
            $customerCounter = 0;
            foreach ($customerGroups as $customerName => $customerItems) {
                $customerId = "cust-{$locationId}-" . $customerCounter++;

                // Beregn kundens total forud
                $customerTotal = 0;
                foreach ($customerItems as $item) {
                    $customerTotal += $item->getAdjustment();
                }

                // Bestem farve baseret på kundens total
                $customerColorClass = $customerTotal > 0 ? 'positive-customer' : ($customerTotal < 0 ? 'negative-customer' : 'neutral-customer');

                // Bestem om kunden skal være foldet ud fra start
                $customerCollapsed = ($customerTotal == 0) ? 'collapsed' : '';
                $customerHideStyle = ($locationTotal == 0 || $customerTotal == 0) ? "style='display: none;'" : "";

                // Vis kundeheader med total og fold-knap (del af location-content)
                echo "<tr class='location-content customer-header {$customerColorClass} {$customerCollapsed}' data-location='{$locationId}' data-customer-id='{$customerId}' {$customerHideStyle}>
                <td colspan='6'><h3><i class='toggle-icon'></i> {$customerName}</h3></td>
                <td colspan='2'><strong>Total: {$customerTotal}</strong></td>
            </tr>";

                // Indsæt kolonne-overskrifter specifikt for denne kunde (del af customer-content)
                echo "<tr class='location-content customer-content customer-column-headers' data-location='{$locationId}' data-customer='{$customerId}' " . ($customerTotal == 0 ? "style='display: none;'" : "{$customerHideStyle}") . ">
                <td>Entryno</td>
                <td>Lokation</td>
                <td>Dato</td>
                <td>Antal</td>
                <td>Beregnet saldo</td>
                <td>NAV saldo</td>
                <td>Note</td>
                <td></td>
            </tr>";

                // Sorter efter dato (ældst først)
                usort($customerItems, function($a, $b) {
                    return strtotime($a->getEntryDate()) <=> strtotime($b->getEntryDate());
                });

                // Variabler til at holde styr på kundens totaler
                $customerBalanceSum = 0;

                // Vis hver reservation for kunden
                foreach ($customerItems as $item) {
                    $customerBalanceSum += $item->getAdjustment();

                    // Kontroller om linjen går i 0 (dvs. adjustment er 0)
                    $zeroOpacity = $item->getAdjustment() == 0 ? 'opacity: 0.5;' : '';
                    $itemDisplayStyle = ($customerTotal == 0) ? "style='display: none; {$zeroOpacity}'" : "style='{$zeroOpacity}'";

                    if ($locationTotal == 0) {
                        $itemDisplayStyle = "style='display: none; {$zeroOpacity}'";
                    }

                    echo "<tr class='location-content customer-content' data-location='{$locationId}' data-customer='{$customerId}' {$itemDisplayStyle}>
                    <td>{$item->getEntryNo()}</td>
                    <td>{$item->getLocationCode()}</td>
                    <td>{$item->getEntryDate()}</td>
                    <td>{$item->getAdjustment()}</td>
                    <td style='" . ($item->getNewBalance() != $customerBalanceSum ? "background: yellow" : "") . "'>{$customerBalanceSum}</td>
                    <td style='" . ($item->getNewBalance() != $customerBalanceSum ? "background: yellow" : "") . "'>{$item->getNewBalance()}</td>
                    <td>{$item->_parsedNote}</td>
                    <td></td>
                </tr>";
                }

                // Vis kunde-total (del af customer-content)
                $customerTotalDisplayStyle = ($customerTotal == 0) ? "style='display: none;'" : "";
                if ($locationTotal == 0) {
                    $customerTotalDisplayStyle = "style='display: none;'";
                }

                echo "<tr class='location-content customer-content customer-total' data-location='{$locationId}' data-customer='{$customerId}' {$customerTotalDisplayStyle}>
                <td colspan='3'><strong>Total for {$customerName}</strong></td>
                <td><strong>{$customerTotal}</strong></td>
                <td colspan='4'></td>
            </tr>";
            }

            // Vis lokation-total (del af location-content)
            echo "<tr class='location-content location-total' data-location='{$locationId}' " . ($locationTotal == 0 ? "style='display: none;'" : "") . ">
            <td colspan='3'><strong>Total for lokation {$locationCode}:</strong></td>
            <td><strong>{$locationTotal}</strong></td>
            <td colspan='4'></td>
        </tr>";
        }

        // Beregn totaler for alle reservationer
        $grandTotal = 0;
        $totalItems = 0;

        foreach ($navisionItems as $item) {
            $grandTotal += $item->getAdjustment();
            $totalItems++;
        }

// Vis grand total
        echo "<tr class='grand-total'>
    <td colspan='3'><strong>TOTAL ALLE RESERVATIONER</strong></td>
    <td><strong>{$grandTotal}</strong></td>
    <td colspan='2'><strong>Antal reservationer: {$totalItems}</strong></td>
    <td colspan='2'></td>
</tr>";

        // JavaScript for fold-funktionalitet
        echo "<tr><td colspan='8' style='padding:0; height:0;'>
        <script>
        jQuery(document).ready(function($) {
            // Debug funktion
            function logDebug(message) {
                var timestamp = new Date().toLocaleTimeString();
                $('#debug-output').prepend('[' + timestamp + '] ' + message + '\\n');
            }
            
            // Vis/skjul debug sektion
            $('#toggle-debug').on('click', function() {
                $('.debug-section').toggle();
            });
            
            // Debug initialiseringsinformation
            logDebug('Initialiserer fold-funktionalitet');
            logDebug('Lokationsheadere fundet: ' + $('.location-header').length);
            logDebug('Kundeheadere fundet: ' + $('.customer-header').length);
            
            // Klik-handler for lokationsheadere
            $('.location-header').on('click', function() {
                var locationId = $(this).data('location-id');
                logDebug('Klik på lokation: ' + locationId);
                
                // Toggle visning af alle rækker for denne lokation
                var isCollapsed = $(this).hasClass('collapsed');
                
                if (isCollapsed) {
                    // Fold ud
                    $(this).removeClass('collapsed');
                    $('tr.location-content[data-location=\"' + locationId + '\"]').show();
                    
                    // Men hold kundernes indhold skjult hvis kunden er collapsed
                    $('tr.customer-header.collapsed[data-location=\"' + locationId + '\"]').each(function() {
                        var customerId = $(this).data('customer-id');
                        $('tr.customer-content[data-location=\"' + locationId + '\"][data-customer=\"' + customerId + '\"]').hide();
                    });
                } else {
                    // Fold ind
                    $(this).addClass('collapsed');
                    $('tr.location-content[data-location=\"' + locationId + '\"]').hide();
                }
                
                logDebug('Lokation ' + locationId + ' toggled til ' + (isCollapsed ? 'vist' : 'skjult'));
            });
            
            // Klik-handler for kundeheadere
            $('.customer-header').on('click', function(e) {
                e.stopPropagation(); // Forhindrer at klikket også rammer lokationsheaderen
                
                var customerId = $(this).data('customer-id');
                var locationId = $(this).data('location');
                logDebug('Klik på kunde: ' + customerId + ' (under lokation ' + locationId + ')');
                
                // Toggle visning af alle kundens rækker
                var isCollapsed = $(this).hasClass('collapsed');
                
                if (isCollapsed) {
                    // Fold ud
                    $(this).removeClass('collapsed');
                    $('tr.customer-content[data-location=\"' + locationId + '\"][data-customer=\"' + customerId + '\"]').show();
                } else {
                    // Fold ind
                    $(this).addClass('collapsed');
                    $('tr.customer-content[data-location=\"' + locationId + '\"][data-customer=\"' + customerId + '\"]').hide();
                }
                
                logDebug('Kunde ' + customerId + ' toggled til ' + (isCollapsed ? 'vist' : 'skjult'));
            });
            
            // Verificer at alle 0-balance enheder er skjult ved start
            $('.location-header.collapsed').each(function() {
                var locationId = $(this).data('location-id');
                if ($('tr.location-content[data-location=\"' + locationId + '\"]').is(':visible')) {
                    logDebug('FEJL: Lokation ' + locationId + ' er collapsed men indhold er synligt - retter...');
                    $('tr.location-content[data-location=\"' + locationId + '\"]').hide();
                }
            });
            
            $('.customer-header.collapsed').each(function() {
                var customerId = $(this).data('customer-id');
                var locationId = $(this).data('location');
                if ($('tr.customer-content[data-location=\"' + locationId + '\"][data-customer=\"' + customerId + '\"]').is(':visible')) {
                    logDebug('FEJL: Kunde ' + customerId + ' er collapsed men indhold er synligt - retter...');
                    $('tr.customer-content[data-location=\"' + locationId + '\"][data-customer=\"' + customerId + '\"]').hide();
                }
            });
            
            // System check
            setTimeout(function() {
                logDebug('System check køres...');
                
                // Kontroller at alle lokationer har korrekt synlighed
                $('.location-header').each(function() {
                    var locationId = $(this).data('location-id');
                    var isCollapsed = $(this).hasClass('collapsed');
                    var contentVisible = $('tr.location-content[data-location=\"' + locationId + '\"]').first().is(':visible');
                    
                    if (isCollapsed && contentVisible) {
                        logDebug('FEJL: Lokation ' + locationId + ' er collapsed men indhold er synligt!');
                        $('tr.location-content[data-location=\"' + locationId + '\"]').hide();
                    } else if (!isCollapsed && !contentVisible) {
                        logDebug('FEJL: Lokation ' + locationId + ' er ikke collapsed men indhold er skjult!');
                        $('tr.location-content[data-location=\"' + locationId + '\"]').show();
                        
                        // Men respekter stadig kunders collapsed state
                        $('tr.customer-header.collapsed[data-location=\"' + locationId + '\"]').each(function() {
                            var customerId = $(this).data('customer-id');
                            $('tr.customer-content[data-location=\"' + locationId + '\"][data-customer=\"' + customerId + '\"]').hide();
                        });
                    }
                });
                
                // Kontroller at alle kunder har korrekt synlighed
                $('.customer-header:visible').each(function() {
                    var customerId = $(this).data('customer-id');
                    var locationId = $(this).data('location');
                    var isCollapsed = $(this).hasClass('collapsed');
                    var contentVisible = $('tr.customer-content[data-location=\"' + locationId + '\"][data-customer=\"' + customerId + '\"]').first().is(':visible');
                    
                    if (isCollapsed && contentVisible) {
                        logDebug('FEJL: Kunde ' + customerId + ' er collapsed men indhold er synligt!');
                        $('tr.customer-content[data-location=\"' + locationId + '\"][data-customer=\"' + customerId + '\"]').hide();
                    } else if (!isCollapsed && !contentVisible) {
                        logDebug('FEJL: Kunde ' + customerId + ' er ikke collapsed men indhold er skjult!');
                        $('tr.customer-content[data-location=\"' + locationId + '\"][data-customer=\"' + customerId + '\"]').show();
                    }
                });
                
                logDebug('System check afsluttet');
            }, 500);
            
            // Tilføj CSS dynamisk
            $('head').append(`
                <style>
                /* Generel styling */
                .location-header, .customer-header {
                    cursor: pointer;
                    transition: background-color 0.3s;
                }
                .location-header:hover, .customer-header:hover {
                    filter: brightness(1.1);
                }
                
                /* Farver baseret på balance */
                .positive-customer {
                    background-color: green;
                    color: white;
                }
                .negative-customer {
                    background-color: red;
                    color: white;
                }
                .neutral-customer {
                    background-color: #e8e8e8;
                }
                
                /* Header styling */
                .location-header {
                    background-color: #4a4a4a;
                    color: white;
                }
                .location-header h1 {
                    margin: 0;
                    padding: 10px 0;
                    font-size: 1.5em;
                    display: flex;
                    align-items: center;
                }
                .customer-header h3 {
                    margin: 0;
                    padding: 5px 0;
                    font-size: 1.2em;
                    display: flex;
                    align-items: center;
                }
                
                /* Tabel struktur */
                .customer-column-headers {
                    background-color: #f5f5f5;
                    font-size: 0.9em;
                }
                .column-headers {
                    background-color: #e0e0e0;
                    font-weight: bold;
                }
                .customer-total, .location-total {
                    background-color: #f0f0f0;
                    border-top: 1px solid #ccc;
                }
                .location-total {
                    border-top: 2px solid #999;
                    font-size: 1.1em;
                }
                
                /* Fold icon styling */
                .toggle-icon {
                    display: inline-block;
                    width: 12px;
                    height: 12px;
                    margin-right: 8px;
                    position: relative;
                }
                .toggle-icon:before,
                .toggle-icon:after {
                    content: '';
                    position: absolute;
                    background-color: currentColor;
                }
                .toggle-icon:before {
                    width: 12px;
                    height: 2px;
                    top: 5px;
                    left: 0;
                }
                .toggle-icon:after {
                    width: 2px;
                    height: 12px;
                    top: 0;
                    left: 5px;
                    transition: transform 0.3s;
                }
                .collapsed .toggle-icon:after {
                    transform: scaleY(0);
                }
                
                /* Badges */
                .location-total-badge {
                    margin-left: auto;
                    padding: 2px 8px;
                    background-color: rgba(255,255,255,0.2);
                    border-radius: 4px;
                    font-size: 0.8em;
                }
                
                /* Debug section */
                #debug-output {
                    white-space: pre;
                    font-family: monospace;
                    padding: 10px;
                    background-color: #f8f8f8;
                    max-height: 300px;
                    overflow: auto;
                }
                
                /* Grand total styling */
.grand-total {
    background-color: #333;
    color: white;
    font-size: 1.2em;
    border-top: 3px solid #000;
    border-bottom: 3px solid #000;
}
.grand-total strong {
    font-weight: bold;
}
                </style>
            `);
        });
        </script>
    </td></tr>";
    }





    private function setError($errorMessage) {

    }



}