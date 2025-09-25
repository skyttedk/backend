<?php

namespace GFUnit\postnord\sync;

use GFCommon\Model\Navision\CountryHelper;

class OutboundOrderXML extends XMLDoc
{



	public static function generateOrderXML($shipment,$itemList)
	{

		// Get country
		if(trimgf($shipment->shipto_country) == "" && intval($shipment->shipto_country) == 0) {
			$companyOrder = \CompanyOrder::find($shipment->companyorder_id);
			$company = \Company::find($companyOrder->company_id);
			$country = CountryHelper::countryToCode($company->language_code);
		} else {
			$country = CountryHelper::countryToCode($shipment->shipto_country);
		}
		if($country == null || trimgf($country) == "") {
			throw new \Exception("Could not determine country in shipping ".$shipment->id);
		}

		$orderNo = $shipment->id;

		$vareXML = "	<Order>
			<OrderHeader>
				<OrderNo>".$shipment->id."</OrderNo>
				<Addresses>
					<Address>
						<Qual>DEL</Qual>
						<ID>".$shipment->id."</ID>
						<Name>".self::xmlString($shipment->shipto_name)."</Name>
						<Address1>".self::xmlString($shipment->shipto_address)."</Address1>
						<Address2>".self::xmlString($shipment->shipto_address2)."</Address2>
						<PostalNo>".self::xmlString($shipment->shipto_postcode)."</PostalNo>
						<City>".self::xmlString($shipment->shipto_city)."</City>
						<CountryCode>".self::xmlString($country)."</CountryCode>
						<Phone>".self::xmlString($shipment->shipto_phone)."</Phone>
						<SMS>".self::xmlString($shipment->shipto_phone)."</SMS>
						<eMail>".self::xmlString($shipment->shipto_email)."</eMail>
					</Address>
				</Addresses>
				<DeliveryMethod>PN Mypack Collect</DeliveryMethod>
			</OrderHeader>
			<OrderLines>
		";

		foreach($itemList as $index => $item) {

			$vareXML .="	<OrderLine>
					<LineID>".($index+1)."</LineID>
					<ItemNo>".self::xmlString($item["itemno"])."</ItemNo>
					<OrderQty>".self::xmlString($item["quantity"])."</OrderQty>
				</OrderLine>
			";

		}

		$vareXML .="</OrderLines>
		</Order>
	";

		return $vareXML;

	}

	public static function generateEnvelopeXML($orderXML)
	{

		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<Message>
	<MessType>OUTBOUNDORDER</MessType>
	<CreationDate>".date("Y-m-d")."</CreationDate>
	<CreationTime>".date("H:i:s")."</CreationTime>
	<Orders>
	".$orderXML."</Orders>
</Message>";

		// Validate xml
		self::verifyXML($xml,__DIR__."/schemas/OutboundOrder.xsd");

		return $xml;

	}

	/**
	 * REMOVED LINES
	<DeliveryDate>2014-10-08</DeliveryDate>
	<ShipmentDate>2014-10-07</ShipmentDate>
	<CustomerRef>Anders Nilsson</CustomerRef>
	<DeliveryMethod>123456</DeliveryMethod>
	 * <AdditionalServices>
	<AdditionalService>Z41</AdditionalService>
	<AdditionalService>Z66</AdditionalService>
	<AdditionalService>Z75</AdditionalService>
	</AdditionalServices>
	 *
	<DeliveryInstruction1>Portkod 1234</DeliveryInstruction1>
	<WarehouseInfo>Plasta pallen</WarehouseInfo>
	<CustomerInfo>Tack för er order</CustomerInfo>
	 */

}