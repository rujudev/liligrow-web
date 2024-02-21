<?php

include_once( ABSPATH . 'wp-admin/includes/admin.php' );
require_once ABSPATH . 'vendor/autoload.php';
require_once WP_CONTENT_DIR . '/plugins/woocommerce-advanced-free-shipping/libraries/wp-conditions/conditions/wpc-condition.php';
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;
use Automattic\WooCommerce\StoreApi\Utilities\CartController;

class Custom_WPC_Conditions extends WPC_Condition {}

enum CurrencyPosOption: string
{
	case LEFT = 'left';
	case RIGHT = 'right';
	case LEFT_SPACE = 'left_space';
	case RIGHT_SPACE = 'right_space';
}

/**
 * Function describe for Altr child
 *
 * @package altr
 */
function altr_child_enqueue_styles()
{

	$parent_style = 'entr-stylesheet';

	$dep = array('bootstrap');
	if (class_exists('WooCommerce')) {
		$dep = array('bootstrap', 'entr-woo-stylesheet');
	}

	wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css', array('bootstrap'));
	wp_enqueue_style('altr-stylesheet', get_stylesheet_directory_uri() . '/style.css', $dep, wp_get_theme()->get('Version'));
	// wp_enqueue_style('hc-offcanvas-nav', get_stylesheet_directory_uri() . '/assets/css/hc-offcanvas-nav.carbon.min.css', array(), '1.0.0');
}

add_action('wp_enqueue_scripts', 'altr_child_enqueue_styles');

function altr_child_enqueue_scripts()
{
	wp_register_script('sweetalert-js', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', array(), null, true);
	wp_enqueue_script('sweetalert-js');
}

add_action('wp_enqueue_scripts', 'altr_child_enqueue_scripts');

/**
 * Set the content width based on enabled sidebar
 */
function entr_main_content_width_columns()
{

	$columns = '12';
	$hide_sidebar = get_post_meta(get_the_ID(), 'envo_extra_hide_sidebar', true);
	if (is_active_sidebar('entr-right-sidebar') && is_singular() && $hide_sidebar == 'on') {
		$columns = '12';
	} elseif (is_active_sidebar('entr-right-sidebar')) {
		$columns = $columns - 4;
	}

	echo absint($columns);
}

add_action('after_setup_theme', 'altr_setup');

function altr_setup()
{
	
	// Remove parent theme header fields
	remove_action('entr_header', 'entr_title_logo', 10);
	remove_action('entr_header', 'entr_menu', 20);
	remove_action('entr_header', 'entr_head_start', 25);
	remove_action('entr_header', 'entr_head_end', 80);
	remove_action('entr_header', 'entr_menu_button', 28);

	// Remove parent theme post thumbnail
	remove_action('entr_single_image', 'entr_featured_image', 10 );
	remove_action('entr_archive_image', 'entr_featured_image', 10 );
	remove_action('entr_page_content', 'entr_featured_image', 10 );

	// Remove parent theme post title
	remove_action( 'entr_single_title', 'entr_title', 20 );
	remove_action( 'entr_archive_title', 'entr_title', 20 );
	remove_action( 'entr_page_content', 'entr_title', 20 );

	// Create child theme header
	add_action('altr_header', 'altr_head_start', 25);
	add_action('altr_header', 'altr_head_end', 80);
	add_action('altr_header', 'altr_header_widget', 20);
	add_action('altr_header', 'altr_title_logo', 11);
	add_action('entr_header', 'altr_menu', 20);
	add_action('altr_header', 'altr_header_widget', 20);

	if (class_exists('WooCommerce')) {
		remove_action('woocommerce_cart_collaterals', 'woocommerce_cross_sell_display');
		remove_action('woocommerce_widget_shopping_cart_total', 'woocommerce_widget_shopping_cart_subtotal', 10);
		// re-position WooCommerce icons
		remove_action('entr_header', 'entr_header_cart', 30);
		remove_action('wp_footer', 'entr_cart_content', 30);
		remove_action('entr_header', 'entr_my_account', 40);
		remove_action('entr_header', 'entr_head_wishlist', 50);
		remove_action('entr_header', 'entr_head_compare', 60);

		// Custom actions
		remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10);

		add_action('altr_header', 'altr_header_cart', 30);
		add_action('altr_header', 'altr_cart_content', 30);
		add_action('altr_header', 'altr_my_account', 40);
		add_action('altr_header', 'entr_head_compare', 60);
		// add WooCommerce search field
		add_action('altr_header', 'altr_menu_search_widget', 20);

		// Custom
		add_action('custom_woocommerce_cross_sell', 'woocommerce_cross_sell_display');

		update_option('woocommerce_currency_pos', 'right');

		if (!in_array('flat_rate:3', WC()->shipping()->get_shipping_methods())) {
			add_action('woocommerce_after_shipping_rate', 'custom_customize_shipping_rate', 10, 2);
		}
	}

	// $conditions = WC()->session->get('shipping_methods_conditions');

    // foreach ($conditions as $condition) {
    //     if ($condition['condition'] === 'subtotal') {
    //         $min_subtotal_cart_free_shipping_value = $condition['value'];
    //         break;
    //     }
    // }
}

if ( !function_exists( 'altr_featured_image' ) ) :

	/**
	 * Generate featured image.
	 */
	add_action( 'entr_single_image', 'altr_featured_image', 10 );
	add_action( 'entr_archive_image', 'altr_featured_image', 10 );
	add_action( 'entr_page_content', 'altr_featured_image', 10 );

	function altr_featured_image() { ?>
		<div class="single-post-header">
			<span class="single-post-header__background <?php echo has_post_thumbnail() ? 'show' : "" ?>"></span>
			<?php
				if ( is_singular() ) {
					entr_thumb_img( 'full', '', false, true );
				} else {
					entr_thumb_img( 'full' );
				}

				altr_post_title();
			?>
		</div>
		<?php
	}

endif;

if ( !function_exists( 'altr_post_title' ) ) :
	function altr_post_title() {
		$title = get_post_meta( get_the_ID(), 'envo_hide_title', true );
		if ( $title != 'on' ) { ?>
			<div class="single-head" <?php echo has_post_thumbnail() ? 'style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #FFF;"' : "" ?> >
				<?php
				if ( is_singular() ) {
					the_title( '<h1 class="single-title">', '</h1>' );
				} else {
					the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
				}
				?>
				<time class="posted-on published" datetime="<?php the_time( 'Y-m-d' ); ?>"></time>
			</div>
			<?php
		}
	}
endif;

function new_woocommerce_instance() {
	return new Client(
		'https://liligrow.es',
		'ck_968f11dd925c7b4e39e8739606395e17753d940d',
		'cs_faa2de6e8fc676becdf65ed16bd4c46f1a97a90c',
		[
			'version' => 'wc/v3',
		]
	);
}

function order_array_desc($a, $b) {
	return $b->quantity - $a->quantity;
}

function woocommerce_rest_api_shortcode($atts, $results)
{
	// tengo los productos ya en $results, pero falta pintarlos
	$results = stripcslashes($results);
	$results = json_decode($results);
	$woocommerce = new_woocommerce_instance();

	try {
		?>
		<div class="woocommerce">
			<ul class="products top_sellers">
				<?php foreach ($results as $result) { ?>
					<?php
					$product = wc_get_product($result->product_id);

					if ($product): ?>
						<li id="product-<?php echo $product->get_id(); ?>" <?php wc_product_class('', $product); ?>
							style="width: auto;">
							<a href="<?php echo $product->get_permalink(); ?>">
								<?php echo $product->get_image(); ?>
								<div class="product-details">
									<h2 class="woocommerce-loop-product__title">
										<?php echo $product->get_name(); ?>
									</h2>
									<div class="star-rating">
										<span style="width: <?php echo $product->get_average_rating() * 20; ?>%">
											<strong class="rating">
												<?php echo $product->get_average_rating(); ?>
											</strong>
										</span>
									</div>
								</div>
							</a>
							<?php echo apply_filters('custom_simple_product_get_price_html', $product); ?>
							<?php custom_woocommerce_template_add_to_cart($product); ?>
						</li>

						<?php
					endif;
					?>
				<?php } ?>
			</ul>
		</div>
		<?php
	} catch (HttpClientException $e) {
		echo $e->getMessage();
	}
}

add_action('wp_ajax_get_top_sellers', 'get_top_sellers');
add_action('wp_ajax_nopriv_get_top_sellers', 'get_top_sellers');

function get_top_sellers() {
    $top_sellers = $_POST['top_sellers'];

	woocommerce_rest_api_shortcode(null, $top_sellers);
}

add_shortcode('woo_rest_api', 'woocommerce_rest_api_shortcode');

function custom_woocommerce_template_add_to_cart($product, $args = [])
{
	if ($product) {
		$defaults = array(
			'quantity' => 1,
			'class' => implode(
				' ',
				array_filter(
					array(
						'button',
						wc_wp_theme_get_element_class_name('button'),
						// escaped in the template.
						'product_type_' . $product->get_type(),
						$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
						$product->supports('ajax_add_to_cart') && $product->is_purchasable() && $product->is_in_stock() ? 'ajax_add_to_cart' : '',
					)
				)
			),
			'attributes' => array(
				'data-product_id' => $product->get_id(),
				'data-product_sku' => $product->get_sku(),
				'aria-label' => $product->add_to_cart_description(),
				'aria-describedby' => $product->add_to_cart_aria_describedby(),
				'rel' => 'nofollow',
			),
		);

		$args = apply_filters('woocommerce_loop_add_to_cart_args', wp_parse_args($args, $defaults), $product);

		if (!empty($args['attributes']['aria-describedby'])) {
			$args['attributes']['aria-describedby'] = wp_strip_all_tags($args['attributes']['aria-describedby']);
		}

		if (isset($args['attributes']['aria-label'])) {
			$args['attributes']['aria-label'] = wp_strip_all_tags($args['attributes']['aria-label']);
		}

		echo apply_filters(
			'woocommerce_loop_add_to_cart_link',
			// WPCS: XSS ok.
			sprintf(
				'<a href="%s" data-quantity="%s" class="%s" %s><span>%s</span></a>',
				esc_url($product->add_to_cart_url()),
				esc_attr(isset($args['quantity']) ? $args['quantity'] : 1),
				esc_attr(isset($args['class']) ? $args['class'] : 'button'),
				isset($args['attributes']) ? wc_implode_html_attributes($args['attributes']) : '',
				esc_html($product->add_to_cart_text())
			),
			$product,
			$args
		);
	}
}

/**
 * Title, logo code
 */
function altr_title_logo()
{
	?>
	<div class="site-heading">
		<div class="site-branding-logo">
			<a href="https://liligrow.es/ruta-desarrollo-pagina-inicio<?php //echo esc_url(home_url('/')); ?>" rel="home">
				<figure>
					<picture>
						<source media="(max-width: 767px)"
							srcset="https://www.liligrow.es/wp-content/uploads/2023/07/white_logo-scaled.webp" />
						<img src="https://www.liligrow.es/wp-content/uploads/2023/07/black_logo-scaled.webp"
							alt="Logo Liligrow" class="custom-logo" />
					</picture>
				</figure>
			</a>
		</div>
		<div class="site-branding-text">
			<?php if (is_front_page()): ?>
				<h1 class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
						<?php bloginfo('name'); ?>
					</a>
				</h1>
			<?php else: ?>
				<p class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
						<?php bloginfo('name'); ?>
					</a>
				</p>
			<?php endif; ?>

			<?php
			$description = get_bloginfo('description', 'display');
			if ($description || is_customize_preview()):
				?>
				<p class="site-description">
					<?php echo esc_html($description); ?>
				</p>
			<?php endif; ?>
		</div><!-- .site-branding-text -->
	</div>
	<?php
}

/**
 * Menu position change
 */
function altr_menu()
{
	?>
	<div class="menu-heading">
		<nav id="site-navigation" class="navbar navbar-default">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'main_menu',
					'depth' => 5,
					'container_id' => 'theme-menu',
					'container' => 'div',
					'container_class' => 'menu-container',
					'menu_class' => 'nav navbar-nav navbar-' . get_theme_mod('menu_position', 'left'),
					'fallback_cb' => 'Entr_WP_Bootstrap_Navwalker::fallback',
					'walker' => new Entr_WP_Bootstrap_Navwalker(),
				)
			);
			?>
		</nav>
	</div>
	<?php
}

if (!function_exists('altr_menu_button')) {
	add_action('altr_header', 'altr_menu_button', 10);
	/**
	 * Mobile menu button
	 */
	function altr_menu_button()
	{
		?>
		<div class="menu-button visible-xs">
			<div class="navbar-header">
				<a href="#" id="main-menu-panel" class="toggle menu-panel" data-panel="main-menu-panel">
					<span></span>
				</a>
			</div>
		</div>
		<?php
	}
}

/**
 * Create WooCommerce search filed in header
 */
function altr_menu_search_widget()
{
	?>
	<div class="menu-search-widget">
		<?php the_widget('WC_Widget_Product_Search', 'placeholder="Buscar..."'); ?>
	</div>
	<?php
}

/**
 * Add header widget area
 */
function altr_header_widget()
{
	?>
	<div class="header-widget-area">
		<?php if (is_active_sidebar('altr-header-area')) { ?>
			<div class="site-heading-sidebar">
				<?php dynamic_sidebar('altr-header-area'); ?>
			</div>
		<?php } ?>
	</div>
	<?php
}

if (!function_exists('altr_my_account')) {
	function altr_my_account()
	{
		$login_link = get_permalink(get_option('woocommerce_myaccount_page_id'));
		?>
		<div class="header-my-account">
			<a href="<?php echo esc_url($login_link); ?>" data-tooltip="<?php esc_attr_e('My Account', 'entr'); ?>"
				title="<?php esc_attr_e('My Account', 'entr'); ?>">
				<i class="la la-user"></i>
			</a>
		</div>
		<?php
	}
}


function altr_head_start()
{
	?>
	<div class="header-right">
		<div class="settings">
			<?php
}

function altr_head_end()
{
	?>
		</div>
	</div>
	<?php
}

add_action('widgets_init', 'altr_widgets_init');

/**
 * Register the Sidebars
 */
function altr_widgets_init()
{
	register_sidebar(
		array(
			'name' => esc_html__('Header Section', 'altr'),
			'id' => 'altr-header-area',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<div class="widget-title"><h3>',
			'after_title' => '</h3></div>',
		)
	);
	register_sidebar(
		array(
			'name' => esc_html__('Top bar', 'altr'),
			'id' => 'altr-top-bar',
			'before_widget' => '<div id="%1$s" class="widget %2$s col-sm-12">',
			'after_widget' => '</div>',
			'before_title' => '<div class="widget-title"><h3>',
			'after_title' => '</h3></div>',
		),
	);
}


/**
 * Create top bar widget area
 * */
add_action('altr_top_bar', 'altr_top_bar');

function altr_top_bar()
{
	$conditions = get_option('free-shipping-conditions');

    foreach ($conditions as $condition) {
        if ($condition['condition'] === 'subtotal') {
            $min_subtotal_cart_free_shipping_value = $condition['value'];
            break;
        }
    }
    ?>
	<div class="top-bar-section container-fluid">
		<div class="container">
			<div id="minors-sale-info-container">
				<svg viewBox="0 0 63 53" version="1.1" class="restricted-sale-icon">
					<g id="outside-elements" transform="translate(-0.5,-0.5)" fill="currentColor" stroke="#1e1e1e"
						stroke-width="3">
						<rect ry="10" width="59px" x="2.5" height="49px" y="2.5" stroke-width="2" />
						<rect y="3.5" height="47px" width="56px" x="4" rx="11" ry="8.5" stroke-width="2" />
					</g>
					<g id="inside-elements" transform="translate(8,-5.5009019)">
						<path
							d="M 14,29 H 11 V 26 A 1,1 0 0 0 10,25 H 6 a 1,1 0 0 0 -1,1 v 3 H 2 a 1,1 0 0 0 -1,1 v 4 a 1,1 0 0 0 1,1 h 3 v 3 a 1,1 0 0 0 1,1 h 4 a 1,1 0 0 0 1,-1 v -3 h 3 a 1,1 0 0 0 1,-1 v -4 a 1,1 0 0 0 -1,-1 z"
							id="path15" inkscape:connector-curvature="0" />
						<path
							d="m 27,19 h -4 a 1,1 0 0 0 -0.71,0.29 l -6,6 a 1,1 0 0 0 0,1.42 l 3,3 a 1,1 0 0 0 1.42,0 L 22,28.41 V 44 a 1,1 0 0 0 1,1 h 4 a 1,1 0 0 0 1,-1 V 20 a 1,1 0 0 0 -1,-1 z"
							id="path9" inkscape:connector-curvature="0" />
						<g id="eight-number">
							<path d="m 43.07,30.82 a 7,7 0 1 0 -10.14,0 8,8 0 1 0 10.14,0 z" id="outside-stroke"
								inkscape:connector-curvature="0" />
							<g id="inside-circles">
								<circle cx="38" cy="26" width="20" height="10" r="2" id="circle11" fill="currentColor" />
								<circle cx="38" cy="37" width="20" height="10" r="3" id="circle13" fill="currentColor" />
							</g>
						</g>
					</g>
				</svg>
				<h5 id="sale-prohibited-to-minors-txt">Prohibida la venta a menores</h5>
			</div>
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" class="free-shipping-track-icon">
				<g data-name="Free Delivery">
					<path
						d="M16.716 50.407a6.538 6.538 0 1 1 6.538-6.538 6.545 6.545 0 0 1-6.538 6.538zm0-11.076a4.538 4.538 0 1 0 4.538 4.538 4.543 4.543 0 0 0-4.538-4.538zM5.761 34.137a1 1 0 0 1-1-1v-8.5a1 1 0 0 1 1-1h4.6a1 1 0 0 1 0 2h-3.6v7.5a1 1 0 0 1-1 1z" />
					<path
						d="M9.047 29.886H5.761a1 1 0 1 1 0-2h3.286a1 1 0 0 1 0 2zm9.402 4.251a1 1 0 0 1-.679-.266l-2.917-2.7v1.964a1 1 0 0 1-2 0v-8.5a1 1 0 0 1 1-1h2.47a3.126 3.126 0 0 1 .082 6.251l2.723 2.514a1 1 0 0 1-.679 1.734zm-3.6-6.251h1.47a1.126 1.126 0 0 0 0-2.252h-1.47zm11.692 6.251h-4.6a1 1 0 0 1-1-1v-8.5a1 1 0 0 1 1-1h4.6a1 1 0 0 1 0 2h-3.6v6.5h3.6a1 1 0 0 1 0 2z" />
					<path
						d="M25.172 29.886h-3.227a1 1 0 0 1 0-2h3.227a1 1 0 0 1 0 2zm9.46 4.251h-4.595a1 1 0 0 1-1-1v-8.5a1 1 0 0 1 1-1h4.595a1 1 0 0 1 0 2h-3.595v6.5h3.595a1 1 0 1 1 0 2z" />
					<path
						d="M33.264 29.886h-3.227a1 1 0 0 1 0-2h3.227a1 1 0 0 1 0 2zm10.344 15.057h-21.35a1 1 0 1 1 0-2h21.35a1 1 0 0 1 0 2zm-32.43 0h-3.42a1 1 0 0 1-1-1v-2.05H5.8a1 1 0 0 1-1-1v-3.16a1 1 0 0 1 2 0v2.16h.96a1 1 0 0 1 1 1v2.05h2.42a1 1 0 0 1 0 2z" />
					<path
						d="M59.238 44.943h-4.55a1 1 0 0 1 0-2h4.55a1 1 0 0 0 1-1V29.8l-6.565-9.852H38.938a1 1 0 0 1-1-1v-3.35H2.761a1 1 0 0 1 0-2h36.177a1 1 0 0 1 1 1v3.35h14.27a1 1 0 0 1 .832.445l7.03 10.55a1 1 0 0 1 .168.555v12.45a3 3 0 0 1-3 2.995Z" />
					<path
						d="M23.243 20.456h-2.561a1 1 0 0 1 0-2h2.561a1 1 0 0 1 0 2zm37.995 10.307h-15.27a1 1 0 0 1-1-1v-8.27a1 1 0 0 1 1-1h9.94a1 1 0 0 1 0 2h-8.94v6.27h14.27a1 1 0 0 1 0 2zM57.845 38.1a1 1 0 0 1-1-1v-1.79a1 1 0 0 1 1-1h3.393a1 1 0 0 1 0 2h-2.393v.79a1 1 0 0 1-1 1zm-8.698 12.307a6.538 6.538 0 1 1 6.539-6.538 6.545 6.545 0 0 1-6.539 6.538zm0-11.076a4.538 4.538 0 1 0 4.539 4.538 4.544 4.544 0 0 0-4.539-4.538zM16.72 45.37a1.5 1.5 0 0 1-1.06-.44 1.516 1.516 0 0 1-.44-1.06 1.358 1.358 0 0 1 .03-.29 1.568 1.568 0 0 1 .08-.29 2.013 2.013 0 0 1 .14-.25 1.059 1.059 0 0 1 .19-.23 1.368 1.368 0 0 1 .22-.19 2.148 2.148 0 0 1 .26-.14c.09-.03.19-.06.28-.08a1.516 1.516 0 0 1 1.36.41 1.018 1.018 0 0 1 .18.23 1.229 1.229 0 0 1 .14.25 1.612 1.612 0 0 1 .09.29 1.358 1.358 0 0 1 .03.29 1.5 1.5 0 0 1-1.5 1.5z" />
					<path
						d="M49.149 45.37a1.524 1.524 0 0 1-.3-.03 1.551 1.551 0 0 1-.281-.09 1.531 1.531 0 0 1-.259-.13 1.911 1.911 0 0 1-.22-.19 1.422 1.422 0 0 1-.191-.23 2.292 2.292 0 0 1-.139-.26 2.123 2.123 0 0 1-.08-.28 1.372 1.372 0 0 1 0-.58 2.123 2.123 0 0 1 .08-.28 2.292 2.292 0 0 1 .139-.26 1.093 1.093 0 0 1 .191-.23 1.911 1.911 0 0 1 .22-.19c.089-.05.169-.1.259-.14s.191-.06.281-.08a1.46 1.46 0 0 1 .589 0 2.254 2.254 0 0 1 .281.08 2.134 2.134 0 0 1 .259.14 2.03 2.03 0 0 1 .231.19 1.018 1.018 0 0 1 .18.23 1.3 1.3 0 0 1 .14.26 1.413 1.413 0 0 1 0 1.14 1.729 1.729 0 0 1-.32.49 2.03 2.03 0 0 1-.231.19 1.256 1.256 0 0 1-.259.13 1.3 1.3 0 0 1-.281.09 1.488 1.488 0 0 1-.289.03zm-10.304-3.848H25.172a1 1 0 0 1 0-2h12.673V23.493a1 1 0 1 1 2 0v17.029a1 1 0 0 1-1 1zM17.151 20.456H4.471a1 1 0 0 1 0-2h12.68a1 1 0 0 1 0 2zm-5.527 18.277H2.761a1 1 0 0 1 0-2h8.863a1 1 0 1 1 0 2z" />
				</g>
			</svg>
			<h5 id="free-shipping-text"></h5>
		</div>
	</div>
	<script>
		const saleProhibitedToMinorsTxt = document.querySelector('#sale-prohibited-to-minors-txt');
		const deliveryInfoContainerTxt = document.querySelector('#free-shipping-text');

		function media(args, callback) {
			function handle(queryEvent) {
				callback(queryEvent);
			}

			const query = window.matchMedia(args);
			query.addEventListener('change', handle);
			handle(query);
		}

		media('(max-width: 767px)', function (event) {
			if (event.matches) {
				saleProhibitedToMinorsTxt.classList.add('hide');
				deliveryInfoContainerTxt.textContent = "Envío gratis desde <?php echo $min_subtotal_cart_free_shipping_value; ?>€*";

				return;
			}

			saleProhibitedToMinorsTxt.classList.remove('hide');
			deliveryInfoContainerTxt.textContent = "Envío gratis desde <?php echo $min_subtotal_cart_free_shipping_value; ?>€ a península";
		})
	</script>
<?php }

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if (!function_exists('chld_thm_cfg_locale_css')):
	function chld_thm_cfg_locale_css($uri)
	{
		if (empty($uri) && is_rtl() && file_exists(get_template_directory() . '/rtl.css'))
			$uri = get_template_directory_uri() . '/rtl.css';
		return $uri;
	}
endif;
add_filter('locale_stylesheet_uri', 'chld_thm_cfg_locale_css');

// END ENQUEUE PARENT ACTION

if (!function_exists('get_product_categories_list ')):
	function get_product_categories_list($list_class = null)
	{
		$orderby = 'name';
		$order = 'asc';
		$hide_empty = true;
		$parent = 0;
		$cat_args = array(
			'orderby' => $orderby,
			'order' => $order,
			'parent' => $parent,
			'hide_empty' => $hide_empty,
		);
		$product_categories = get_terms('product_cat', $cat_args);

		if (!empty($product_categories)) { ?>
			<ul <?php echo ($list_class) ? 'class="' . $list_class . '"' : '' ?>>
				<?php foreach ($product_categories as $key => $category) { ?>
					<li>
						<a href="<?php echo get_term_link($category); ?>">
							<span>
								<?php echo $category->name; ?>
							</span>
						</a>
					</li>
				<?php } ?>
			</ul>
		<?php }
	}
endif;

if (!function_exists('woo_hide_product_categories_widget')) {
	remove_action('woocommerce_product_categories_widget_args', 'woo_hide_product_categories_widget', 20);
	do_action('woocommerce_product_categories_widget_args', 'woo_hide_product_categories_widget');

	function woo_hide_product_categories_widget($list_args)
	{
		$list_args['hide_empty'] = 1;
		return $list_args;
	}
}

function redirect_after_logout($logout_url, $redirect)
{
	return $logout_url . '&amp;redirect_to=' . home_url();
}
add_filter('logout_url', 'redirect_after_logout', 10, 2);

/**
 * Modificaciones a menu de mi cuenta
 */
function my_account_menu_order($args)
{
	$menuOrder = array(
		'dashboard' => __('Dashboard', 'woocommerce'),
		'orders' => __('Orders', 'woocommerce'),
		'edit-address' => __('Addresses', 'woocommerce'),
		'edit-account' => __('Account details', 'woocommerce'),
		'customer-logout' => __('Logout', 'woocommerce'),
	);
	return $menuOrder;
}
add_filter('woocommerce_account_menu_items', 'my_account_menu_order');

function custom_get_account_menu_items() {
	$menu_items = wc_get_account_menu_items();
	$icons = [
		'dashboard'		  => '<i class="las la-home la-lg"></i>',
		'orders'          => '<i class="las la-luggage-cart la-lg"></i>',
		'downloads'       => '<i class="las la-download la-lg"></i>',
		'edit-address'    => '<i class="las la-address-card la-lg"></i>',
		'payment-methods' => '<i class="lab la-cc-visa la-lg"></i>',
		'edit-account'    => '<i class="las la-user-edit la-lg"></i>',
		'customer-logout' => '<i class="las la-sign-out-alt la-lg"></i>'
	];

	foreach($menu_items as $item_key => $label) { ?>
		<li class="<?php echo wc_get_account_menu_item_classes( $item_key ); ?>">
			<a href="<?php echo esc_url( wc_get_account_endpoint_url( $item_key ) ); ?>">
				<span>
					<?php echo esc_html( $label ); ?>
				</span>
				<?php echo $icons[$item_key]; ?>
			</a>
		</li>
	<?php

	}
}

add_action( 'get_account_menu_items', 'custom_get_account_menu_items');

/**
 * Modificaciones al carrito
 */

if ( !function_exists('altr_cart_link') ) {

	function altr_cart_link()
	{
		?>
		<a class="mini-cart-contents" data-tooltip="<?php esc_attr_e('Cart', 'entr'); ?>"
			title="<?php esc_attr_e('Cart', 'entr'); ?>">
			<div class="mini-cart-icon-container">
				<svg viewBox="0 0 35.714626 32.204663" height="32.204662" width="35.714626">
					<g transform="matrix(0.13333333,0,0,-0.13333333,-13.678271,116.30363)" id="g2062">
						<g transform="translate(-13.482968,2.9962151)" id="g9"
							stroke="#1e1e1e"
							stroke-width="4.74749994"
							stroke-miterlimit="4"
							stroke-dasharray="none"
							stroke-opacity="1" >
							<path fill="#ffffff" fill-opacity="1" fill-rule="nonzero" stroke="#1e1e1e" stroke-linejoin="miter"
								stroke-miterlimit="4" stroke-dasharray="none"
								d="m 226.578,700.934 h 126.59 c 0.215,0 0.422,0.007 0.637,0.027 0.066,0.012 0.144,0.019 0.222,0.031 0.129,0.02 0.254,0.039 0.391,0.059 0.09,0.019 0.176,0.039 0.266,0.058 0.105,0.028 0.214,0.059 0.332,0.086 0.086,0.032 0.175,0.059 0.273,0.09 0.098,0.028 0.195,0.067 0.301,0.106 0.09,0.039 0.176,0.07 0.265,0.109 0.098,0.047 0.196,0.098 0.293,0.145 0.079,0.039 0.165,0.078 0.243,0.128 0.109,0.059 0.207,0.118 0.304,0.184 0.067,0.039 0.137,0.078 0.203,0.129 0.137,0.098 0.274,0.195 0.403,0.293 0.019,0.027 0.047,0.039 0.078,0.066 0.156,0.129 0.301,0.254 0.437,0.403 0.051,0.039 0.09,0.086 0.129,0.125 0.098,0.109 0.196,0.207 0.282,0.312 0.05,0.059 0.089,0.117 0.136,0.176 0.078,0.098 0.157,0.207 0.227,0.305 0.047,0.066 0.098,0.136 0.137,0.214 0.066,0.098 0.125,0.196 0.183,0.293 0.051,0.079 0.09,0.157 0.129,0.243 0.047,0.097 0.098,0.195 0.145,0.293 0.039,0.089 0.078,0.175 0.117,0.265 0.039,0.098 0.07,0.196 0.109,0.293 0.028,0.098 0.067,0.196 0.098,0.293 0.027,0.098 0.047,0.195 0.066,0.301 0.031,0.098 0.059,0.195 0.078,0.305 0,0.007 0,0.019 0.012,0.027 l 20.399,106.543 c 0.371,1.945 -0.145,3.937 -1.395,5.461 -1.262,1.512 -3.125,2.402 -5.098,2.402 H 209.586 l -3.918,20.352 c -2.723,14.187 -15.184,24.48 -29.629,24.48 h -49.617 c -3.645,0 -6.602,-2.957 -6.602,-6.609 0,-3.652 2.957,-6.61096 6.602,-6.61096 h 49.617 c 8.117,0 15.117,-5.78404 16.652,-13.76004 l 24.297,-126.457 c -8.175,-3.672 -13.879,-11.883 -13.879,-21.414 0,-12.942 10.528,-23.469 23.469,-23.469 h 126.59 c 3.652,0 6.613,2.961 6.613,6.613 0,3.653 -2.961,6.61 -6.613,6.61 h -126.59 c -5.656,0 -10.242,4.601 -10.242,10.246 0,5.652 4.586,10.254 10.242,10.254"
								id="top" />
							<path fill="#ffffff" fill-opacity="1" fill-rule="nonzero" stroke="#1e1e1e" stroke-miterlimit="4"
								stroke-dasharray="none"
								d="m 246.078,658.59804 c -7.488,0 -13.555,-6.067 -13.555,-13.555 0,-7.48 6.067,-13.547 13.555,-13.547 7.481,0 13.547,6.067 13.547,13.547 0,7.488 -6.066,13.555 -13.547,13.555"
								id="left-wheel" />
							<path fill="#ffffff" fill-opacity="1" fill-rule="nonzero" stroke="#1e1e1e" stroke-miterlimit="4"
								stroke-dasharray="none"
								d="m 327.387,658.59804 c -7.489,0 -13.551,-6.067 -13.551,-13.555 0,-7.48 6.062,-13.547 13.551,-13.547 7.48,0 13.554,6.067 13.554,13.547 0,7.488 -6.074,13.555 -13.554,13.555"
								id="right-wheel" />
							<path fill="#1e1e1e" fill-opacity="1" fill-rule="nonzero" stroke="#1e1e1e" stroke-linejoin="round"
								stroke-miterlimit="4" stroke-dasharray="none" id="weed-sheet"
								d="m 252.398,744.76748 c 0.985,-0.028 1.954,-0.067 2.911,-0.137 0.476,-0.039 0.957,-0.059 1.433,-0.098 0.481,-0.027 0.949,-0.066 1.418,-0.105 l 0.703,-0.071 0.703,-0.058 c 0.469,-0.039 0.938,-0.078 1.395,-0.125 3.703,-0.363 7.305,-0.793 10.859,-1.192 0.45,-0.058 0.891,-0.129 1.34,-0.156 0.438,-0.051 0.887,-0.098 1.328,-0.148 0.887,-0.125 1.778,-0.176 2.657,-0.293 1.472,-0.176 2.949,-0.391 4.433,-0.664 l -0.344,0.175 c -0.273,0.157 -0.546,0.313 -0.828,0.469 -0.558,0.313 -1.105,0.637 -1.633,0.977 -1.054,0.683 -2.097,1.398 -3.085,2.168 -0.489,0.39 -0.977,0.781 -1.454,1.183 -0.48,0.399 -0.949,0.809 -1.406,1.231 -1.816,1.699 -3.555,3.484 -5.226,5.32 -3.329,3.692 -6.336,7.656 -8.973,11.895 -1.32,2.121 -2.539,4.316 -3.652,6.593 -0.547,1.153 -1.075,2.313 -1.555,3.504 -0.242,0.598 -0.477,1.192 -0.692,1.817 -0.109,0.304 -0.226,0.617 -0.324,0.929 -0.097,0.313 -0.203,0.625 -0.293,0.946 0.313,-0.137 0.606,-0.274 0.899,-0.43 0.304,-0.137 0.586,-0.293 0.879,-0.449 0.578,-0.293 1.132,-0.613 1.691,-0.938 1.113,-0.633 2.188,-1.297 3.242,-1.992 1.055,-0.672 2.09,-1.367 3.094,-2.09 l 1.516,-1.074 c 0.488,-0.359 0.984,-0.723 1.472,-1.094 3.907,-2.949 7.578,-6.054 11.106,-9.258 0.429,-0.41 0.867,-0.808 1.316,-1.199 0.442,-0.402 0.86,-0.82 1.301,-1.222 0.859,-0.821 1.738,-1.621 2.566,-2.461 0.422,-0.418 0.84,-0.84 1.25,-1.258 0.41,-0.43 0.813,-0.871 1.211,-1.309 0.391,-0.449 0.793,-0.89 1.184,-1.34 0.39,-0.449 0.762,-0.918 1.14,-1.386 0.743,-0.946 1.477,-1.895 2.18,-2.91 0.156,-0.223 0.301,-0.45 0.457,-0.672 -0.769,2.234 -1.347,4.48 -1.758,6.718 -0.507,2.743 -0.761,5.489 -0.82,8.231 -0.039,2.734 0.031,5.48 0.176,8.223 0.305,5.48 0.969,10.968 2.101,16.445 0.137,0.695 0.301,1.379 0.45,2.062 0.164,0.684 0.32,1.368 0.507,2.059 0.34,1.367 0.723,2.734 1.153,4.113 0.429,1.367 0.887,2.742 1.414,4.11 0.519,1.367 1.086,2.746 1.789,4.113 0.703,-1.367 1.269,-2.746 1.785,-4.113 0.527,-1.368 0.988,-2.743 1.418,-4.11 0.43,-1.379 0.809,-2.746 1.152,-4.113 0.184,-0.691 0.34,-1.375 0.508,-2.059 0.145,-0.683 0.313,-1.367 0.449,-2.062 1.133,-5.477 1.797,-10.965 2.098,-16.445 0.149,-2.743 0.215,-5.489 0.176,-8.223 -0.059,-2.742 -0.313,-5.488 -0.82,-8.231 -0.411,-2.238 -0.985,-4.484 -1.758,-6.718 0.156,0.222 0.304,0.449 0.461,0.672 0.703,1.015 1.433,1.964 2.175,2.91 0.383,0.468 0.754,0.937 1.145,1.386 0.391,0.45 0.781,0.891 1.18,1.34 0.402,0.438 0.8,0.879 1.211,1.309 0.41,0.418 0.832,0.84 1.25,1.258 0.832,0.84 1.711,1.64 2.57,2.461 0.437,0.402 0.859,0.82 1.297,1.222 0.449,0.391 0.89,0.789 1.32,1.199 3.524,3.204 7.195,6.309 11.102,9.258 0.488,0.371 0.988,0.735 1.476,1.094 l 1.512,1.074 c 1.008,0.723 2.043,1.418 3.098,2.09 1.054,0.695 2.129,1.359 3.242,1.992 0.554,0.325 1.113,0.645 1.687,0.938 0.293,0.156 0.578,0.312 0.879,0.449 0.293,0.156 0.586,0.293 0.899,0.43 -0.098,-0.321 -0.196,-0.633 -0.293,-0.946 -0.094,-0.312 -0.215,-0.625 -0.321,-0.929 -0.214,-0.625 -0.449,-1.219 -0.691,-1.817 -0.481,-1.191 -1.008,-2.351 -1.555,-3.504 -1.113,-2.277 -2.336,-4.472 -3.652,-6.593 -2.637,-4.239 -5.645,-8.203 -8.977,-11.895 -1.668,-1.836 -3.406,-3.621 -5.222,-5.32 -0.461,-0.422 -0.93,-0.832 -1.407,-1.231 -0.48,-0.402 -0.968,-0.793 -1.464,-1.183 -0.977,-0.77 -2.024,-1.485 -3.079,-2.168 -0.527,-0.34 -1.074,-0.664 -1.628,-0.977 -0.286,-0.156 -0.559,-0.312 -0.84,-0.469 l -0.332,-0.175 c 1.484,0.273 2.957,0.488 4.433,0.664 0.879,0.117 1.766,0.168 2.657,0.293 0.441,0.05 0.886,0.097 1.328,0.148 0.449,0.027 0.886,0.098 1.336,0.156 3.554,0.399 7.16,0.829 10.859,1.192 0.461,0.047 0.93,0.086 1.398,0.125 l 0.704,0.058 0.703,0.071 c 0.468,0.039 0.937,0.078 1.414,0.105 0.48,0.039 0.957,0.059 1.437,0.098 0.957,0.07 1.922,0.109 2.91,0.137 0.985,0.031 1.981,0.058 3.02,0 -0.727,-0.723 -1.496,-1.375 -2.27,-2.012 -0.789,-0.625 -1.601,-1.199 -2.41,-1.758 -1.652,-1.102 -3.371,-2.09 -5.136,-2.949 -3.547,-1.727 -7.297,-3.016 -11.172,-3.848 -0.969,-0.215 -1.953,-0.371 -2.93,-0.527 -0.488,-0.086 -0.988,-0.145 -1.484,-0.203 -0.489,-0.059 -0.989,-0.11 -1.485,-0.149 -1.984,-0.164 -3.984,-0.136 -5.988,0.129 -0.496,0.039 -0.996,0.137 -1.492,0.235 -0.469,0.085 -0.949,0.183 -1.418,0.292 0.332,-0.253 0.656,-0.507 0.976,-0.761 0.215,-0.188 0.422,-0.364 0.637,-0.547 0.203,-0.188 0.418,-0.363 0.625,-0.547 l 1.25,-1.113 c 1.648,-1.485 3.27,-3.008 4.871,-4.629 0.801,-0.813 1.602,-1.633 2.402,-2.5 l 0.598,-0.656 c 0.195,-0.223 0.391,-0.457 0.594,-0.684 0.391,-0.457 0.793,-0.918 1.172,-1.445 -0.633,0.031 -1.258,0.097 -1.864,0.195 -0.605,0.109 -1.222,0.227 -1.808,0.383 -0.586,0.156 -1.18,0.32 -1.746,0.527 -0.285,0.098 -0.567,0.203 -0.86,0.301 -0.285,0.109 -0.558,0.226 -0.839,0.344 -2.227,0.937 -4.309,2.148 -6.243,3.554 l -0.722,0.528 -0.692,0.554 c -0.468,0.371 -0.929,0.754 -1.367,1.153 -0.215,0.195 -0.43,0.41 -0.644,0.617 -0.207,0.203 -0.422,0.418 -0.618,0.644 -0.203,0.215 -0.398,0.438 -0.593,0.665 -0.196,0.234 -0.371,0.468 -0.559,0.703 -0.723,0.957 -1.367,1.992 -1.894,3.113 -0.137,0.312 -0.274,0.625 -0.399,0.949 l 0.32,-5.644 0.129,-2.207 0.059,-1.114 0.039,-0.558 v -0.625 c 0.008,-0.43 -0.051,-0.848 -0.09,-1.27 -0.008,-0.105 -0.027,-0.215 -0.058,-0.312 l -0.059,-0.313 -0.066,-0.32 c -0.02,-0.098 -0.04,-0.207 -0.079,-0.313 -0.128,-0.402 -0.234,-0.82 -0.421,-1.211 -0.086,-0.195 -0.157,-0.402 -0.254,-0.597 l -0.313,-0.567 c -0.047,-0.097 -0.098,-0.195 -0.156,-0.281 l -0.184,-0.273 c -0.117,-0.176 -0.234,-0.364 -0.363,-0.539 l -0.418,-0.508 -0.207,-0.254 -0.234,-0.234 -0.469,-0.458 c -0.164,-0.148 -0.332,-0.285 -0.508,-0.429 l -0.254,-0.207 c -0.086,-0.067 -0.183,-0.125 -0.273,-0.196 l -0.547,-0.371 c -1.524,-0.918 -3.301,-1.464 -5.106,-1.562 v 4.062 c 1.141,-0.078 2.313,0.129 3.387,0.618 l 0.402,0.203 c 0.059,0.031 0.137,0.058 0.196,0.097 l 0.183,0.129 0.383,0.235 0.352,0.281 0.176,0.137 0.164,0.168 0.332,0.312 0.293,0.34 0.156,0.176 c 0.051,0.058 0.09,0.117 0.129,0.187 l 0.273,0.371 c 0.078,0.125 0.145,0.274 0.223,0.399 0.176,0.265 0.273,0.558 0.41,0.84 0.043,0.07 0.059,0.148 0.078,0.214 l 0.078,0.227 0.071,0.223 c 0.027,0.07 0.058,0.148 0.066,0.226 0.059,0.301 0.156,0.606 0.176,0.926 l 0.058,0.48 0.032,0.547 0.058,1.114 0.125,2.207 0.344,5.957 c -0.156,-0.43 -0.332,-0.852 -0.52,-1.262 -0.523,-1.121 -1.171,-2.156 -1.894,-3.113 -0.184,-0.235 -0.371,-0.469 -0.555,-0.703 -0.195,-0.227 -0.39,-0.45 -0.597,-0.665 -0.196,-0.226 -0.411,-0.441 -0.614,-0.644 -0.215,-0.207 -0.429,-0.422 -0.644,-0.617 -0.442,-0.399 -0.899,-0.782 -1.367,-1.153 l -0.696,-0.554 -0.722,-0.528 c -1.934,-1.406 -4.012,-2.617 -6.239,-3.554 -0.285,-0.118 -0.558,-0.235 -0.84,-0.344 -0.293,-0.098 -0.578,-0.203 -0.859,-0.301 -0.566,-0.207 -1.164,-0.371 -1.746,-0.527 -0.59,-0.156 -1.203,-0.274 -1.809,-0.383 -0.605,-0.098 -1.23,-0.164 -1.867,-0.195 0.383,0.527 0.781,0.988 1.176,1.445 0.203,0.227 0.398,0.461 0.594,0.684 l 0.593,0.656 c 0.801,0.867 1.602,1.687 2.403,2.5 1.601,1.621 3.222,3.144 4.875,4.629 l 1.25,1.113 c 0.203,0.184 0.418,0.359 0.625,0.547 0.215,0.183 0.418,0.359 0.633,0.547 0.324,0.254 0.644,0.508 0.976,0.761 -0.469,-0.109 -0.945,-0.207 -1.414,-0.292 -0.5,-0.098 -0.996,-0.196 -1.496,-0.235 -2,-0.265 -4.004,-0.293 -5.984,-0.129 -0.5,0.039 -0.996,0.09 -1.496,0.149 -0.489,0.058 -0.985,0.117 -1.473,0.203 -0.977,0.156 -1.961,0.312 -2.93,0.527 -3.879,0.832 -7.629,2.121 -11.172,3.848 -1.769,0.859 -3.484,1.847 -5.136,2.949 -0.813,0.559 -1.621,1.133 -2.414,1.758 -0.77,0.637 -1.543,1.289 -2.266,2.012 1.035,0.058 2.031,0.031 3.019,0 z" />
						</g>
					</g>
				</svg>
				<span class="count">
					<?php echo wp_kses_data(WC()->cart->get_cart_contents_count()); ?>
				</span>
			</div>
			<div class="amount-cart hidden-xs">
				<?php echo wc_price( get_cart_subtotal_sum() ); ?>
			</div>
		</a>

		<?php
	}

}

if (!function_exists('altr_header_cart')) {
	function altr_header_cart()
	{
		if (get_theme_mod('woo_header_cart', 1) == 1) {
			?>
			<div class="header-cart">
				<div class="header-cart-block">
					<div class="header-cart-inner">
						<?php altr_cart_link(); ?>
					</div>
				</div>
			</div>
			<?php
		}
	}
}


if (!function_exists('altr_cart_content')) {
	function cart_icon()
	{ ?>

	<?php }
	function altr_cart_content()
	{
		?>
		<ul class="site-header-cart list-unstyled">
			<div class="top-header-cart">
				<svg class="cart-icon" viewBox="0 0 34.714775 30.704659" height="30.704659" width="34.714775">
					<g transform="matrix(0.13333333,0,0,-0.13333333,-14.178271,115.80363)" id="g2062">
						<g stroke="#1e1e1e" stroke-width="4.74749994" stroke-miterlimit="4" stroke-dasharray="none" stroke-opacity="1"
							transform="translate(-13.482968,2.9962151)" id="g9">
							<path fill="#1e1e1e" fill-opacity="1" fill-rule="nonzero" stroke="#1e1e1e" stroke-width="0"
								stroke-linejoin="miter" stroke-miterlimit="4" stroke-dasharray="none" stroke-opacity="1"
								d="m 226.578,700.934 h 126.59 c 0.215,0 0.422,0.007 0.637,0.027 0.066,0.012 0.144,0.019 0.222,0.031 0.129,0.02 0.254,0.039 0.391,0.059 0.09,0.019 0.176,0.039 0.266,0.058 0.105,0.028 0.214,0.059 0.332,0.086 0.086,0.032 0.175,0.059 0.273,0.09 0.098,0.028 0.195,0.067 0.301,0.106 0.09,0.039 0.176,0.07 0.265,0.109 0.098,0.047 0.196,0.098 0.293,0.145 0.079,0.039 0.165,0.078 0.243,0.128 0.109,0.059 0.207,0.118 0.304,0.184 0.067,0.039 0.137,0.078 0.203,0.129 0.137,0.098 0.274,0.195 0.403,0.293 0.019,0.027 0.047,0.039 0.078,0.066 0.156,0.129 0.301,0.254 0.437,0.403 0.051,0.039 0.09,0.086 0.129,0.125 0.098,0.109 0.196,0.207 0.282,0.312 0.05,0.059 0.089,0.117 0.136,0.176 0.078,0.098 0.157,0.207 0.227,0.305 0.047,0.066 0.098,0.136 0.137,0.214 0.066,0.098 0.125,0.196 0.183,0.293 0.051,0.079 0.09,0.157 0.129,0.243 0.047,0.097 0.098,0.195 0.145,0.293 0.039,0.089 0.078,0.175 0.117,0.265 0.039,0.098 0.07,0.196 0.109,0.293 0.028,0.098 0.067,0.196 0.098,0.293 0.027,0.098 0.047,0.195 0.066,0.301 0.031,0.098 0.059,0.195 0.078,0.305 0,0.007 0,0.019 0.012,0.027 l 20.399,106.543 c 0.371,1.945 -0.145,3.937 -1.395,5.461 -1.262,1.512 -3.125,2.402 -5.098,2.402 H 209.586 l -3.918,20.352 c -2.723,14.187 -15.184,24.48 -29.629,24.48 h -49.617 c -3.645,0 -6.602,-2.957 -6.602,-6.609 0,-3.652 2.957,-6.61096 6.602,-6.61096 h 49.617 c 8.117,0 15.117,-5.78404 16.652,-13.76004 l 24.297,-126.457 c -8.175,-3.672 -13.879,-11.883 -13.879,-21.414 0,-12.942 10.528,-23.469 23.469,-23.469 h 126.59 c 3.652,0 6.613,2.961 6.613,6.613 0,3.653 -2.961,6.61 -6.613,6.61 h -126.59 c -5.656,0 -10.242,4.601 -10.242,10.246 0,5.652 4.586,10.254 10.242,10.254"
								id="top" />
							<path fill="#1e1e1e" fill-opacity="1" fill-rule="nonzero" stroke="#ffffff" stroke-width="0"
								stroke-miterlimit="4" stroke-dasharray="none" stroke-opacity="1"
								d="m 246.078,662.34806 c -7.488,0 -13.555,-6.067 -13.555,-13.555 0,-7.48 6.067,-13.547 13.555,-13.547 7.481,0 13.547,6.067 13.547,13.547 0,7.488 -6.066,13.555 -13.547,13.555"
								id="left-wheel" />
							<path fill="#1e1e1e" fill-opacity="1" fill-rule="nonzero" stroke="#1e1e1e" stroke-width="0"
								stroke-miterlimit="4" stroke-dasharray="none" stroke-opacity="1"
								d="m 327.387,662.34806 c -7.489,0 -13.551,-6.067 -13.551,-13.555 0,-7.48 6.062,-13.547 13.551,-13.547 7.48,0 13.554,6.067 13.554,13.547 0,7.488 -6.074,13.555 -13.554,13.555"
								id="right-wheel" />
							<path fill="#ffffff" fill-opacity="1" fill-rule="nonzero" stroke="#ffffff" stroke-width="1"
								stroke-linejoin="round" stroke-miterlimit="4" stroke-dasharray="none" stroke-opacity="1" id="weed-sheet"
								d="m 252.398,744.76748 c 0.985,-0.028 1.954,-0.067 2.911,-0.137 0.476,-0.039 0.957,-0.059 1.433,-0.098 0.481,-0.027 0.949,-0.066 1.418,-0.105 l 0.703,-0.071 0.703,-0.058 c 0.469,-0.039 0.938,-0.078 1.395,-0.125 3.703,-0.363 7.305,-0.793 10.859,-1.192 0.45,-0.058 0.891,-0.129 1.34,-0.156 0.438,-0.051 0.887,-0.098 1.328,-0.148 0.887,-0.125 1.778,-0.176 2.657,-0.293 1.472,-0.176 2.949,-0.391 4.433,-0.664 l -0.344,0.175 c -0.273,0.157 -0.546,0.313 -0.828,0.469 -0.558,0.313 -1.105,0.637 -1.633,0.977 -1.054,0.683 -2.097,1.398 -3.085,2.168 -0.489,0.39 -0.977,0.781 -1.454,1.183 -0.48,0.399 -0.949,0.809 -1.406,1.231 -1.816,1.699 -3.555,3.484 -5.226,5.32 -3.329,3.692 -6.336,7.656 -8.973,11.895 -1.32,2.121 -2.539,4.316 -3.652,6.593 -0.547,1.153 -1.075,2.313 -1.555,3.504 -0.242,0.598 -0.477,1.192 -0.692,1.817 -0.109,0.304 -0.226,0.617 -0.324,0.929 -0.097,0.313 -0.203,0.625 -0.293,0.946 0.313,-0.137 0.606,-0.274 0.899,-0.43 0.304,-0.137 0.586,-0.293 0.879,-0.449 0.578,-0.293 1.132,-0.613 1.691,-0.938 1.113,-0.633 2.188,-1.297 3.242,-1.992 1.055,-0.672 2.09,-1.367 3.094,-2.09 l 1.516,-1.074 c 0.488,-0.359 0.984,-0.723 1.472,-1.094 3.907,-2.949 7.578,-6.054 11.106,-9.258 0.429,-0.41 0.867,-0.808 1.316,-1.199 0.442,-0.402 0.86,-0.82 1.301,-1.222 0.859,-0.821 1.738,-1.621 2.566,-2.461 0.422,-0.418 0.84,-0.84 1.25,-1.258 0.41,-0.43 0.813,-0.871 1.211,-1.309 0.391,-0.449 0.793,-0.89 1.184,-1.34 0.39,-0.449 0.762,-0.918 1.14,-1.386 0.743,-0.946 1.477,-1.895 2.18,-2.91 0.156,-0.223 0.301,-0.45 0.457,-0.672 -0.769,2.234 -1.347,4.48 -1.758,6.718 -0.507,2.743 -0.761,5.489 -0.82,8.231 -0.039,2.734 0.031,5.48 0.176,8.223 0.305,5.48 0.969,10.968 2.101,16.445 0.137,0.695 0.301,1.379 0.45,2.062 0.164,0.684 0.32,1.368 0.507,2.059 0.34,1.367 0.723,2.734 1.153,4.113 0.429,1.367 0.887,2.742 1.414,4.11 0.519,1.367 1.086,2.746 1.789,4.113 0.703,-1.367 1.269,-2.746 1.785,-4.113 0.527,-1.368 0.988,-2.743 1.418,-4.11 0.43,-1.379 0.809,-2.746 1.152,-4.113 0.184,-0.691 0.34,-1.375 0.508,-2.059 0.145,-0.683 0.313,-1.367 0.449,-2.062 1.133,-5.477 1.797,-10.965 2.098,-16.445 0.149,-2.743 0.215,-5.489 0.176,-8.223 -0.059,-2.742 -0.313,-5.488 -0.82,-8.231 -0.411,-2.238 -0.985,-4.484 -1.758,-6.718 0.156,0.222 0.304,0.449 0.461,0.672 0.703,1.015 1.433,1.964 2.175,2.91 0.383,0.468 0.754,0.937 1.145,1.386 0.391,0.45 0.781,0.891 1.18,1.34 0.402,0.438 0.8,0.879 1.211,1.309 0.41,0.418 0.832,0.84 1.25,1.258 0.832,0.84 1.711,1.64 2.57,2.461 0.437,0.402 0.859,0.82 1.297,1.222 0.449,0.391 0.89,0.789 1.32,1.199 3.524,3.204 7.195,6.309 11.102,9.258 0.488,0.371 0.988,0.735 1.476,1.094 l 1.512,1.074 c 1.008,0.723 2.043,1.418 3.098,2.09 1.054,0.695 2.129,1.359 3.242,1.992 0.554,0.325 1.113,0.645 1.687,0.938 0.293,0.156 0.578,0.312 0.879,0.449 0.293,0.156 0.586,0.293 0.899,0.43 -0.098,-0.321 -0.196,-0.633 -0.293,-0.946 -0.094,-0.312 -0.215,-0.625 -0.321,-0.929 -0.214,-0.625 -0.449,-1.219 -0.691,-1.817 -0.481,-1.191 -1.008,-2.351 -1.555,-3.504 -1.113,-2.277 -2.336,-4.472 -3.652,-6.593 -2.637,-4.239 -5.645,-8.203 -8.977,-11.895 -1.668,-1.836 -3.406,-3.621 -5.222,-5.32 -0.461,-0.422 -0.93,-0.832 -1.407,-1.231 -0.48,-0.402 -0.968,-0.793 -1.464,-1.183 -0.977,-0.77 -2.024,-1.485 -3.079,-2.168 -0.527,-0.34 -1.074,-0.664 -1.628,-0.977 -0.286,-0.156 -0.559,-0.312 -0.84,-0.469 l -0.332,-0.175 c 1.484,0.273 2.957,0.488 4.433,0.664 0.879,0.117 1.766,0.168 2.657,0.293 0.441,0.05 0.886,0.097 1.328,0.148 0.449,0.027 0.886,0.098 1.336,0.156 3.554,0.399 7.16,0.829 10.859,1.192 0.461,0.047 0.93,0.086 1.398,0.125 l 0.704,0.058 0.703,0.071 c 0.468,0.039 0.937,0.078 1.414,0.105 0.48,0.039 0.957,0.059 1.437,0.098 0.957,0.07 1.922,0.109 2.91,0.137 0.985,0.031 1.981,0.058 3.02,0 -0.727,-0.723 -1.496,-1.375 -2.27,-2.012 -0.789,-0.625 -1.601,-1.199 -2.41,-1.758 -1.652,-1.102 -3.371,-2.09 -5.136,-2.949 -3.547,-1.727 -7.297,-3.016 -11.172,-3.848 -0.969,-0.215 -1.953,-0.371 -2.93,-0.527 -0.488,-0.086 -0.988,-0.145 -1.484,-0.203 -0.489,-0.059 -0.989,-0.11 -1.485,-0.149 -1.984,-0.164 -3.984,-0.136 -5.988,0.129 -0.496,0.039 -0.996,0.137 -1.492,0.235 -0.469,0.085 -0.949,0.183 -1.418,0.292 0.332,-0.253 0.656,-0.507 0.976,-0.761 0.215,-0.188 0.422,-0.364 0.637,-0.547 0.203,-0.188 0.418,-0.363 0.625,-0.547 l 1.25,-1.113 c 1.648,-1.485 3.27,-3.008 4.871,-4.629 0.801,-0.813 1.602,-1.633 2.402,-2.5 l 0.598,-0.656 c 0.195,-0.223 0.391,-0.457 0.594,-0.684 0.391,-0.457 0.793,-0.918 1.172,-1.445 -0.633,0.031 -1.258,0.097 -1.864,0.195 -0.605,0.109 -1.222,0.227 -1.808,0.383 -0.586,0.156 -1.18,0.32 -1.746,0.527 -0.285,0.098 -0.567,0.203 -0.86,0.301 -0.285,0.109 -0.558,0.226 -0.839,0.344 -2.227,0.937 -4.309,2.148 -6.243,3.554 l -0.722,0.528 -0.692,0.554 c -0.468,0.371 -0.929,0.754 -1.367,1.153 -0.215,0.195 -0.43,0.41 -0.644,0.617 -0.207,0.203 -0.422,0.418 -0.618,0.644 -0.203,0.215 -0.398,0.438 -0.593,0.665 -0.196,0.234 -0.371,0.468 -0.559,0.703 -0.723,0.957 -1.367,1.992 -1.894,3.113 -0.137,0.312 -0.274,0.625 -0.399,0.949 l 0.32,-5.644 0.129,-2.207 0.059,-1.114 0.039,-0.558 v -0.625 c 0.008,-0.43 -0.051,-0.848 -0.09,-1.27 -0.008,-0.105 -0.027,-0.215 -0.058,-0.312 l -0.059,-0.313 -0.066,-0.32 c -0.02,-0.098 -0.04,-0.207 -0.079,-0.313 -0.128,-0.402 -0.234,-0.82 -0.421,-1.211 -0.086,-0.195 -0.157,-0.402 -0.254,-0.597 l -0.313,-0.567 c -0.047,-0.097 -0.098,-0.195 -0.156,-0.281 l -0.184,-0.273 c -0.117,-0.176 -0.234,-0.364 -0.363,-0.539 l -0.418,-0.508 -0.207,-0.254 -0.234,-0.234 -0.469,-0.458 c -0.164,-0.148 -0.332,-0.285 -0.508,-0.429 l -0.254,-0.207 c -0.086,-0.067 -0.183,-0.125 -0.273,-0.196 l -0.547,-0.371 c -1.524,-0.918 -3.301,-1.464 -5.106,-1.562 v 4.062 c 1.141,-0.078 2.313,0.129 3.387,0.618 l 0.402,0.203 c 0.059,0.031 0.137,0.058 0.196,0.097 l 0.183,0.129 0.383,0.235 0.352,0.281 0.176,0.137 0.164,0.168 0.332,0.312 0.293,0.34 0.156,0.176 c 0.051,0.058 0.09,0.117 0.129,0.187 l 0.273,0.371 c 0.078,0.125 0.145,0.274 0.223,0.399 0.176,0.265 0.273,0.558 0.41,0.84 0.043,0.07 0.059,0.148 0.078,0.214 l 0.078,0.227 0.071,0.223 c 0.027,0.07 0.058,0.148 0.066,0.226 0.059,0.301 0.156,0.606 0.176,0.926 l 0.058,0.48 0.032,0.547 0.058,1.114 0.125,2.207 0.344,5.957 c -0.156,-0.43 -0.332,-0.852 -0.52,-1.262 -0.523,-1.121 -1.171,-2.156 -1.894,-3.113 -0.184,-0.235 -0.371,-0.469 -0.555,-0.703 -0.195,-0.227 -0.39,-0.45 -0.597,-0.665 -0.196,-0.226 -0.411,-0.441 -0.614,-0.644 -0.215,-0.207 -0.429,-0.422 -0.644,-0.617 -0.442,-0.399 -0.899,-0.782 -1.367,-1.153 l -0.696,-0.554 -0.722,-0.528 c -1.934,-1.406 -4.012,-2.617 -6.239,-3.554 -0.285,-0.118 -0.558,-0.235 -0.84,-0.344 -0.293,-0.098 -0.578,-0.203 -0.859,-0.301 -0.566,-0.207 -1.164,-0.371 -1.746,-0.527 -0.59,-0.156 -1.203,-0.274 -1.809,-0.383 -0.605,-0.098 -1.23,-0.164 -1.867,-0.195 0.383,0.527 0.781,0.988 1.176,1.445 0.203,0.227 0.398,0.461 0.594,0.684 l 0.593,0.656 c 0.801,0.867 1.602,1.687 2.403,2.5 1.601,1.621 3.222,3.144 4.875,4.629 l 1.25,1.113 c 0.203,0.184 0.418,0.359 0.625,0.547 0.215,0.183 0.418,0.359 0.633,0.547 0.324,0.254 0.644,0.508 0.976,0.761 -0.469,-0.109 -0.945,-0.207 -1.414,-0.292 -0.5,-0.098 -0.996,-0.196 -1.496,-0.235 -2,-0.265 -4.004,-0.293 -5.984,-0.129 -0.5,0.039 -0.996,0.09 -1.496,0.149 -0.489,0.058 -0.985,0.117 -1.473,0.203 -0.977,0.156 -1.961,0.312 -2.93,0.527 -3.879,0.832 -7.629,2.121 -11.172,3.848 -1.769,0.859 -3.484,1.847 -5.136,2.949 -0.813,0.559 -1.621,1.133 -2.414,1.758 -0.77,0.637 -1.543,1.289 -2.266,2.012 1.035,0.058 2.031,0.031 3.019,0 z" />
						</g>
					</g>
				</svg>

				<h4 class="title">Mi carrito (<span id="mini-cart-products-qty"><?php echo WC()->cart->get_cart_contents_count(); ?></span>)</h4>
			<i class="las la-times la-1x"></i>
			</div>
			<?php the_widget('WC_Widget_Cart', 'title='); ?>
		</ul>
		<?php
	}
}

function toggle_cart_sidebar()
{
	?>
	<script>
		const headerCart = document.querySelector('.header-cart');
		const closeIcon = document.querySelector('ul.site-header-cart .top-header-cart i.la-times');
		const body = document.body;
		const pageWrap = document.querySelector('.cart-open .page-wrap');
		const siteHeaderCart = document.querySelector('ul.site-header-cart');

		function toggleCartOpen() {
			body.classList.toggle('cart-open');
		}

		document.addEventListener('click', function (event) {
			const target = event.target;

			if (!headerCart.contains(target)) {
				const isOpen = body.classList.contains('cart-open');

				if (isOpen && (target === closeIcon || !siteHeaderCart.contains(target))) {
					toggleCartOpen();
				}
			}
		});
	</script>
	<?php
}

add_action('wp_footer', 'toggle_cart_sidebar');

// add filter if we want to use in wordpress with woocommerce
add_filter('wp_get_nav_menu_items', 'nav_remove_empty_category_menu_item', 10, 3);

function nav_remove_empty_category_menu_item($items, $menu, $args)
{
	global $wpdb;

	foreach ($items as $key => $item) {
		
		if (($item->type == 'taxonomy')) {
			$query = new WP_Query(
				array(
					'tax_query' => array(
						array(
							'taxonomy' => 'product_cat',
							'field' => 'id',
							'terms' => $item->object_id,
							'include_children' => true,
						),
					),
					'nopaging' => true,
					'fields' => 'ids',
				)
			);

			if ($query->post_count == 0) {
				unset($items[$key]);
			}
		}
	}

	return $items;

}

function my_child_theme_register_menus()
{
	register_nav_menus(
		array(
			'hc-offcanvas-menu' => 'Menú personalizado para hc-offcanvas-nav',
		)
	);
}
add_action('after_setup_theme', 'my_child_theme_register_menus');

/**
 * Campos personalizados del formulario de Registro de WooCommerce
 * */
function add_woocommerce_sign_up_custom_fields()
{
	?>
	<div class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
		<label for="reg_first_name">
			<?php esc_attr_e('First name', 'woocommerce'); ?> <span class="required">*</span>
		</label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_first_name"
			id="reg_first_name" value="<?php if (!empty($_POST['account_first_name']))
				echo esc_attr($_POST['account_first_name']); ?>" required>
	</div>

	<div class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
		<label for="reg_last_name">
			<?php esc_attr_e('Last name', 'woocommerce'); ?> <span class="required">*</span>
		</label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_last_name"
			id="reg_last_name" value="<?php if (!empty($_POST['account_last_name']))
				echo esc_attr($_POST['account_last_name']); ?>" required>
	</div>

	<div class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
		<label for="reg_date_of_birth">
			<?php esc_attr_e('Date of birth.', 'woocommerce'); ?> <span class="required">*</span>
		</label>
		<input type="date" class="woocommerce-Input woocommerce-Input--date input-text" name="account_date_of_birth"
			id="reg_date_of_birth" value="<?php if (!empty($_POST['account_date_of_birth']))
				echo esc_attr($_POST['account_date_of_birth']); ?>" required>
		<div class="underage hide">Para poder registrarte necesitas ser mayor de 18 años.</div>
	</div>
	<?php
}

add_action('woocommerce_register_form_start', 'add_woocommerce_sign_up_custom_fields');

function add_new_fields_to_new_created_customer($customer_id)
{
	if (isset($_POST['account_first_name']) && !empty($_POST['account_first_name'])) {
		update_user_meta($customer_id, 'first_name', sanitize_text_field($_POST['account_first_name']));
	}
	if (isset($_POST['account_last_name']) && !empty($_POST['account_last_name'])) {
		update_user_meta($customer_id, 'last_name', sanitize_text_field($_POST['account_last_name']));
	}
	if (isset($_POST['account_date_of_birth']) && !empty($_POST['account_date_of_birth'])) {
		update_user_meta($customer_id, 'date_of_birth', sanitize_text_field($_POST['account_date_of_birth']));
	}
}

add_action('woocommerce_created_customer', 'add_new_fields_to_new_created_customer');

/**
 * Custom Scripts, Styles & JSON Data
 * */

function enqueue_custom_scripts_to_specific_page()
{
	if (is_page('home')) {

		wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js', array(), null, true);
	}

	if (is_page('frequent-asked-questions')) {
		wp_enqueue_script('custom-accordion-script', get_stylesheet_directory_uri() . '/assets/js/accordion.min.js', array(), null, true);
	}

	if (is_page("my-profile")) {
		wp_enqueue_script('custom-login-page-script', get_stylesheet_directory_uri() . '/assets/js/login-page.js', array(), null, true);
	}
}

add_action('wp_enqueue_scripts', 'enqueue_custom_scripts_to_specific_page');

function enqueue_custom_styles_to_specific_page()
{
	if (is_page('home')) {
		wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css', array(), null);
	}
}

add_action('wp_enqueue_scripts', 'enqueue_custom_styles_to_specific_page');

function add_json_to_specific_page()
{
	if (is_page('frequent-asked-questions')) {
		$faqs_json_file_url = get_stylesheet_directory_uri() . '/assets/json/faqs.json';
		wp_enqueue_script('custom-faqs-script', get_stylesheet_directory_uri() . '/assets/', array(), '2.0', true);

		// Obtener el contenido del JSON y convertirlo a un objeto PHP
		$faqs_json_file_contents = file_get_contents($faqs_json_file_url);
		$faqs_json_data = json_decode($faqs_json_file_contents, true);

		// Pasar los datos del JSON al script de JavaScript
		wp_localize_script('custom-faqs-script', 'faqsData', $faqs_json_data);
	}
}
add_action('wp_enqueue_scripts', 'add_json_to_specific_page');

function get_formatted_address($args)
{
	$wc_countries = new WC_Countries();
	$default_args = array(
		'first_name' => '',
		'last_name' => '',
		'company' => '',
		'address_1' => '',
		'address_2' => '',
		'city' => '',
		'state' => '',
		'postcode' => '',
		'country' => '',
	);

	$args = array_map('trim', wp_parse_args($args, $default_args));
	$state = $args['state'];
	$country = $args['country'];

	// Get all formats.
	$formats = $wc_countries->get_address_formats();

	// Get format for the address' country.
	$format = ($country && isset($formats[$country])) ? $formats[$country] : $formats['default'];

	// Handle full country name.
	$full_country = (isset($wc_countries->countries[$country])) ? $wc_countries->countries[$country] : $country;

	// Country is not needed if the same as base.
	if ($country === $wc_countries->get_base_country() && !apply_filters('woocommerce_formatted_address_force_country_display', false)) {
		$format = str_replace('{country}', '', $format);
	}

	// Handle full state name.
	$full_state = ($country && $state && isset($wc_countries->states[$country][$state])) ? $wc_countries->states[$country][$state] : $state;

	// Substitute address parts into the string.
	$customer_address = array_map(
		'esc_html',
		apply_filters(
			'woocommerce_formatted_address_replacements',
			array(
				'{name}' => sprintf(
					/* translators: 1: first name 2: last name */
					_x('%1$s %2$s', 'full name', 'woocommerce'),
					$args['first_name'],
					$args['last_name']
				),
				'{company}' => $args['company'],
				'{address_1}' => $args['address_1'],
				'{address_2}' => $args['address_2'],
				'{city}' => $args['city'],
				'{state}' => $full_state,
				'{postcode}' => $args['postcode'],
				'{country}' => $full_country
			),
			$args
		)
	);

	$formatted_address = [];

	foreach ($customer_address as $key => $value) {
		$cleaned_key = str_replace(array('{', '}'), '', $key);

		$formatted_address[] = "<span " . ($cleaned_key === "name" ? "style='font-weight: bold;'" : "") . ">{$value}</span>";
	}

	return implode($formatted_address);
}

enum Type: string
{
	case Cart = 'cart';
	case MiniCart = 'mini-cart';
	case Product = 'product';
}

function get_mini_cart_item_html($product, $cart_item, $cart_item_key)
{ ?>
	<li
		class="woocommerce-mini-cart-item <?php echo esc_attr(apply_filters('woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key)); ?>">
		<div class="content">
			<picture>
				<?php echo $product->thumbnail; ?>
			</picture>
			<div class="details">
				<?php if (empty($product->permalink)): ?>
					<span>
						<?php echo wp_kses_post($product->name); ?>
					</span>
				<?php else: ?>
					<a href="<?php echo esc_url($product->permalink); ?>">
						<?php echo wp_kses_post($product->name); ?>
					</a>
				<?php endif; ?>
				<div class="qty-container">
					<div class="qty-result">
						<?php echo $product->quantity_input; ?>

						<div class="price qty-price">
							<!-- Echar un vistazo sobre el redondeo, ya que no se está haciendo correctamente. Si tenemos 4.50, redondea a 5 -->
							<?php echo wc_price( $product->subtotal );  ?>
							<small class="tax_label"><?php echo WC()->countries->inc_tax_or_vat(); ?></small>
						</div>
					</div>
				</div>
			</div>
		</div>

		<?php echo $product->remove; ?>
	</li>
<?php }

function show_remove_cart_item_icon($cart_item_key, $product, $product_id, $product_name)
{
	return apply_filters(
		'woocommerce_cart_item_remove_link',
		sprintf(
			'<a href="%s" class="remove remove_from_cart_button" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s"><i class="las la-trash-alt la-lg"></i></a>',
			esc_url(wc_get_cart_remove_url($cart_item_key)),
			esc_attr(sprintf(__('Remove %s from cart', 'woocommerce'), wp_strip_all_tags($product->name))),
			esc_attr($product->id),
			esc_attr($cart_item_key),
			esc_attr($product->sku)
		),
		$cart_item_key
	);
}

function get_custom_product_quantity_input($_product, $cart_item = null, $cart_item_key = null, ?Type $type = null)
{
	if ($_product->is_sold_individually()) {
		$min_quantity = 1;
		$max_quantity = 1;
	} else {
		$min_quantity = $_product->get_min_purchase_quantity();
		$max_quantity = $_product->get_max_purchase_quantity();
	}

	$woocommerce_quantity_input = woocommerce_quantity_input(
		array(
			'input_name' => $type !== Type::Product ? "cart[{$cart_item_key}][qty]" : $_product->name . '_qty_input',
			'input_value' => $type !== Type::Product ? $cart_item['quantity'] : 1,
			'max_value' => $max_quantity,
			'min_value' => $min_quantity,
			'product_name' => $_product->name,
			'product_price' => get_item_price_inc_tax($_product),
			'class'
		),
		$_product,
		false
	);

	$custom_quantity_input = '<div class="quantity-container">';
	$custom_quantity_input .= '<button class="decrement"  type="button"><i class="las la-minus la-2x"></i></button>';
	$custom_quantity_input .= $woocommerce_quantity_input;
	$custom_quantity_input .= '<button class="increment"  type="button"><i class="las la-plus la-2x"></i></button>';
	$custom_quantity_input .= '</div>';

	return $custom_quantity_input;
}

function get_mapped_cart_item_data($_product, $cart_item, $cart_item_key)
{
	$tax_classes = $_product->get_tax_class();
	$tax_rates = WC_Tax::get_rates($tax_classes);

	$price = 0;

	foreach ($tax_rates as $tax) {
		$price = wc_price( round( ($_product->get_price() + ($_product->get_price() * ($tax['rate'] / 100)) ) * $cart_item['quantity'], 3 )  );
	}

	$product = new stdClass();

	$product->id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
	$product->name = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);
	$product->thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);
	$product->price = $_product->get_price();
	$product->subtotal = apply_filters( 'custom_woocommerce_item_subtotal', $cart_item);
	$product->permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
	$product->sku = $_product->get_sku();
	$product->quantity_input = get_custom_product_quantity_input($_product, $cart_item, $cart_item_key);
	$product->remove = show_remove_cart_item_icon($cart_item_key, $_product, $product->id, $product->name);

	return $product;
}

function get_custom_cart($cart = null, $type = '')
{
	foreach ($cart as $cart_item_key => $cart_item) {
		$_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);

		if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key)) {
			$product = get_mapped_cart_item_data($_product, $cart_item, $cart_item_key);

			// if ( $type === Type::Cart->value ) { echo "Es para el carrito"; }
			if ($type === Type::MiniCart) {
				echo get_mini_cart_item_html($product, $cart_item, $cart_item_key);
			}
		}
	}
}

add_action('custom_cart', 'get_custom_cart', 10, 2);

function custom_product_tabs($tabs)
{
	global $product;

	if ($product->has_attributes() || $product->has_dimensions() || $product->has_weight()) {
		$tabs['additional_information']['title'] = 'Ficha técnica';
	}

	return $tabs;
}

function woocommerce_template_extra_details() { ?>
	<div class="details-banner">
		<div class="gift">
			<figure class="icon">
				<picture>
					<img src="https://www.liligrow.es/wp-content/uploads/2023/10/gifts.webp" alt="Regalos en todos tus pedidos" />
				</picture>
			</figure>
			<div class="info">
				<header>
					<h4 class="title">Regalos en <span>todos tus pedidos</span></h4>
				</header>
			</div>
		</div>
		<div class="delivery">
			<figure class="icon">
				<picture>
					<img src="https://www.liligrow.es/wp-content/uploads/2023/10/black-delivery.webp" alt="Envíos en 24/48 horas" />
				</picture>
			</figure>
			<div class="info">
				<header>
					<h4 class="title">Envío en <span>24/48h</span></h4>
				</header>
			</div>
		</div>
		<div class="payment">
			<figure class="icon">
				<picture>
					<img src="https://www.liligrow.es/wp-content/uploads/2023/10/black-anonymous.webp" alt="Empaquetado discreto"
						height="50"
					/>
				</picture>
			</figure>
			<div class="info">
				<header>
					<h4 class="title">Paquete <span>discreto</span></h4>
				</header>
			</div>
		</div>
		<div class="purchase">
			<figure class="icon">
				<picture>
					<img src="https://www.liligrow.es/wp-content/uploads/2023/10/black-secure.webp" alt="Pagos seguros" />
				</picture>
			</figure>
			<div class="info">
				<header>
					<h4 class="title">Pagos <span>seguros</span></h4>
				</header>
			</div>
		</div>
	</div>
<?php
}

add_action('woocommerce_single_product_summary', 'woocommerce_template_extra_details', 35);

add_action('wcpos_transaction_complete', 'enviar_ticket_por_correo', 10, 3);

function enviar_ticket_por_correo($order_id, $payment_method, $transaction_data) {
    // Obtiene la instancia del pedido
    $order = wc_get_order($order_id);

    // Verifica si el pedido se completó correctamente
    if ($order->has_status('completed')) {
        // Construye el contenido del ticket (puedes personalizar esto según tus necesidades)
        $subject = 'Ticket de Pedido #' . $order_id;
        $message = 'Aquí está su ticket de pedido:' . "\n\n" . $order->get_formatted_order_total();

        // Obtiene el correo electrónico del cliente
        $to = $order->get_billing_email();

        // Envía el correo
        wp_mail($to, $subject, $message);
    }
}

// function reset_chosen_shipping_methods() {
// 	WC()->session->set( 'chosen_shipping_methods', array());
// }

// add_action('init', 'reset_chosen_shipping_methods');



// Función que se ejecutará después del renderizado de la tasa de envío
function custom_customize_shipping_rate($method, $index) {
    // Accede a la información del método de envío y su índice
    $method_id = $method->id;
    $label     = $method->label;
	$selected_shipping_method = WC()->session->get('chosen_shipping_methods')[0];
	$checked_attribute = $selected_shipping_method === $method_id ? 'checked="checked"' : '';

    // Personaliza el renderizado del radio button según tus necesidades
    //echo '<div class="custom-shipping-rate">';
    echo '<input type="radio" name="shipping_method[' . $index . ']" data-index="' . $index . '" id="shipping_method_' . $index . '_' . $method_id . '" value="' . esc_attr($method_id) . '" class="shipping_method" ' . $checked_attribute . '>';
    echo '<label for="shipping_method_' . $index . '_' . $method_id . '">' . esc_html($label) . '</label>';
    //echo '</div>';
}

add_filter( 'woocommerce_variable_sale_price_html', 'custom_variable_price_range', 10, 2 );
add_filter( 'woocommerce_variable_price_html', 'custom_min_max_variable_price_html', 10, 2 );

function custom_min_max_variable_price_html( $price, $product ) {
    $prices = $product->get_variation_prices( true );
    $min_price = current( $prices['price'] );

    $min_keys = current(array_keys( $prices['price'] ));
    $min_price_regular = $prices['regular_price'][$min_keys];
    //$min_price_html = wc_price( $min_price ) . $product->get_price_suffix();
	$min_price_html = wc_price( $min_price );

	
    if( $min_price_regular != $min_price ){
       	// $min_price_regular_html = '<del>' . wc_price( $min_price_regular ) . $product->get_price_suffix() . '</del>';
		$min_price_regular_html = '<del>' . wc_price( get_item_price_inc_tax($product, $min_price_regular) ) . '</del> ';
        $min_price_html = $min_price_regular_html .'<ins>' . $min_price_html . '</ins>';
    }

    return __('From', 'show-only-lowest-prices-in-woocommerce-variable-products') . ' ' . $min_price_html . ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
}

add_filter( 'custom_simple_product_get_price_html', 'custom_simple_product_get_price_html', 10, 2);

function custom_simple_product_get_price_html($product) { ?>
	<p class="price">
		<?php if ($product->get_sale_price() !== '') : ?>
			<del>
				<?php echo wc_price( get_item_price_inc_tax($product, $product->get_regular_price()) ); ?>
			</del>
		<?php endif; ?>
		<?php echo wc_price( get_item_price_inc_tax($product) ); ?>
		<small class="tax_label"><?php echo WC()->countries->inc_tax_or_vat(); ?></small>
	</p>
<?php }

function get_item_price_inc_tax($product, $price = null) {
	if (!isset($price)) {
		$price		  = $product->get_price();
	}

	$tax_rates 	  = WC_Tax::get_rates( $product->get_tax_class() );
	$taxes     	  = WC_Tax::calc_tax( $price, $tax_rates );
	$total_taxes  = 0;

	foreach($taxes as $tax) {
		$total_taxes += (float) $tax;
	}

	return (float) $price + $total_taxes;
}

function get_item_subtotal($cart_item, $inc_taxes = true) {
	$product	  = wc_get_product($cart_item['product_id']);
	$price 		  = $product->get_price();
	$quantity	  = $cart_item['quantity'];
	$total_taxes  = 0;

	if ($product->is_type('variable')) {
        // Obtiene la variación seleccionada en el carrito
        $variation_id = $cart_item['variation_id'];
        $variation    = wc_get_product($variation_id);

        // Obtiene el precio de la variación
        $price = $variation->get_price();
    }

	if ($inc_taxes) {
		$tax_rates 	  = WC_Tax::get_rates( $product->get_tax_class() );
		$taxes     	  = WC_Tax::calc_tax( $price, $tax_rates );
	
		foreach($taxes as $tax) {
			$total_taxes += (float) $tax;
		}
	}

	return number_format($price + $total_taxes, 2, '.', '') * $quantity;
}

function get_order_subtotal_sum($order) {
	$result = 0;

	foreach ( $order->get_items() as $item_id => $item ) {
		$result += get_item_subtotal([
			'product_id' => $item->get_product()->id, 
			'quantity' => $item->get_quantity()], 
			false);
	}

	return $result;
}

function get_order_total_sum($order) {
	$subtotal = get_order_subtotal_sum($order);
	$total_tax = 0;

	foreach ( $order->get_tax_totals() as $code => $tax ) {
		$total_tax += $tax->amount;
	}

	return number_format($subtotal + $total_tax, 2, '.', '');
}

add_filter( 'custom_woocommerce_item_subtotal', 'get_item_subtotal', 10, 2);

function get_cart_subtotal_sum($inc_taxes = true) {
    // Obtén el carrito de WooCommerce
    $cart = WC()->cart;

    // Inicializa la suma
    $subtotal_sum = 0;

    // Recorre todos los elementos del carrito
    foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
        $subtotal_sum += get_item_subtotal($cart_item, $inc_taxes);
    }

	return $subtotal_sum;
}

function get_cart_total_inc_shipping_total() {
	return get_cart_subtotal_sum() + WC()->cart->get_shipping_total();
}

function cart_totals_order_total_html() {
	$total = get_cart_total_inc_shipping_total();

	// if (get_is_subtotal_condition_fulfilled()) {
	// 	$total = get_cart_subtotal_sum();
	// }


	WC()->cart->set_total($total);
	$value = '<strong>' . wc_price( $total ) . '</strong> ';
	// If prices are tax inclusive, show taxes here.
	if ( wc_tax_enabled() && WC()->cart->display_prices_including_tax() ) {
		$tax_string_array = array();
		$cart_tax_totals  = WC()->cart->get_tax_totals();

		if ( get_option( 'woocommerce_tax_total_display' ) === 'itemized' ) {
			foreach ( $cart_tax_totals as $code => $tax ) {
				$tax_string_array[] = sprintf( '%s %s', $tax->formatted_amount, $tax->label );
			}
		} elseif ( ! empty( $cart_tax_totals ) ) {
			$tax_string_array[] = sprintf( '%s %s', wc_price( WC()->cart->get_taxes_total( true, true ) ), WC()->countries->tax_or_vat() );
		}

		if ( ! empty( $tax_string_array ) ) {
			$taxable_address = WC()->customer->get_taxable_address();
			if ( WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping() ) {
				$country = WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ];
				/* translators: 1: tax amount 2: country name */
				$tax_text = wp_kses_post( sprintf( __( '(includes %1$s estimated for %2$s)', 'woocommerce' ), implode( ', ', $tax_string_array ), $country ) );
			} else {
				/* translators: %s: tax amount */
				$tax_text = wp_kses_post( sprintf( __( '(includes %s)', 'woocommerce' ), implode( ', ', $tax_string_array ) ) );
			}

			$value .= '<small class="includes_tax">' . $tax_text;

			if (WC()->cart->get_shipping_total() > 0) {
				$value .= ' + envío incluido</small>';
			} else {
				$value .= '</small>';
			}
		}
	}
	
	return $value;
}

function custom_woocommerce_widget_shopping_cart_subtotal() { ?>
	<strong><?php echo esc_html__( 'Subtotal:', 'woocommerce' ) ?></strong>
	<?php echo wc_price( get_cart_subtotal_sum() ); ?>
	<small class="tax_label"><?php echo WC()->countries->inc_tax_or_vat(); ?></small>

<?php
}

add_action( 'woocommerce_widget_shopping_cart_total', 'custom_woocommerce_widget_shopping_cart_subtotal', 10);
