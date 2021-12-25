<?php

use IhMelhorEnvio\Classes\Base;
use IhMelhorEnvio\Classes\BaseConfiguration;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;

if (!defined('_PS_VERSION_')) {
	exit;
}

class IhMelhorEnvio extends CarrierModuleCore
{

	public const MODULE_NAME = 'ihmelhorenvio';
	public $id_carrier;

	public function __construct()
	{
		$this->name = self::MODULE_NAME;
		$this->tab = 'shipping_logistics';
		$this->version = '1.0.0';
		$this->author = 'Heitor Massarente';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = [
			'min' => '1.6',
			'max' => '1.7.99',
		];
		$this->bootstrap = true;
		$this->limited_countries = array('br');

		parent::__construct();

		$this->displayName = $this->l('Melhor Envio');
		$this->description = $this->l('Módulo para cotações de frete usando o serviço do Melhor Envio');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

		if (!Configuration::get('IHMELHOR_ENVIO')) {
			$this->warning = $this->l('No name provided');
		}
	}

	public function install()
	{
		if (
			!parent::install()
			|| !$this->registerHook('actionCarrierUpdate')
			|| !$this->registerHook('actionAdminCarriersControllerDeleteAfter')
		) {
			return false;
		}
		return true;
	}

	public function uninstall()
	{
		if (
			!parent::uninstall()
			|| !ConfigurationCore::deleteByName(BaseConfiguration::API_ENVIRONMENT)
			|| !ConfigurationCore::deleteByName(BaseConfiguration::API_SANDBOX_KEY)
			|| !ConfigurationCore::deleteByName(BaseConfiguration::API_PROD_KEY)
			|| !ConfigurationCore::deleteByName(BaseConfiguration::SERVICES_ENABLED)
			|| !ConfigurationCore::deleteByName(BaseConfiguration::MODULE_CARRIERS)
			|| !$this->unregisterHook('actionCarrierUpdate')
			|| !$this->unregisterHook('actionAdminCarriersControllerDeleteAfter')
		) {
			return false;
		}
		return true;
	}

	public function reset()
	{
		return $this->uninstall() && $this->install();
	}

	public function getContent()
	{
		return Tools::redirectAdmin(
			SymfonyContainer::getInstance()->get('router')->generate('ihmelhorenvio_configuration')
		);
	}

	public function hookUpdateCarrier($params)
	{
		$id_carrier_old = (int) $params['id_carrier'];
		$id_carrier_new = (int) $params['carrier']->id;
		BaseConfiguration::updateCarrierId($id_carrier_old, $id_carrier_new);
	}

	public function hookActionAdminCarriersControllerDeleteAfter($params)
	{
		$carrier_id = $params['return']->id;
		BaseConfiguration::removeCarrier($carrier_id);
	}

	/**
	 *
	 * @param Cart $params
	 * @param type $shipping_cost
	 * @return boolean
	 */
	public function getOrderShippingCost($params, $shipping_cost)
	{
		$carriers = BaseConfiguration::getCarriers();
		$serviceIdx = array_search(
			$this->id_carrier,
			array_map(fn ($carrier) => $carrier[1], $carriers)
		);
		$service = (int) $carriers[$serviceIdx][0];
		return Base::getShippingRates($params, $service);
	}

	/**
	 *
	 * @param type $params
	 * @return type
	 */
	public function getOrderShippingCostExternal($params)
	{
		return $this->getOrderShippingCost($params, 0);
	}
}
