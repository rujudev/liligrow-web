<?php 
    function woocommerce_product_categories_custom_widget() {
        register_widget( 'woocommerce_product_categories_slider_widget' );
    }
    
    add_action( 'widgets_init', 'woocommerce_product_categories_custom_widget' );

    class Woocommerce_Product_Categories_Slider_Widget extends WP_Widget {
        public function __construct() {
            parent::__construct(
                'woocommerce_product_categories_slider_widget',
                'Woocommerce Product Categories',
                array(
                    'description' => esc_html__( 'Displays a slider of product categories', 'woocommerce' ),
                )
            );
        }

        public function widget( $args, $instance ) {
            $title = apply_filters( 'widget_title', $instance['title'] );
            echo $args['before_widget'];
            if (! empty( $title ) ) {
                echo $args['before_title']. $title. $args['after_title'];
            }
            echo $args['after_widget'];
        }

        public function update( $new_instance, $old_instance ) {
           
        }

        public function form( $instance ) {

        }
    }