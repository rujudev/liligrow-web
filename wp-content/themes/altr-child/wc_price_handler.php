<?php
// wc_price_handler.php

include_once WC_ABSPATH . 'includes/wc-formatting-functions.php';

// Recibe el precio como parámetro
$price = isset( $_POST['price'] ) ? $_POST['price'] : 0;

// Aplica wc_price a la variable recibida
$formatted_price = wc_price( $price );

echo json_encode( array( 'formatted_price' => $formatted_price ) );

// Asegúrate de finalizar la ejecución después de la salida
wp_die();

?>