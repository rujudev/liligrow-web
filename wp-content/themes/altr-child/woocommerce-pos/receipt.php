<?php
/**
 * Sales Receipt Template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce-pos/receipt.php.
 * HOWEVER, this is not recommended , don't be surprised if your POS breaks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
	<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<style>
			html, body, ul, li, fieldset, address {
				font-family: sans-serif;
				font-size: 14px;
				margin: 0;
				padding: 0;
			}
			h1, h2, h3, h4, h5, h6 {
				margin: 0;
				padding: 0;
				font-weight: 600;
				line-height: 1.3;
			}
			h1 {
				font-size: 18px;
				margin-bottom: 15px;
			}
			h2 {
				font-size: 16px;
				margin-bottom: 12px;
			}
			h3 {
				font-size: 14px;
				margin-bottom: 10px;
			}
			h4 {
				font-size: 14px;
				margin-bottom: 8px;
			}
			h5 {
				font-size: 14px;
				margin-bottom: 6px;
			}
			h6 {
				font-size: 12px;
				margin-bottom: 4px;
			}

			html, body {
				height: 100%;
			}


			.sales-receipt {
				width: 100%;
				max-width: 100%;
				height: 100%;
				position: relative;
			}
			.header, .footer {
				text-align: center;
			}

			.header {
				margin-bottom: 10px;
			}

			.header img {
				max-width: 200px;
				height: auto;
			}

			.header h3 {
				text-transform: uppercase;
			}

			#info-details {
				display: flex;
				flex-direction: column;
				gap: .5em;
			}

			.order-details {
				max-width: 300px;
				margin: 0 auto;
				display: grid;
				padding: 10px 0;
				grid-template-areas: "order ."
									 "cashier date"
									 "method hour";
				gap: .5em 1.5em;
			}

			.order-details li {
				list-style: none;
				display: flex;
				justify-content: space-between;
			}

			.order-details .order {
				grid-area: order;
			}
			.order-details .date {
				grid-area: date;
			}
			.order-details .hour {
				grid-area: hour;
			}
			.order-details .cashier {
				grid-area: cashier;
			}
			.order-details .method {
				grid-area: method;
			}

			.order-totals {
				display: flex;
				flex-direction: column;
			}

			.order-totals div {
				display: flex;
				justify-content: space-between;
			}
			.order-totals div:not(#amount-tendered, #exchange) {
				margin-bottom: .5em;
			}

			.order-totals #total {
				align-items: center;
			}

			.order-totals #total #order-total-details {
				display: flex;
				flex-direction: column;
				align-items: flex-end;
				gap: .2em;
			}

			.order-totals #total #order-total-details .includes_tax {
				font-size: 10px;
				font-style: italic;
			}

			#pos_cash {
				padding-block: .5em;
				border-block: 1px dashed #ddd;
				display: flex;
				flex-direction: column;
				gap: .5em;
			}

			.footer {
				width: 100%;
				text-align: center;
			}

			/* Style for Order Details Table */
			table {
				width: 100%;
				margin-bottom: 10px;
				border-bottom: 1px dashed #ddd;
			}

			.order-totals,
			.order-details strong,
			table thead {
				text-transform: uppercase;
			}

			table thead tr th,
			table tbody tr td,
			table tfoot tr td {
				border: none;
				padding: 5px;
				text-align: left;
			}

			table thead tr th {
				font-weight: bold;
				border-block: 1px dashed #ddd;
			}
			table tfoot tr th {
				padding: 5px;
				text-align: right;
			}
			th:last-child, td:last-child {
				text-align: right;
			}
			table ul.wc-item-meta {
				padding: 0;
				list-style: none;
				margin-top: 5px !important;
			}
			table ul.wc-item-meta li {
				font-size: 10px;
			}

			table ul.wc-item-meta p {
				display: inline-block;
				margin: 0;
			}

			/* Style for Customer Details */
			.woocommerce-customer-details {
				margin-bottom: 20px;
			}
			.woocommerce-columns {
				display: flex;
				justify-content: space-between;
				margin-bottom: 10px;
				padding: 5px;
			}
			.woocommerce-column {
				flex: 0 0 calc(50% - 10px);
			}
			address {
				font-size: 12px;
				line-height: 1.4;
			}
			address p {
				margin-bottom: 3px;
			}
			.woocommerce-customer-details--phone,
			.woocommerce-customer-details--email {
				font-size: 12px;
				margin-bottom: 3px;
			}

			@media print {
				* {
					font-size: 10px;
				}

				table ul.wc-item-meta * {
					font-size: 7px;
					font-weight: 700;
				}
			}
		</style>
		<?php
		/**
		 * IMPORTANT!
		 * This hook adds the javascript to print the receipt.
		 */
		do_action( 'woocommerce_pos_receipt_head' );
		?>
	</head>
<body <?php body_class(); ?>>
<div class="sales-receipt">
	<div class="header">
	<?php 
		$direccion = get_option('woocommerce_store_address');
		$ciudad = get_option('woocommerce_store_city');
		$codigo_postal = get_option('woocommerce_store_postcode');
		$header_image = 665;

		if ( $header_image ) :
			$src = wp_get_attachment_url( $header_image );
			?>
			<picture>
				<img alt="Logo Liligrow" data-src="<?php echo $src; ?>" class="custom-logo lazyloaded" src="<?php echo $src; ?>">
				<noscript><img src="<?php echo $src; ?>" alt="Logo Liligrow" class="custom-logo" /></noscript>
			</picture>
			<!-- <img src="<?php //echo esc_url( $src ); ?>" alt="<?php //bloginfo( 'name' ); ?>"> -->
		<?php endif; ?>
		<h3><?php echo bloginfo( 'name' ); ?></h3>
		<div id="info-details">
			<span>Raúl Gil Esteve</span>
			<span>20051530S</span>
			<span><?php echo $direccion; ?></span>
			<span><?php echo $ciudad . ", " . $codigo_postal; ?></span>
			<span>603014125</span>
		</div>
	</div>

	<ul class="order-details">
		<li class="order">
			<strong><?php esc_html_e( 'Order:', 'woocommerce' ); ?></strong>
			<span>#<?php echo esc_html( $order->get_order_number() ); ?></span>
		</li>
		<li class="date">
			<strong><?php esc_html_e( 'Date', 'woocommerce' ); ?></strong>
			<span><?php echo esc_html( wc_format_datetime( $order->get_date_created(), 'd/m/Y' ) ); ?></span>
		</li>
		<li class="hour">
			<strong><?php echo esc_html_e( 'Hour', 'woocommerce'); ?></strong>
			<span><?php echo esc_html( wc_format_datetime( $order->get_date_created(), 'H:i:s' ) ); ?></span>
		</li>
		<?php
			// if order has meta value _pos_user, get the user id and display the user name
			$pos_user = $order->get_meta( '_pos_user' );
			
			if ( $pos_user ) {
				$user = get_user_by( 'id', $pos_user );
				$user_name = $user->display_name; ?>
				
				<li class="cashier">
					<strong><?php echo esc_html__( 'Cashier: ', 'woocommerce-pos' ); ?></strong>
					<span><?php echo esc_html( $user_name ); ?></span>
				</li> <?php
			}
			
			if ( $order->get_payment_method_title() ) : ?>
				<li class="method">
					<strong><?php esc_html_e( 'Paid in', 'woocommerce' ); ?>:</strong>
					<span><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></span>
				</li>
		<?php endif; ?>
	</ul>

	<table>
		<thead>
			<tr>
				<th>Qty</th>
				<th><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
				<!-- <th><?php // esc_html_e( 'Price', 'woocommerce' ); ?></th> -->
				<th><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $order->get_items() as $item_id => $item ) { ?>
			<?php 
				$product_id = $item->get_product_id();
				$variation_id = $item->get_variation_id();
				$product = $item->get_product(); // see link above to get $product info
				$product_name = $item->get_name();
				$quantity = $item->get_quantity();
				$subtotal = $item->get_subtotal();
				$total = $item->get_total();
				$tax = $item->get_subtotal_tax();
				$tax_class = $item->get_tax_class();
				$tax_status = $item->get_tax_status();
				$allmeta = $item->get_meta_data();
				$somemeta = $item->get_meta( '_whatever', true );
				$item_type = $item->get_type(); // e.g. "line_item", "fee"
			?>
			<tr>
				<td><?php echo esc_html( $item->get_quantity() ); ?></td>
				<td>
					<?php 
						echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $item->get_name(), $item, false ) );

						do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, false );

						wc_display_item_meta( $item );

						do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, false );
					?>
				</td>
				<td><?php echo wc_price( get_item_subtotal(['product_id' => $item->get_product()->id, 'quantity' => $item->get_quantity()], false) ); ?></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>

	<div class="order-totals">
		<?php $totals = $order->get_order_item_totals( ); ?>
		<?php 
			get_order_total_sum($order);
		?>
		<div id="subtotal">
			<strong><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></strong>
			<span><?php echo wp_kses_post( wc_price( get_order_subtotal_sum($order) ) ); ?></span>
		</div>
		<?php if ( wc_tax_enabled() ) : ?>
			<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
				<?php foreach ( $order->get_tax_totals() as $code => $tax ) : ?>
					<div id="tax">
						<strong><?php echo esc_html( $tax->label ); ?></strong>
						<span><?php echo wp_kses_post( wc_price( $tax->amount ) ); ?></span>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<div id="country-tax-or-vat">
					<strong><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></strong>
					<span><?php echo wp_kses_post( wc_price( $order->get_total_tax() ) ); ?></span>
				</div>
			<?php endif; ?>
		<?php endif; ?>
		<div id="total">
			<strong><?php esc_html_e( 'Total', 'woocommerce' ); ?></strong>
			<span><?php echo wc_price( get_order_total_sum($order) ); ?></span>
		</div>
		<?php if ( $order->get_payment_method() === 'pos_cash'): ?>
			<div id="pos_cash">
				<div id="amount-tendered">
					<strong>Efectivo</strong>
					<span><?php echo wp_kses_post( wc_price( $order->get_meta('_pos_cash_amount_tendered') ) );?></span>
				</div>
				<div id="exchange">
					<strong>Cambio</strong>
					<span><?php echo wp_kses_post( wc_price( $order->get_meta('_pos_cash_amount_tendered') - $order->get_total() ) );?></span>
				</div>
			</div>
		<?php endif; ?>
	</div>

<?php if ( $order->get_customer_note() ) : ?>
	<div class="customer-notes">
		<h4 class="section-title"><?php esc_html_e( 'Customer Notes', 'woocommerce-pos' ); ?></h4>
		<p><?php echo wp_kses_post( nl2br( $order->get_customer_note() ) ); ?></p>
	</div>
<?php endif; ?>

	<div class="footer">
		<p>
			<b><?php esc_html_e( 'Thank you for your purchase!', 'woocommerce-pos' ); ?></b>
		</p>
	</div>
</div>

</body>

<footer>
	<script>
		document.addEventListener("DOMContentLoaded",(function(){const r=this.querySelector("div.css-175oi2r.r-150rngu.r-eqz5dr.r-16y2uox.r-1wbh5a2.r-11yh6sk.r-1rnoaur.r-agouwx"),t=this.querySelectorAll(".order-totals #tax strong");t.length>0&&t.forEach((r=>{r.innerText+=" Incluido"})),r&&r.setAttribute("style","max-width: 80mm;")}));
	</script>
</footer>

</html>

