<?php

header("Access-Control-Allow-Origin: https://www.liligrow.es");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

global $woocommerce;

// Ahora tendrás acceso al contexto actual de WooCommerce
$cart = $woocommerce->cart;

// Realizar operaciones con WooCommerce aquí

echo json_encode($cart);

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $post = json_decode(json_encode($_POST));

//     if (isset($post->action)) {
//         if ($post->action === 'update_cart_item_quantity') {

//             echo json_encode([
//                 "success" => true,
//                 "cart" => $woocommerce->cart
//             ]);
//         }
//     }
// }