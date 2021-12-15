<?php

namespace IhMelhorEnvio\Controller\Admin;

use IhMelhorEnvio;
use IhMelhorEnvio\Classes\ModuleConfiguration;
use IhMelhorEnvio\Forms\ConfigurationType;
use MelhorEnvio\Enums\Environment;
use MelhorEnvio\Resources\Shipment\Product;
use MelhorEnvio\Shipment;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminIhMelhorEnvioController extends FrameworkBundleAdminController
{
	public function __construct()
	{
		parent::__construct();
	}

	public function configurationAction(Request $request)
	{
		$data = $this->getModuleConfigurationData();
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

		$result &= ModuleConfiguration::setProductionApiKey($data[ModuleConfiguration::API_PROD_KEY]);
		$result &= ModuleConfiguration::setSandboxApiKey($data[ModuleConfiguration::API_SANDBOX_KEY]);

		if ($data[ModuleConfiguration::API_ENVIRONMENT]) {
			$result &= ModuleConfiguration::setEnvironment(Environment::PRODUCTION);
		} else {
			$result &= ModuleConfiguration::setEnvironment(Environment::SANDBOX);
		}

		$result &= ModuleConfiguration::setServicesEnabled($data[ModuleConfiguration::SERVICES_ENABLED]);

		return $result;
	}

	public function getModuleConfigurationData()
	{

		$configEnvironment = ModuleConfiguration::getEnvironment();
		$environment = $configEnvironment === Environment::PRODUCTION ? true : false;

		return [
			ModuleConfiguration::API_PROD_KEY => ModuleConfiguration::getProductionApiKey(),
			ModuleConfiguration::API_SANDBOX_KEY => ModuleConfiguration::getSandboxApiKey(),
			ModuleConfiguration::API_ENVIRONMENT => $environment,
			ModuleConfiguration::SERVICES_ENABLED => ModuleConfiguration::getServicesEnabled()
		];
	}

	public function testConfiguration()
	{

		if (ModuleConfiguration::getEnvironment() === Environment::SANDBOX) {

			try {

				$shipment = new Shipment(ModuleConfiguration::getSandboxApiKey(), Environment::SANDBOX);

				$calculator = $shipment->calculator();

				$calculator->postalCode('01010010', '20271130');
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

				$shipment = new Shipment(ModuleConfiguration::getProductionApiKey(), Environment::PRODUCTION);

				$calculator = $shipment->calculator();

				$calculator->postalCode('01010010', '20271130');

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
