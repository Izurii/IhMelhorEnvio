<?php

namespace IhMelhorEnvio\Classes;

use AddressCore;
use CarrierCore;
use Cart;
use Db;
use GroupCore;
use IhMelhorEnvio;
use IhMelhorEnvio\Assets\CarrierConfigs;
use LanguageCore;
use MelhorEnvio\Enums\Environment;
use MelhorEnvio\Resources\Shipment\Product;
use MelhorEnvio\Shipment;
use ProductCore;
use RangePriceCore;
use RangeWeightCore;
use ShopCore;

class Base
{

	public static function getShippingRates(Cart $params, int $service)
	{
		$shop = new ShopCore(null, null, $params->id_shop);
		$fromCep = str_replace('-', '', $shop->getAddress()->postcode);

		if (empty($fromCep) || $fromCep == null || $fromCep == '') {
			return false;
		}

		$toAddress = new AddressCore($params->id_address_delivery);
		$toCep = str_replace('-', '', $toAddress->postcode);

		if (empty($toCep) || $toCep == null || $toCep == '') {
			return false;
		}
		if (empty($toAddress) || $toAddress == null || $toAddress == '') {
			return false;
		}

		$environment = BaseConfiguration::getEnvironment();
		$token = '';

		if ($environment == Environment::PRODUCTION)
			$token = BaseConfiguration::getProductionApiKey();
		else
			$token = BaseConfiguration::getSandboxApiKey();

		if (empty($token) || $token == null || $token == '') {
			return false;
		}

		$shipment = new Shipment($token, $environment);
		$calculator = $shipment->calculator();

		$calculator->postalCode($fromCep, $toCep);

		try {
			/** @var ProductCore */
			foreach ($params->getProducts() as $product) {

				if (
					!isset($product['height'])
					|| !isset($product['width'])
					|| !isset($product['depth'])
					|| !isset($product['weight'])
					|| !isset($product['price'])
					|| !isset($product['cart_quantity'])
				) {
					throw new \Exception('Product dimensions not found');
					break;
				}

				$calculator->addProduct(
					new Product(
						uniqid(),
						$product['height'],
						$product['width'],
						$product['depth'],
						$product['weight'],
						$product['price'],
						$product['cart_quantity']
					)
				);
			}
		} catch (\Exception $e) {
			return false;
		}

		$calculator->addService($service);
		$response = json_decode(
			$calculator
				->calculate()
				->getBody()
				->getContents()
		);

		if (property_exists($response, 'error')) {
			return false;
		}

		return $response->price;
	}

	public static function installCarrier($carrierId)
	{
		$carrier = new CarrierCore();

		$carrierConfig = CarrierConfigs::getCarrierConfig($carrierId);

		$carrier->name = $carrierConfig->name;
		$carrier->external_module_name = IhMelhorEnvio::MODULE_NAME;
		// $carrier->is_module = true;
		$carrier->max_weight = $carrierConfig->max_weight;
		$carrier->max_width = $carrierConfig->max_width;
		$carrier->max_height = $carrierConfig->max_height;
		$carrier->max_depth = $carrierConfig->max_depth;
		$carrier->range_behavior = 0;
		$carrier->need_range = true;
		$carrier->shipping_external = true;
		$carrier->active = true;

		foreach (LanguageCore::getLanguages(true) as $language) {
			$carrier->delay[(int)$language['id_lang']] = $carrierConfig->name;
		}

		if (!$carrier->add()) {
			return false;
		}

		$groups = GroupCore::getGroups(true);
		foreach ($groups as $group) {
			Db::getInstance()->insert('carrier_group', [
				'id_carrier' => (int)$carrier->id,
				'id_group' => (int)$group['id_group'],
			]);
		}

		$rangePrice = new RangePriceCore();
		$rangePrice->id_carrier = $carrier->id;
		$rangePrice->delimiter1 = '0';
		$rangePrice->delimiter2 = '10000';
		$rangePrice->add();

		$rangeWeight = new RangeWeightCore();
		$rangeWeight->id_carrier = $carrier->id;
		$rangeWeight->delimiter1 = $carrierConfig->min_weight;
		$rangeWeight->delimiter2 = $carrierConfig->max_weight;
		$rangeWeight->add();

		Db::getInstance()->insert(
			'carrier_zone',
			array(
				'id_carrier' => (int)$carrier->id,
				'id_zone' => 6
			)
		);
		Db::getInstance()->insert(
			'delivery',
			array(
				'id_carrier' => (int)$carrier->id,
				'id_range_price' => (int)$rangePrice->id,
				'id_range_weight' => NULL,
				'id_zone' => 6,
				'price' => '0'
			)
		);
		Db::getInstance()->insert(
			'delivery',
			array(
				'id_carrier' => (int)$carrier->id,
				'id_range_price' => NULL,
				'id_range_weight' => (int)$rangeWeight->id,
				'id_zone' => 6,
				'price' => '0'
			)
		);

		$logoName = $carrierConfig->logo;
		if (!copy(
			_PS_MODULE_DIR_ . IhMelhorEnvio::MODULE_NAME . '/logos/' . $logoName,
			_PS_SHIP_IMG_DIR_ . $carrier->id . '.jpg'
		))
			return false;

		return $carrier->id;
	}

	public static function uninstallCarrier($carrierId)
	{
		$carrier = new CarrierCore($carrierId);

		$carrier->active = false;
		$carrier->deleted = true;

		return $carrier->save();
	}

	public static function disableAllCarriers()
	{
	}
}
