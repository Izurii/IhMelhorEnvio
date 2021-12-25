<?php

namespace IhMelhorEnvio\Classes;

use ConfigurationCore;
use Exception;

class BaseConfiguration
{

	public const API_ENVIRONMENT = 'IHMELHOR_ENVIO_API_ENVIRONMENT';
	public const API_PROD_KEY = 'IHMELHOR_ENVIO_API_KEY_PROD';
	public const API_SANDBOX_KEY = 'IHMELHOR_ENVIO_API_KEY_SANDBOX';
	public const SERVICES_ENABLED = 'IHMELHOR_ENVIO_SERVICES_ENABLED';
	public const MODULE_CARRIERS = 'IHMELHOR_ENVIO_CARRIERS';

	public function __construct()
	{
	}

	public static function getEnvironment()
	{
		return ConfigurationCore::get(self::API_ENVIRONMENT);
	}

	public static function setEnvironment($environment)
	{
		return ConfigurationCore::updateValue(self::API_ENVIRONMENT, $environment);
	}

	public static function getProductionApiKey()
	{
		return ConfigurationCore::get(self::API_PROD_KEY);
	}

	public static function setProductionApiKey($productionApiKey)
	{
		return ConfigurationCore::updateValue(self::API_PROD_KEY, $productionApiKey);
	}

	public static function getSandboxApiKey()
	{
		return ConfigurationCore::get(self::API_SANDBOX_KEY);
	}

	public static function setSandboxApiKey($sandboxApiKey)
	{
		return ConfigurationCore::updateValue(self::API_SANDBOX_KEY, $sandboxApiKey);
	}

	public static function getServicesEnabled(): array
	{
		$servicesEnabled = ConfigurationCore::get(self::SERVICES_ENABLED);
		if (!$servicesEnabled) {
			return [];
		}
		return array_map('intval', explode(',', $servicesEnabled));
	}

	public static function setServicesEnabled($services)
	{
		$servicesAdded = array_diff($services, self::getServicesEnabled());
		$servicesRemoved = array_diff(self::getServicesEnabled(), $services);

		$carriers = self::getCarriers();
		foreach ($servicesAdded as $service) {
			if (!($carrierId = Base::installCarrier($service))) {
				throw new Exception('Error installing carrier ' . $service);
			}
			$carriers[] = [$service, $carrierId];
		}

		foreach ($servicesRemoved as $service) {

			$carrierIdx = array_search($service, array_map(function ($carrier) {
				return $carrier[0];
			}, $carriers));

			$carrierId = $carriers[$carrierIdx][1];
			array_splice($carriers, $carrierIdx, 1);

			if (!Base::uninstallCarrier($carrierId)) {
				throw new Exception('Error uninstalling carrier ' . $service);
			}
		}

		if (!self::setCarriers($carriers)) {
			return false;
		}

		return ConfigurationCore::updateValue(self::SERVICES_ENABLED, implode(',', $services));
	}

	public static function getCarriers()
	{
		$carriers = ConfigurationCore::get(self::MODULE_CARRIERS);
		if (!$carriers) {
			return [];
		}

		$array = [];
		foreach (explode(',', $carriers) as $carrier) {
			$array[] = array_map('intval', explode(':', $carrier));
		}

		return $array;
	}

	public static function setCarriers($carriers)
	{
		return ConfigurationCore::updateValue(self::MODULE_CARRIERS, implode(',', array_map(function ($carrier) {
			return implode(':', $carrier);
		}, $carriers)));
	}

	public static function updateCarrierId($oldId, $newId)
	{
		$carriers = self::getCarriers();
		foreach ($carriers as $key => $carrier) {
			if ($carrier[1] == $oldId) {
				$carriers[$key][1] = $newId;
			}
		}
		return self::setCarriers($carriers);
	}

	public static function removeCarrier($carrierId)
	{
		$carriers = self::getCarriers();
		$servicesEnabled = self::getServicesEnabled();

		foreach ($carriers as $key => $carrier) {
			if ($carrier[1] == $carrierId) {
				unset($carriers[$key]);
				unset($servicesEnabled[array_search($carrier[0], $servicesEnabled)]);
			}
		}
		return self::setCarriers($carriers) && self::setServicesEnabled($servicesEnabled);
	}
}
