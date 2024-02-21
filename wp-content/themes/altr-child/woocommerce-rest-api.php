<?php

require_once ABSPATH . 'vendor/autoload.php';
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

// Crea una clase llamada WooRestAPI
class WooRestAPI {
	private $woocommerce;

	public function __construct() {
		$woocommerce = new Client(
			'https://liligrow.es',
			'ck_968f11dd925c7b4e39e8739606395e17753d940d',
			'cs_faa2de6e8fc676becdf65ed16bd4c46f1a97a90c',
			[
				'version' => 'wc/v3',
			]
		);
    }

	public function get_products() {
		try {
            $products = $this->woocommerce->get( 'products' );
            return $products;
        } catch (HttpClientException $e) {
            return false;
        }
	}
}