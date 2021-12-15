<?php

namespace IhMelhorEnvio\Forms;

use IhMelhorEnvio\Classes\ModuleConfiguration;
use MelhorEnvio\Enums\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationType extends AbstractType
{

	private const SERVICES_MELHOR_ENVIO = [
		"Correios PAC" => Service::CORREIOS_PAC,
		"Correios SEDEX" => Service::CORREIOS_SEDEX,
		// "Correios ESEDEX" => Service::CORREIOS_ESEDEX,
		"Correios Mini" => Service::CORREIOS_MINI,
		"Jadlog Package" => Service::JADLOG_PACKAGE,
		"JadLog Com" => Service::JADLOG_COM,
		// "ViaBrasil Aéreo" => Service::VIABRASIL_AEREO,
		"ViaBrasil Rodoviário" => Service::VIABRASIL_RODOVIARIO,
		// "Latam Cargo - Próximo dia" => Service::LATAMCARGO_PROXIMODIA,
		// "Latam Cargo - Próximo Voo" => Service::LATAMCARGO_PROXIMOVOO,
		// "Latam Cargo - Juntos" => Service::LATAMCARGO_JUNTOS,
		"Azul Cargo - Amanhã" => Service::AZULCARGO_AMANHA,
		"Azul Cargo - Ecommerce" => Service::AZULCARGO_ECOMMERCE
	];

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add(ModuleConfiguration::API_ENVIRONMENT, CheckboxType::class, [
			'label' => 'Use production API',
			'attr' => [
				'class' => 'align-items-center'
			],
			'required' => false,
		]);
		$builder->add(ModuleConfiguration::API_PROD_KEY, TextType::class, [
			'label' => 'API Production Key',
			'required' => true,
		]);
		$builder->add(ModuleConfiguration::API_SANDBOX_KEY, TextType::class, [
			'label' => 'API Sandbox Key',
			'required' => true,
		]);
		$builder->add(ModuleConfiguration::SERVICES_ENABLED, ChoiceType::class, [
			'label' => 'Services',
			'multiple' => true,
			'expanded' => true,
			'choices' => self::SERVICES_MELHOR_ENVIO,
		]);
		$builder->add('btnTestConfiguration', SubmitType::class, [
			'label' => 'Test Configuration',
			'attr' => [
				'class' => 'btn btn-secondary pull-left',
			],
		]);
		$builder->add('btnSaveConfiguration', SubmitType::class, [
			'label' => 'Save',
			'attr' => [
				'class' => 'btn btn-primary pull-right',
			],
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'label' => false,
		]);
	}
}
