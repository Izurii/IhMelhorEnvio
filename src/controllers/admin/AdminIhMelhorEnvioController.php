<?php

namespace IhMelhorEnvio\Controller\Admin;

use ContextCore;
use IhMelhorEnvio\Classes\BaseConfiguration;
use IhMelhorEnvio\Forms\ConfigurationType;
use MelhorEnvio\Enums\Environment;
use MelhorEnvio\Resources\Shipment\Product;
use MelhorEnvio\Shipment;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use ShopCore;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\HttpFoundation\Request;

class AdminIhMelhorEnvioController extends FrameworkBundleAdminController
{
	public function __construct()
	{
		parent::__construct();
	}

	public function configurationAction(Request $request)
	{
		$data = $this->getBaseConfigurationData();
		$configurationForm = $this->createForm(ConfigurationType::class, $data);
		$configurationForm->handleRequest($request);

		$resultHandleForm = null;
		$resultTestConfiguration = null;

		/** @var ClickableInterface */
		$btnTestConfiguration = $configurationForm->get('btnTestConfiguration');
		/** @var ClickableInterface */
		$btnSaveConfiguration = $configurationForm->get('btnSaveConfiguration');

		if ($btnTestConfiguration->isClicked()) {
			$resultTestConfiguration = $this->testConfiguration();
		}

		if ($btnSaveConfiguration->isClicked() && $configurationForm->isValid()) {
			$resultHandleForm = $this->handleForm($configurationForm->getData());
		}

		return $this->render('@Modules/ihmelhorenvio/views/templates/admin/config.twig', [
			'configurationForm' => $configurationForm->createView(),
			'resultHandleForm' => $resultHandleForm,
			'resultTestConfiguration' => $resultTestConfiguration
		]);
	}

	public function handleForm($data)
	{

		$result = true;

		$result &= BaseConfiguration::setProductionApiKey($data[BaseConfiguration::API_PROD_KEY]);
		$result &= BaseConfiguration::setSandboxApiKey($data[BaseConfiguration::API_SANDBOX_KEY]);

		if ($data[BaseConfiguration::API_ENVIRONMENT]) {
			$result &= BaseConfiguration::setEnvironment(Environment::PRODUCTION);
		} else {
			$result &= BaseConfiguration::setEnvironment(Environment::SANDBOX);
		}

		$result &= BaseConfiguration::setServicesEnabled($data[BaseConfiguration::SERVICES_ENABLED]);

		return $result;
	}

	public function getBaseConfigurationData()
	{

		$configEnvironment = BaseConfiguration::getEnvironment();
		$environment = $configEnvironment === Environment::PRODUCTION ? true : false;

		return [
			BaseConfiguration::API_PROD_KEY => BaseConfiguration::getProductionApiKey(),
			BaseConfiguration::API_SANDBOX_KEY => BaseConfiguration::getSandboxApiKey(),
			BaseConfiguration::API_ENVIRONMENT => $environment,
			BaseConfiguration::SERVICES_ENABLED => BaseConfiguration::getServicesEnabled()
		];
	}

	public function testConfiguration()
	{

		$context = ContextCore::getContext();
		$shop = new ShopCore(null, null, $context->shop->id);
		$fromCep = str_replace('-', '', $shop->getAddress()->postcode);

		if ($fromCep == '' || $fromCep == null || empty($fromCep)) {
			return 'You need to set a valid postal code in your shop address';
		}

		if (BaseConfiguration::getEnvironment() === Environment::SANDBOX) {

			try {

				$sandBoxApiKey = BaseConfiguration::getSandboxApiKey();

				if (
					$sandBoxApiKey == ''
					|| $sandBoxApiKey == null
					|| empty($sandBoxApiKey)
				) {
					return 'You need to set a valid Sandbox API Key';
				}

				$shipment = new Shipment($sandBoxApiKey, Environment::SANDBOX);

				$calculator = $shipment->calculator();

				$calculator->postalCode($fromCep, '03112030');
				$calculator->addProducts(
					new Product(uniqid(), 40, 30, 50, 10.00, 100.0, 1),
					new Product(uniqid(), 5, 1, 10, 0.1, 50.0, 1)
				);

				$response = $calculator->calculate();

				if ($response->getStatusCode() !== 200) {
					$result = $response->getBody()->getContents();
				}

				$result = true;
			} catch (\Exception $e) {
				$result = $e->getMessage();
			}
		} else {

			try {

				$productionApiKey = BaseConfiguration::getProductionApiKey();

				if (
					$productionApiKey == ''
					|| $productionApiKey == null
					|| empty($productionApiKey)
				) {
					return 'You need to set a valid Production API Key';
				}

				$shipment = new Shipment($productionApiKey, Environment::PRODUCTION);

				$calculator = $shipment->calculator();

				$calculator->postalCode($fromCep, '03112030');

				$calculator->addProducts(
					new Product(uniqid(), 40, 30, 50, 10.00, 100.0, 1),
					new Product(uniqid(), 5, 1, 10, 0.1, 50.0, 1)
				);

				$response = $calculator->calculate();

				if ($response->getStatusCode() !== 200) {
					$result = $response->getBody()->getContents();
				}

				$result = true;
			} catch (\Exception $e) {
				$result = $e->getMessage();
			}
		}

		return $result;
	}
}
