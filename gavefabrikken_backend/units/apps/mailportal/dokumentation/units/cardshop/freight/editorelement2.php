<?php

namespace GFUnit\cardshop\freight;

class EditorElement2
{
    private $freightItem;

    public function __construct(CSFreightItem $freightItem)
    {
        $this->freightItem = $freightItem;
    }

    public function renderEditor()
    {
        $shop = \Shop::find($this->freightItem->getShopId());
        $cardshopFreight = $this->freightItem->getCardshopFreight();

        $parent = null;
        if (intvalgf($this->freightItem->getParentId()) > 0) {
            $parent = \Company::find(intvalgf($this->freightItem->getParentId()));
        }

        // Default dot price
        $cardshopSettings = \CardshopSettings::find_by_shop_id($shop->id);

        $canUseDot = $cardshopSettings->dot_use == 1;
        $dotPrice = $cardshopSettings->dot_price / 100;

        $canUseCarryup = $cardshopSettings->carryup_use == 1;
        $carryupPrice = $cardshopSettings->carryup_price / 100;

        $country = $this->freightItem->getShipToCountry();
        if ($country == 1) $country = "Danmark";
        if ($country == 5) $country = "Sverige";
        if ($country == 4) $country = "Norge";

        $att = $this->freightItem->getContactName();
        if ($parent != null && $att == "11111111") {
            $att = $parent->contact_name;
        }

        $phone = $this->freightItem->getContactPhone();
        if ($parent != null && $phone == "11111111") {
            $phone = $parent->contact_phone;
        }

        $addressType = $this->freightItem->isChild() ? "Underlevering" : "Hovedadressen";
        $addressTypeColor = $this->freightItem->isChild() ? "blue" : "green";

        ob_start();

        ?><tr class="freightitemeditorparent" data-itemkey="<?php echo $this->freightItem->getUniqueKey(); ?>" data-freightid="<?php echo $cardshopFreight == null ? 0 : $cardshopFreight->id; ?>" style="font-family: verdana; font-size: 12px; background: #FAFAFA;">
        <td style="vertical-align: top; padding: 5px;"><?php echo implode(", ", $this->freightItem->getCompanyOrderList()); ?></td>
        <td style="vertical-align: top; padding: 5px;">
            <?php echo $this->freightItem->getShipToCompany(); ?><br>
            <?php echo $this->freightItem->getShipToAddress(); ?><br>
            <?php echo $this->freightItem->getShipToAddress2(); ?><br>
            <?php echo $this->freightItem->getShipToPostalCode() . " " . $this->freightItem->getShipToCity(); ?><br>
            <?php echo $country; ?><br>
            Att: <?php echo $att; ?><br>
            Telefon: <?php echo $phone; ?><br>
            <span style="color: <?php echo $addressTypeColor; ?>;"><?php echo $addressType; ?></span>
        </td>
        <td style="vertical-align: top; padding: 5px; height: 100%; min-height: 65px;">
            <textarea class="freightnotes" style="width: 100%; height: 100%; min-height: 65px;"><?php echo $cardshopFreight == null ? "" : $cardshopFreight->note; ?></textarea>
        </td>
        <td style="vertical-align: top; padding: 5px;">
            <?php if ($canUseDot) {
                $dotPriceType = ($cardshopFreight == null ? 1 : $cardshopFreight->dot_pricetype);
                if ($dotPriceType == 0) $dotPriceType = 1;
                ?>
                <label><input type="checkbox" name="dotdelivery" class="usedot" onchange="useDotChange(this)" <?php if ($cardshopFreight != null && $cardshopFreight->dot == 1) echo "checked"; ?>> DOT levering</label>
                <div class="dotdetails" style="display: <?php if ($cardshopFreight != null && $cardshopFreight->dot == 1) echo "block"; else echo "none"; ?>; margin-top: 4px;">
                    <div>Pris: <select class="dotpricetype" onChange="dotPriceChange(this)">
                            <option value="1" <?php if ($dotPriceType == 1) echo "selected"; ?>>Standard: <?php echo $dotPrice ?></option>
                            <option value="2" <?php if ($dotPriceType == 2) echo "selected"; ?>>Gratis</option>
                            <option value="3" <?php if ($dotPriceType == 3) echo "selected"; ?>>Anden pris</option>
                        </select><span>: <input class="dotpriceamount" type="text" style="width: 50px; text-align: right;" placeholder="Pris" value="<?php if ($cardshopFreight == null || $cardshopFreight->dot_pricetype != 3) echo $dotPrice; else echo $cardshopFreight->dot_price / 100; ?>">,-</span></div>
                    <div style="padding-top: 4px;">
                        DOT dato og tid: <input type="datetime-local" name="dotdate" class="dotdate" value="<?php if ($cardshopFreight == null || $cardshopFreight->dot_date == null) echo ""; else echo $cardshopFreight->dot_date ? $cardshopFreight->dot_date->format('Y-m-d\TH:i') : '' ?>" style="width: 150px;">
                    </div>
                </div>
            <?php } else echo "<input type=\"hidden\" name=\"dotdelivery\" class=\"usedot\" value='0'>"; ?>
        </td>
        <td style="vertical-align: top; padding: 5px;">
            <?php if ($canUseCarryup) {
                $carryupPriceType = ($cardshopFreight == null ? 1 : $cardshopFreight->carryup_pricetype);
                if ($carryupPriceType == 0) $carryupPriceType = 1;

                $carryupType = ($cardshopFreight == null ? 3 : $cardshopFreight->carryuptype);
                if ($carryupType == 0) $carryupType = 3;
                ?>
                <label><input type="checkbox" name="carryup" class="usecarryup" onChange="useCarryupChange(this)" <?php if ($cardshopFreight != null && $cardshopFreight->carryup == 1) echo "checked"; ?>> Opbæring</label>
                <div class="carryupdetails" style="line-height: 150%; display: <?php if ($cardshopFreight != null && $cardshopFreight->carryup == 1) echo "block"; else echo "none"; ?>; margin-top: 4px;">
                    Pris: <select class="carryuppricetype" onChange="carryupPriceChange(this)">
                        <option value="1" <?php if ($carryupPriceType == 1) echo "selected"; ?>>Standard: <?php echo $carryupPrice; ?></option>
                        <option value="2" <?php if ($carryupPriceType == 2) echo "selected"; ?>>Gratis</option>
                        <option value="3" <?php if ($carryupPriceType == 3) echo "selected"; ?>>Anden pris</option>
                    </select><span>: <input class="carryuppriceamount" type="text" style="width: 50px; text-align: right;" placeholder="Pris" value="<?php if ($cardshopFreight == null || $cardshopFreight->carryup_pricetype != 3) echo $carryupPrice; else echo $cardshopFreight->carryup_price / 100; ?>">,-</span><br>
                    <label><input type="radio" name="carryuptype<?php echo $this->freightItem->getUniqueKey(); ?>" class="carryuptype" value="3" <?php if ($carryupType == 3) echo "checked"; ?>> Plads til helpalle</label><br>
                    <label><input type="radio" name="carryuptype<?php echo $this->freightItem->getUniqueKey(); ?>" class="carryuptype" value="2" <?php if ($carryupType == 2) echo "checked"; ?>> Plads til halvpalle</label><br>
                    <label><input type="radio" name="carryuptype<?php echo $this->freightItem->getUniqueKey(); ?>" class="carryuptype" value="1" <?php if ($carryupType == 1) echo "checked"; ?>> Har ikke elevator</label>
                </div>
            <?php } else echo "<input type=\"hidden\" name=\"carryup\" class=\"usecarryup\" value='0'>"; ?>
        </td>
        <td id="errorpanel<?php echo str_replace(":", "_", $this->freightItem->getUniqueKey()); ?>" class="savepanel" style="display: none; padding: 12px; background-color: #ffcccc; border: 1px solid #ff0000; vertical-align: top; padding: 5px;"></td>
        </tr><?php

        $content = ob_get_clean();
        return $content;
    }
}
?>
