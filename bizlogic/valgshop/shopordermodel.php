<?php

namespace GFBiz\valgshop;

class ShopOrderModel
{

    private $shop;
    private $shopmeta;

    private $approval;
    
    private $navisionVSState;

    private $navisionDev = false;

    public function __construct($shopid,$navisionDev=false)
    {
        $this->shop = \Shop::find($shopid);
        $this->navisionDev = $navisionDev;

        $metaList = \ShopMetadata::find_by_sql("SELECT * FROM shop_metadata WHERE shop_id = ".$this->shop->id);
        $this->shopmeta = $metaList[0];

        $approvalList = \ShopApproval::find_by_shop_id($shopid);
        if($approvalList != null && countgf($approvalList) == 0) {
            $this->approval = $approvalList[0];
        }

        $this->navisionVSState = \NavisionVSState::find_by_shop_id($shopid);
        
    }

    public function isPapirValg() {
        return $this->shopmeta->order_type == "papirvalg";
    }

    public function getShopID() {
        if($this->navisionDev) return "test";
        else return "STD";
    }

    /* ORDER STATES */

    public function isFinalInvoice() {
        return $this->isInvoiceApproved();
    }

    /* APPROVAL FUNCTIONS */

    public function hasApproval() {
        return $this->approval != null;
    }

    public function getOrderApproval() {
        if(!$this->hasApproval()) return 0;
        return $this->approval->orderdata_approval;
    }

    public function isOrderApproved() {
        return $this->getOrderApproval() == 2;
    }

    public function isOrderWaitingApproval() {
        return in_array($this->getOrderApproval(),array(1,3));
    }


    public function getInvoiceApproval() {
        if(!$this->hasApproval()) return 0;
        return $this->approval->invoice_approval;
    }

    public function isInvoiceApproved() {
        return $this->getInvoiceApproval() == 2;
    }

    public function isInvoiceWaitingApproval() {
        return in_array($this->getInvoiceApproval(),array(1,3));
    }


    /* INVOICE FUNCTIONALITY */

    public function getShopInvoices()
    {
        if($this->shopInvoiceList === null) {
            $this->loadShopInvoices();
        }
        return $this->shopInvoiceList;
    }

    private $shopInvoiceList;

    private function loadShopInvoices() {

        $list = \ShopInvoice::find('all', array('conditions' => array('shop_id = ?',$this->shop->id)));
        if($list == null || !is_array($list) || countgf($list) == 0) {
            throw new \Exception("Could not find any shop invoices");
        }

        $this->shopInvoiceList = array();
        $hasPrimary = false;

        foreach($list as $shopInvoice) {
            $shopInvoice = new ShopInvoiceModel($shopInvoice,$this);
            if($this->getNavDebitorNo() == $shopInvoice->getBillToCustomer()) {
                $hasPrimary = true;
            }
            $this->shopInvoiceList[] = $shopInvoice;
        }

        if(!$hasPrimary) {
            throw new \Exception("Could not find primary shop invoice");
        }

    }

    /* GET HEADERS */

    public function getShopName() {
        return $this->shop->name;
    }

    public function getVSNumber()
    {
        return $this->shopmeta->order_no;
    }

    public function getSalesperson() {
        return trimgf($this->shopmeta->salesperson_code);
    }

    public function getRequisitionNo() {
        return trimgf($this->shopmeta->requisition_no);
    }

    public function getLanguageID() {
        return intvalgf($this->shop->localisation);
    }

    /* ORDERCONF */

    public function useSuppressOrderConfirmation() {
        return $this->shopmeta->suppress_orderconf == 1;
    }

    /* PREPAYMENT */

    public function usePrepayment() {
        return $this->shopmeta->prepayment == 1 && !$this->isPapirValg();
    }

    public function usePrepaymentReissue() {
        return $this->shopmeta->prepayment_reissue == 1;
    }

    public function hasPrepaymentPostingDate() {
        return $this->shopmeta->prepayment_postingdate == null ? false : true;
    }

    public function getPrepaymentPostingDateString() {
        return date("d-m-Y",$this->shopmeta->prepayment_postingdate->getTimestamp());
    }

    public function hasPrepaymentDueDate() {
        return $this->shopmeta->prepayment_duedate == null ? false : true;
    }

    public function getPrepaymentDueDateString() {
        return date("d-m-Y",$this->shopmeta->prepayment_duedate->getTimestamp());
    }

    /* STATE */

    public function getOrderState() {
        if($this->navisionVSState == null) {
            return 0;
        } else  {
            return $this->navisionVSState->state;
        }

    }

    public function isCancelled() {
        return $this->getOrderState() == 3 || $this->getOrderState() == 4;
    }

    /* NAVISION FUNCTIONS */

    public function getNavDebitorNo() {
        return intvalgf($this->shopmeta->nav_debitor_no);
    }

    public function isPrivateDelivery() {
        return $this->shopmeta->private_delivery == 1;
    }

    public function getLastVersion() {

        $versionList = \NavisionVSVersion::find_by_sql("SELECT * FROM navision_vs_version WHERE status = 1 && shop_id = ".$this->shop->id." ORDER BY version DESC LIMIT 1");

        if(countgf($versionList) == 0) {
            return 0;
        }

        else {
            return $versionList[0]->version;
        }

    }

    public function getNextVersion() {
        return $this->getLastVersion()+1;
    }

    /* ORDER LINE DATA */

    // Present count
    public function getPresentCount()
    {
        return intval($this->shopmeta->user_count);
    }


    // Valgshop fee
    public function getValgshopFee() {
        return intval(floatval($this->shopmeta->valgshop_fee)*100);
    }

    // Gift wrap

    public function useGiftwrap() {
        return intval($this->shopmeta->present_wrap) == 1;
    }

    public function getGiftwrapPrice() {
        return intval(floatval($this->shopmeta->present_wrap_price)*100);
    }

    // Name tags

    public function useNametag() {
        return intval($this->shopmeta->present_nametag) == 1;
    }

    public function getNametagPrice() {
        return intval(floatval($this->shopmeta->present_nametag_price)*100);
    }

    // PAPER CARDS

    public function usePaperCards() {
        return $this->shopmeta->present_papercard == 1;
    }

    public function getPaperCardPrice() {
        return intval(floatval($this->shopmeta->present_papercard_price)*100);
    }

    public function getPaperCardLineCode() {
        return "JULEKORT";
    }

    // DOT
    public function useDOT() {
        return $this->shopmeta->dot_use == 1;
    }

    public function getDOTAmount() {
        return intval($this->shopmeta->dot_amount);
    }


    public function getDOTPrice() {
        return intval(floatval($this->shopmeta->dot_price)*100);
    }

    // FEES

    public function useInvoiceFee() {
        return intvalgf($this->shopmeta->invoice_fee) == 1;
    }

    public function getInvoiceFeePrice() {
        return intval(floatval($this->shopmeta->invoice_fee_value)*100);
    }

    public function useEnvironmentFee() {
        $fee = floatval($this->shopmeta->environment_fee);
        return ($fee > 0);
    }

    public function getEnvironmentFeePercent() {
        //$fee = floatval($this->shopmeta->environment_fee);
        $fee = 3.5;
        return $fee;
    }

    

    // CARRYUP

    public function useCarryup() {
        return $this->shopmeta->carryup_use == 1;
    }

    public function getCarryupAmount() {
        return intval($this->shopmeta->carryup_amount);
    }

    public function getCarryupPrice() {
        return intval(floatval($this->shopmeta->carryup_price)*100);
    }

   
    // PLANT TREE

    public function usePlantTree() {
        return $this->shopmeta->plant_tree == 1;
    }

    public function getPlantTreePrice() {
        return 0;
    }

    // Private delivery

    public function usePrivateDelivery() {
        return $this->shopmeta->private_delivery == 1;
    }

    public function getPrivateDeliveryPrice() {
        return intval(floatval($this->shopmeta->privatedelivery_price)*100);
    }

    // FREE DELIVERY
    public function useFreeDelivery() {
        return $this->shopmeta->deliveryprice_option == 1 && intval($this->shopmeta->deliveryprice_amount) == 0;
    }

    // AGREED DELIVERY
    public function useAgreedDelivery() {
        return $this->shopmeta->deliveryprice_option == 1 && intval($this->shopmeta->deliveryprice_amount) > 0;
    }

    public function getAgreedDeliveryPrice() {
        return intval(floatval($this->shopmeta->deliveryprice_amount)*100);
    }

    // TOTAL PRICE

    public function getShopBudget() {
        return intval(floatval($this->shopmeta->budget)*100);
    }


    public function isFlexBudget() {
        return $this->shopmeta->flex_budget == 1;
    }

    // DISCOUNT

    public function useDiscount() {
        return $this->shopmeta->discount_option == 1;
    }

    public function getDiscountPercentage() {
        return intval($this->shopmeta->discount_value);
    }

    /* DEADLINES */

    public function getDeadlineList() {

        $deadlines = array();

        if($this->shopmeta->deadline_customerdata != null) {

            $deadlines[] = array(
                "sort" => $this->shopmeta->deadline_customerdata->getTimestamp(),
                "text" => $this->dateToText($this->shopmeta->deadline_customerdata)." - Levering af logo, velkomst tekst"
            );

            $deadlines[] = array(
                "sort" => $this->shopmeta->deadline_customerdata->getTimestamp()+1,
                "text" => $this->dateToText($this->shopmeta->deadline_customerdata)." - Lev af medarbejderliste med mail"
            );

        }

        if($this->shopmeta->deadline_testshop != null && !$this->isPapirValg()) {
            $deadlines[] = array(
                "sort" => $this->shopmeta->deadline_testshop->getTimestamp(),
                "text" => $this->dateToText($this->shopmeta->deadline_testshop)." - Deadline på testshop"
            );
        }

        if($this->shopmeta->deadline_changes != null && !$this->isPapirValg()) {
            $deadlines[] = array(
                "sort" => $this->shopmeta->deadline_changes->getTimestamp(),
                "text" => $this->dateToText($this->shopmeta->deadline_changes)." - Rettelser til testshop"
            );
        }

        if($this->shop->start_date != null && !$this->isPapirValg()) {
            $deadlines[] = array(
                "sort" => $this->shop->start_date->getTimestamp(),
                "text" => $this->dateToText($this->shop->start_date)." - Valgshop åbner"
            );
        }

        if($this->shop->end_date != null && !$this->isPapirValg()) {
            $deadlines[] = array(
                "sort" => $this->shop->end_date->getTimestamp(),
                "text" => $this->dateToText($this->shop->end_date)." - Valgshop lukker"
            );
        }

        if($this->shopmeta->reminder_use == 1 && $this->shopmeta->reminder_date != null && !$this->isPapirValg()) {
            $deadlines[] = array(
                "sort" => $this->shopmeta->reminder_date->getTimestamp(),
                "text" => $this->dateToText($this->shopmeta->reminder_date)." - Reminder"
            );
        }

        if($this->shopmeta->deadline_listconfirm != null && !$this->isPapirValg()) {
            $deadlines[] = array(
                "sort" => $this->shopmeta->deadline_listconfirm->getTimestamp(),
                "text" => $this->dateToText($this->shopmeta->deadline_listconfirm)." - Endelig godkendelse af fordelingslister"
            );
        }

        if($this->shopmeta->delivery_date != null && !$this->usePrivateDelivery()) {
            $deadlines[] = array(
                "sort" => $this->shopmeta->delivery_date->getTimestamp(),
                "text" => $this->dateToText($this->shopmeta->delivery_date)." - Levering af gaver"
            );
        }

        if($this->shopmeta->delivery_date != null && $this->usePrivateDelivery()) {
            $deadlines[] = array(
                "sort" => $this->shopmeta->delivery_date->getTimestamp(),
                "text" => 'Uge '.$this->shopmeta->delivery_date->format('W')." - Levering til GLS Pakkeshop"
            );
        }

        return $deadlines;
    }

    public function getSortedDeadlineList() {
        $deadlines = $this->getDeadlineList();
        usort($deadlines, function($a, $b) {
            return $a['sort'] - $b['sort'];
        });
        return $deadlines;
    }

    private function dateToText($date) {
        return $date->format("d.m");
    }


    /*
     * LOAD PRESENTS
     */

    public function getSpecialPriceByVarenr($varenr) {

        $models = $this->getPresentModelsData();
        foreach($models as $model) {
            if(trim(strtolower($model["model_name"])) == trim(strtolower($varenr))) {
                return $model["specialPrice"];
            }
        }

    }

    public function getPresentModelsData() {

        $sql = "SELECT model_name, model_no, fullalias, present.id, present_model.model_id, present_model.model_present_no, present.pt_price FROM `present`, present_model where present.shop_id = ".intval($this->shop->id)." && present.id = present_model.present_id && present_model.language_id = 1  ORDER BY TRIM(`present_model`.`fullalias`) ASC, present.id asc, model_id asc";
        $modellist = \PresentModel::find_by_sql($sql);
        if(countgf($modellist) == 0) return array();

        $presentModels = array();
        $presentModelsNoAlias = array();
        $aliasMap = array();
        $subIndexes = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o");
        $maxAlias = 0;
        $lastAliasForPresentId = array();

        foreach($modellist as $model) {
            $specialPrice = null;

            if($this->getLanguageID() == 1 && trimgf($model->pt_price) != "") {
                $ptPriceData = json_decode($model->pt_price, true);
                if($ptPriceData != null && isset($ptPriceData["special"]) && $ptPriceData["special"] != "" && intvalgf($ptPriceData["special"]) > 0) {
                    $specialPrice = floatval($ptPriceData["special"]) * 100;
                }
            }

            if($this->getLanguageID() == 4 && trimgf($model->pt_price_no) != "") {
                $ptPriceData = json_decode($model->pt_price_no, true);
                if($ptPriceData != null && isset($ptPriceData["special"]) && $ptPriceData["special"] != "" && intvalgf($ptPriceData["special"]) > 0) {
                    $specialPrice = floatval($ptPriceData["special"]) * 100;
                }
            }

            if($this->getLanguageID() == 5 && trimgf($model->pt_price_se) != "") {
                $ptPriceData = json_decode($model->pt_price_se, true);
                if($ptPriceData != null && isset($ptPriceData["special"]) && $ptPriceData["special"] != "" && intvalgf($ptPriceData["special"]) > 0) {
                    $specialPrice = floatval($ptPriceData["special"]) * 100;
                }
            }

            $modelItem = array(
                "present_name" => $model->model_name,
                "model_name" => $model->model_no,
                "full_name" => $model->model_name.(trimgf($model->model_no) == "" ? "" : ", ".$model->model_no),
                "fullalias" => $model->fullalias,
                "present_id" => $model->id,
                "model_id" => $model->model_id,
                "itemno" => $model->model_present_no,
                "specialPrice" => $specialPrice
            );

            if(intval($model->fullalias) > 0) {
                $aliasMap[$model->fullalias][] = $modelItem;
                $maxAlias = max($maxAlias, intval($model->fullalias));
                $lastAliasForPresentId[$model->id] = $model->fullalias;
            } else {
                $presentModelsNoAlias[] = $modelItem;
            }
        }

        // Correct alias without subindexes
        foreach ($aliasMap as $alias => &$models) {
            if (count($models) > 1) {
                // Hvis der er flere modeller med samme alias, tildel bogstaver
                for ($i = 0; $i < count($models); $i++) {
                    $models[$i]["fullalias"] = $alias . $subIndexes[$i];
                }
            } else {
                // Hvis der kun er én model med dette alias, behold det originale alias
                $models[0]["fullalias"] = $alias;
            }
        }

        // Flatten aliasMap into presentModels
        foreach ($aliasMap as $alias => $models) {
            $presentModels = array_merge($presentModels, $models);
        }

        // Add presents without alias to the end of the list and give them alias, if same present_id as last add letters to them
        foreach($presentModelsNoAlias as $model) {
            $presentId = $model["present_id"];
            if (isset($lastAliasForPresentId[$presentId])) {
                $lastAlias = intval($lastAliasForPresentId[$presentId]);
                $subIndexCounter = 0;
                $firstAliasAssigned = false;
                foreach ($presentModels as &$existingModel) {
                    if ($existingModel["present_id"] == $presentId) {
                        if (!$firstAliasAssigned && intval($existingModel["fullalias"]) == trim($existingModel["fullalias"])) {
                            $existingModel["fullalias"] .= $subIndexes[0];
                            $firstAliasAssigned = true;
                        }
                        $subIndexCounter++;
                    }
                }
                $model["fullalias"] = $lastAlias . $subIndexes[$subIndexCounter];
            } else {
                $lastAlias = $maxAlias + 1;
                $model["fullalias"] = $lastAlias;
                $maxAlias++;
            }
            $lastAliasForPresentId[$presentId] = $model["fullalias"];
            $presentModels[] = $model;
        }

        // Sort alfanumerisk
        usort($presentModels, function($a, $b) {
            return strnatcmp($a["fullalias"], $b["fullalias"]);
        });

        return $presentModels;
    }

    /**
     * AUTOGAVE
     */

    public function useAutogave()
    {
        return $this->shopmeta->autogave_use == 1;
    }

    public function getAutogaveItemNo()
    {
        return $this->shopmeta->autogave_itemno;
    }

}

