<?php

namespace GFUnit\privatedelivery\sedsv;

class StateMatrixPoint
{

    public $type;
    public $itemno;
    public $quantity;
    public $min_days;
    public $max_days;
    public $avg_days;

    public function __construct($type,$itemno, $quantity, $min_days, $max_days, $avg_days)
    {
        $this->type = $type;
        $this->itemno = $itemno;
        $this->quantity = $quantity;
        $this->min_days = $min_days;
        $this->max_days = $max_days;
        $this->avg_days = $avg_days;
    }

    public function mergeMatrixPoint(StateMatrixPoint $matrixPoint)
    {

        if($this->type != $matrixPoint->type) {
            throw new \Exception('Cannot merge matrix points of different types');
        }

        if($this->itemno != $matrixPoint->itemno) {
            throw new \Exception('Cannot merge matrix points of different itemno');
        }

        $this->min_days = min($this->min_days, $matrixPoint->min_days);
        $this->max_days = max($this->max_days, $matrixPoint->max_days);
        $this->avg_days = ($this->avg_days*$this->quantity + $matrixPoint->avg_days*$matrixPoint->quantity) / ($this->quantity + $matrixPoint->quantity);
        $this->quantity += $matrixPoint->quantity;

    }

}

class StateMatrix
{

    private $helper;

    public function __construct()
    {

        $this->helper = new Helpers();

    }

    public function dispatch()
    {

        // Load data
        $this->loadDBWaitingOrders();
        $this->loadDSVWaitingShipments();
        $this->loadDSVStatusData();

        $itemNoList = $this->getUniqueItemNoList();

        ?><h1>DSV Matrix</h1><?php

        // Make table width matrixpoint data
        ?><table style="width: 100%;" cellpadding="0" cellspacing="0"><?php

        ?><tr>
            <th>Varenr</th>
            <th>Beskrivelse</th>
            <th>SAMPAK</th>
            <th>DK Ikke frigivet</th>
            <th>DK Venter på varer</th>
            <th>DSV: Hold</th>
            <th>DSV: Allocated</th>
            <th>DSV: Released</th>
            <th>DSV: Picked</th>
            <th>DSV: Completed</th>
            <th>DSV: Shipped</th>
        <th>DSV: Total venter</th>
        <th>DSV: Total sendt</th>
        </tr><?php

        foreach($itemNoList as $itemNo) {

            $item = \NavisionItem::find_by_no($itemNo);
            $bomItems = \NavisionBomItem::find_by_sql("SELECT * FROM `navision_bomitem` WHERE language_id = 1 && parent_item_no = '" . $itemNo . "' && deleted is null");

            ?><tr>
                <td><?php echo $itemNo; ?></td>
                <td><?php echo $item->description; ?></td>
                <td><?php

                    if($bomItems != null && count($bomItems) > 0) {
                        foreach ($bomItems as $bomItem) {
                            echo "<div>" . $bomItem->quantity_per . " x " . $bomItem->no . "</div>";
                        }
                    }

                ?></td>
                <td><?php $mp = $this->getMatrixPoint("dknotready",$itemNo); $this->drawMatrixPoint($mp); ?></td>
            <td><?php $mp = $this->getMatrixPoint("dkwaiting",$itemNo); $this->drawMatrixPoint($mp); ?></td>

            </tr><?php

        }

        ?></table><?php

        //echo "<pre>".print_r($this->matrixPointMap,true)."</pre>";

    }


    private function getMatrixPoint($type,$itemNo) {
        if(isset($this->matrixPointMap[$type][$itemNo])) {
            return $this->matrixPointMap[$type][$itemNo];
        }
        return null;
    }

    private function drawMatrixPoint(?StateMatrixPoint $mp) {

        if($mp == null) {
            echo "-";
            return;
        }

        echo "<div>".$mp->quantity."</div><div>".$mp->avg_days."</div>";
    }

    private $matrixPointMap = array();

    private function addMatrixPoint(StateMatrixPoint $matrixPoint)
    {
        if(isset($this->matrixPointMap[$matrixPoint->type][$matrixPoint->itemno])) {
            $this->matrixPointMap[$matrixPoint->type][$matrixPoint->itemno]->mergeMatrixPoint( $matrixPoint);
            return;
        }
        $this->matrixPointMap[$matrixPoint->type][$matrixPoint->itemno] = $matrixPoint;
    }

    private function getUniqueItemNoList() {
        $itemNoList = array();
        foreach($this->matrixPointMap as $type => $matrixPointList) {
            foreach($matrixPointList as $matrixPoint) {
                $itemNoList[$matrixPoint->itemno] = $matrixPoint->itemno;
            }
        }
        return array_values($itemNoList);
    }

    private function loadDSVWaitingShipments()
    {

        // Fetch data from db
        $sql = "SELECT itemno, sum(quantity) as quantity, MIN(TIMESTAMPDIFF(DAY, created_date, NOW())) AS min_days, MAX(TIMESTAMPDIFF(DAY, created_date, NOW())) AS max_days, AVG(TIMESTAMPDIFF(DAY, created_date, NOW())) AS avg_days from shipment where handler = 'mydsv' && shipment_state = 1 group by itemno ORDER BY `shipment`.`itemno` ASC";
        $shipmentList = \Shipment::find_by_sql($sql);

        // Add matrix point
        foreach($shipmentList as $shipment) {
            $matrixPoint = new StateMatrixPoint('dkwaiting', $shipment->itemno, $shipment->quantity, $shipment->min_days, $shipment->max_days, $shipment->avg_days);
            $this->addMatrixPoint($matrixPoint);
        }

    }


    private function loadDBWaitingOrders() {

        $sql = "SELECT pm.model_present_no as itemno, count(o.id) as quantity, MIN(TIMESTAMPDIFF(DAY, o.order_timestamp, NOW())) AS min_days, MAX(TIMESTAMPDIFF(DAY, o.order_timestamp, NOW())) AS max_days, AVG(TIMESTAMPDIFF(DAY, o.order_timestamp, NOW())) AS avg_days 
FROM `order` o 
INNER JOIN shop_user su ON o.shopuser_id = su.id 
INNER JOIN cardshop_settings cs ON su.shop_id = cs.shop_id 
INNER JOIN present_model pm ON pm.model_id = o.present_model_id 
WHERE pm.language_id = 1 
AND su.blocked = 0 
AND su.shutdown = 0 
AND su.delivery_state NOT IN (1,100) 
AND cs.privatedelivery_handler = 'mydsv' 
GROUP BY pm.model_present_no;";

        $orderList = \Order::find_by_sql($sql);

        foreach($orderList as $order) {
            $matrixPoint = new StateMatrixPoint('dknotready', $order->itemno, $order->quantity, $order->min_days, $order->max_days, $order->avg_days);
            $this->addMatrixPoint($matrixPoint);
        }

    }

    private function loadDSVStatusData() {

        $sql = "SELECT 
  shipment.itemno, 
  dsvstatus.last_status, 
  COUNT(dsvstatus.id) AS total_orders,
  MIN(
    CASE 
      WHEN dsvstatus.shipped_date IS NOT NULL THEN TIMESTAMPDIFF(DAY, dsvstatus.created, dsvstatus.shipped_date)
      ELSE TIMESTAMPDIFF(DAY, dsvstatus.created, CURRENT_DATE)
    END
  ) AS dsv_minimum_days,
  MAX(
    CASE 
      WHEN dsvstatus.shipped_date IS NOT NULL THEN TIMESTAMPDIFF(DAY, dsvstatus.created, dsvstatus.shipped_date)
      ELSE TIMESTAMPDIFF(DAY, dsvstatus.created, CURRENT_DATE)
    END
  ) AS dsv_maximum_days,
  AVG(
    CASE 
      WHEN dsvstatus.shipped_date IS NOT NULL THEN TIMESTAMPDIFF(DAY, dsvstatus.created, dsvstatus.shipped_date)
      ELSE TIMESTAMPDIFF(DAY, dsvstatus.created, CURRENT_DATE)
    END
  ) AS dsv_average_days,
  MIN(
    CASE 
      WHEN dsvstatus.shipped_date IS NOT NULL THEN TIMESTAMPDIFF(DAY, shipment.created_date, dsvstatus.shipped_date)
      ELSE TIMESTAMPDIFF(DAY, shipment.created_date, CURRENT_DATE)
    END
  ) AS shipment_minimum_days,
  MAX(
    CASE 
      WHEN dsvstatus.shipped_date IS NOT NULL THEN TIMESTAMPDIFF(DAY, shipment.created_date, dsvstatus.shipped_date)
      ELSE TIMESTAMPDIFF(DAY, shipment.created_date, CURRENT_DATE)
    END
  ) AS shipment_maximum_days,
  AVG(
    CASE 
      WHEN dsvstatus.shipped_date IS NOT NULL THEN TIMESTAMPDIFF(DAY, shipment.created_date, dsvstatus.shipped_date)
      ELSE TIMESTAMPDIFF(DAY, shipment.created_date, CURRENT_DATE)
    END
  ) AS shipment_average_days
FROM 
  dsvstatus
JOIN 
  shipment ON dsvstatus.shipment_id = shipment.id
GROUP BY 
  shipment.itemno, 
  dsvstatus.last_status;";

        $dsvStatusList = \Dsvstatus::find_by_sql($sql);

        foreach($dsvStatusList as $dsvStatus) {

            // Add matrix point
            $matrixPoint = new StateMatrixPoint('se'.trim(strtolower($dsvStatus->last_status)), $dsvStatus->itemno, $dsvStatus->total_orders, $dsvStatus->dsv_minimum_days, $dsvStatus->dsv_maximum_days, $dsvStatus->dsv_average_days);
            $this->addMatrixPoint($matrixPoint);

            // Add matrix point for shipment_ dates
            $matrixPoint = new StateMatrixPoint('setotal'.(trim(strtolower($dsvStatus->last_status)) == "shipped" ? "complete" : "incomplete"), $dsvStatus->itemno, $dsvStatus->total_orders, $dsvStatus->shipment_minimum_days, $dsvStatus->shipment_maximum_days, $dsvStatus->shipment_average_days);
            $this->addMatrixPoint($matrixPoint);
        }

    }

}