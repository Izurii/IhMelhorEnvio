<?php

namespace IhMelhorEnvio\Assets;

use MelhorEnvio\Enums\Service;

class CarrierConfigs
{

	public static function getCarrierConfig($carrier)
	{
		if ($carrier == Service::CORREIOS_PAC) {
			return (object) self::CONFIG_CORREIOS_PAC;
		} elseif ($carrier == Service::CORREIOS_SEDEX) {
			return (object) self::CONFIG_CORREIOS_SEDEX;
		} elseif ($carrier == Service::CORREIOS_ESEDEX) {
			return (object) self::CONFIG_CORREIOS_ESEDEX;
		} elseif ($carrier == Service::CORREIOS_MINI) {
			return (object) self::CONFIG_CORREIOS_MINI;
		} elseif ($carrier == Service::JADLOG_PACKAGE) {
			return (object) self::CONFIG_JADLOG_PACKAGE;
		} elseif ($carrier == Service::JADLOG_COM) {
			return (object) self::CONFIG_JADLOG_COM;
		} elseif ($carrier == Service::VIABRASIL_AEREO) {
			return (object) self::CONFIG_VIABRASIL_AEREO;
		} elseif ($carrier == Service::VIABRASIL_RODOVIARIO) {
			return (object) self::CONFIG_VIABRASIL_RODOVIARIO;
		} elseif ($carrier == Service::LATAMCARGO_PROXIMODIA) {
			return (object) self::CONFIG_LATAMCARGO_PROXIMODIA;
		} elseif ($carrier == Service::LATAMCARGO_PROXIMOVOO) {
			return (object) self::CONFIG_LATAMCARGO_PROXIMOVOO;
		} elseif ($carrier == Service::LATAMCARGO_JUNTOS) {
			return (object) self::CONFIG_LATAMCARGO_JUNTOS;
		} elseif ($carrier == Service::AZULCARGO_AMANHA) {
			return (object) self::CONFIG_AZULCARGO_AMANHA;
		} elseif ($carrier == Service::AZULCARGO_ECOMMERCE) {
			return (object) self::CONFIG_AZULCARGO_ECOMMERCE;
		}
	}

	private const CONFIG_CORREIOS_PAC = [
		"id" => Service::CORREIOS_PAC,
		"name" => "Correios PAC",
		"min_weight" => 0,
		"max_weight" => 30.0,
		"max_width" => 100,
		"max_height" => 100,
		"max_depth" => 100,
		"logo" => "correios.png"
	];

	private const CONFIG_CORREIOS_SEDEX = [
		"id" => Service::CORREIOS_SEDEX,
		"name" => "Correios SEDEX",
		"min_weight" => 0.001,
		"max_weight" => 30.0,
		"max_width" => 100,
		"max_height" => 100,
		"max_depth" => 100,
		"logo" => "correios.png"
	];

	private const CONFIG_CORREIOS_ESEDEX = [
		"id" => Service::CORREIOS_ESEDEX,
		"name" => "Correios ESEDEX",
	];

	private const CONFIG_CORREIOS_MINI = [
		"id" => Service::CORREIOS_MINI,
		"name" => "Correios Mini",
		"min_weight" => 0,
		"max_weight" => 0.3,
		"max_width" => 16,
		"max_height" => 4,
		"max_depth" => 24,
		"logo" => "correios.png"
	];

	private const CONFIG_JADLOG_PACKAGE = [
		"id" => Service::JADLOG_PACKAGE,
		"name" => "Jadlog Package",
		"min_weight" => 0.001,
		"max_weight" => 120.0,
		"max_width" => 105,
		"max_height" => 100,
		"max_depth" => 181,
		"logo" => "jadlog.png"
	];

	private const CONFIG_JADLOG_COM = [
		"id" => Service::JADLOG_COM,
		"name" => "JadLog Com",
		"min_weight" => 0.001,
		"max_weight" => 120.0,
		"max_width" => 105,
		"max_height" => 100,
		"max_depth" => 181,
		"logo" => "jadlog.png"
	];

	private const CONFIG_VIABRASIL_RODOVIARIO = [
		"id" => Service::VIABRASIL_RODOVIARIO,
		"name" => "ViaBrasil Rodoviário",
		"min_weight" => 0.001,
		"max_weight" => 200.0,
		"max_width" => 200,
		"max_height" => 200,
		"max_depth" => 240,
		"logo" => "viabrasil.png"
	];

	private const CONFIG_VIABRASIL_AEREO = [
		"id" => Service::VIABRASIL_AEREO,
		"name" => "ViaBrasil Aéreo",
	];

	private const CONFIG_LATAMCARGO_PROXIMODIA = [
		"id" => Service::LATAMCARGO_PROXIMODIA,
		"name" => "Latam Cargo - Próximo dia",
	];

	private const CONFIG_LATAMCARGO_PROXIMOVOO = [
		"id" => Service::LATAMCARGO_PROXIMOVOO,
		"name" => "Latam Cargo - Próximo Voo",
	];

	private const CONFIG_LATAMCARGO_JUNTOS = [
		"id" => Service::LATAMCARGO_JUNTOS,
		"name" => "Latam Cargo - Juntos",
	];

	private const CONFIG_AZULCARGO_AMANHA = [
		"id" => Service::AZULCARGO_AMANHA,
		"name" => "Azul Cargo - Amanhã",
		"min_weight" => 0.1,
		"max_weight" => 60.0,
		"max_height" => 70,
		"max_width" => 80,
		"max_depth" => 100,
		"logo" => "azulcargo.png"
	];

	private const CONFIG_AZULCARGO_ECOMMERCE = [
		"id" => Service::AZULCARGO_ECOMMERCE,
		"name" => "Azul Cargo - Ecommerce",
		"min_weight" => 0.1,
		"max_weight" => 30.0,
		"max_height" => 70,
		"max_width" => 80,
		"max_depth" => 100,
		"logo" => "azulcargo.png"
	];
}
