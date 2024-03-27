<!DOCTYPE html>
<html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="Explora la amplia gama de productos CBD y derivados de alta calidad en Liligrow, tu growshop de confianza. Desde aceites hasta cremas y productos innovadores para el bienestar, en Liligrow nos dedicamos a ofrecer soluciones naturales que potencien tu calidad de vida. Descubre el poder del CBD con nosotros y encuentra equilibrio y serenidad. Somos tu growshop especializado en productos CBD de calidad superior.">
        <link rel="profile" href="http://gmpg.org/xfn/11">
		<link rel="icon" type="image/png" href="https://www.liligrow.es/wp-content/uploads/2023/08/favicon-liligrow.png">
        <link rel="profile" href="http://gmpg.org/xfn/11">
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Roboto:ital@0;1&family=Ubuntu:wght@300&display=swap" rel="stylesheet">
		
		<?php wp_head(); ?>
		
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/font-awesome-line-awesome/css/all.min.css">
		<link href="<?php echo get_stylesheet_directory_uri() . '/assets/css/custom-styles.css'; ?>" rel="stylesheet">
		
		
		<!-- <script type="text/javascript">
			const currentUser = <?php // echo json_encode(wp_get_current_user()) ?>;

			if (currentUser.ID === 0 && Boolean(<?php // echo !is_page('home') && !is_page('my-profile') ?>)) {
				window.location.href = "https://liligrow.es";
			} else if(currentUser.ID !== 0) {
				const roles = currentUser.roles;

				roles.forEach(role => {
					if (role !== 'administrator' && Boolean(<?php // echo !is_page('home') && !is_page('my-profile') ?>)) {
						window.location.href = "https://liligrow.es";
					}
				})
			}
		</script> -->
		<script>
			document.addEventListener('DOMContentLoaded', function() {
				if (document.body.childNodes[1].nodeName === '#text') {
					document.body.childNodes[1].remove();
				}
			});
		</script>
    </head>
    <body id="blog" <?php body_class(); ?>>
		<?php wp_body_open(); ?>
      <div class="page-wrap">
	 	  <?php do_action( 'altr_top_bar' ); ?>
          <div class="site-header title-header container-fluid">
			<div class="container" >
				<div class="heading-row row" >
					<?php do_action( 'altr_header' ); ?>
				</div>
			</div>
		  </div>
		  <div class="site-menu menu-header container-fluid">
			  <div class="<?php echo esc_attr( get_theme_mod( 'header_content_width', 'container' ) ); ?>" >
				  <div class="heading-row row" >
					  <?php do_action( 'entr_header' ); ?>
				  </div>
			  </div>
		  </div>  
