<?php
/**
 * "Order received" message.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/thankyou.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.1.0
 *
 * @var WC_Order|false $order
 */

defined( 'ABSPATH' ) || exit;
?>

<div id="order-recieved-container">
	<div id="order-recieved-header">
		<svg width="50px" height="50px" viewBox="0 0 50 50" enable-background="new 0 0 50 50" xml:space="preserve">
			<circle fill="#24A801" stroke="#24A801" stroke-linejoin="round" cx="25" cy="25" r="23.667"/>
			<polyline fill="none" stroke="#FFFFFF" stroke-linecap="round" stroke-width="5" stroke-linejoin="round" points="37.912,17.002 20.61,32.998 
				12.088,25.898 "/>
		</svg>
		<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received">
			<?php
			/**
			 * Filter the message shown after a checkout is complete.
			 *
			 * @since 2.2.0
			 *
			 * @param string         $message The message.
			 * @param WC_Order|false $order   The order created during checkout, or false if order data is not available.
			 */
			$message = apply_filters(
				'woocommerce_thankyou_order_received_text',
				__( 'Thank you. Your order has been received.', 'woocommerce' ),
				$order
			);
		
			echo '<h3>' . $message . '</h3>';
			?>
		</p>
	</div>
	<div id="order-recieved-content">
		<p>Hemos recibido tu solicitud <b><i>(N° de Pedido: #<?php echo $order->get_order_number(); ?>)</i></b>. Revisa tu correo para más detalles del pedido.</p>
		<?php $payment_method_title = $order->get_payment_method_title(); ?>
		<?php if (str_contains(strtolower($payment_method_title), 'bizum')) : ?>
			<p>Realizar el pago al número <b><i>603556733.</i></b></p>
		<?php elseif(str_contains(strtolower($payment_method_title), 'transferencia bancaria')) : ?>
			<?php 
				$payment_gateway = wc_get_payment_gateway_by_order( $order ); 
				$account_name = $payment_gateway->account_details[0]['account_name'];
				$account_number = $payment_gateway->account_details[0]['account_number'];
			?>
			<ul style="text-align: left;">
				<li><b>Nombre del beneficiario: <i><?php echo $account_name; ?></i></b></li>
				<li><b>Número de cuenta: <i><?php echo $account_number; ?></i></b></li>
			</ul>
		<?php endif; ?>	
		<p>Una vez hayamos recibido el pago, empezaremos a gestionar tu pedido lo más rapido posible.
		</p>
	</div>

</div>

