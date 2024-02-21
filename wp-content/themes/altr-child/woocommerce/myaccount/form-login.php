<?php
/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-login.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

do_action('woocommerce_before_customer_login_form'); ?>

<?php if ('yes' === get_option('woocommerce_enable_myaccount_registration')): ?>

    <div class="u-columns col2-set" id="customer_login">
        <div class="u-column1 col-1">

<?php endif; ?>

        <form class="woocommerce-form woocommerce-form-login login" method="post" style="
            padding: 2em 3em 3em 3em;
            border-radius: 30px;
            position: relative;
            display: flex;
            flex-direction: column;
            border-color: #f1f1f1;
        ">
            <figure style="
                    position: absolute;
                    width: 100%;
                    top: -40px;
                    left: 0;
                    display: flex;
                    justify-content: center;
                ">
                <svg viewBox="0 0 60.098999 59.745998" version="1.1" style="width: fit-content;" height="80">
                    <rect style="fill:#1e84af;stroke:#ffffff;stroke-width:4.002px" id="rect7" width="56.097"
                        height="55.743999" x="2.0009999" y="2.0009999" ry="27.872" />
                    <path
                        d="m 30.033305,29.499566 a 8.0130214,7.7153269 0 0 0 8.00417,-7.706316 C 37.661679,11.593719 22.404612,11.595338 22.030149,21.793382 a 8.0127888,7.7151029 0 0 0 8.003156,7.706184 z m 0,-13.376797 c 7.782815,0.238097 7.781493,11.103699 -1.27e-4,11.340473 -7.78179,-0.23825 -7.779516,-11.103251 1.27e-4,-11.340514 z"
                        id="path7"
                        style="fill:#ffffff;fill-opacity:1;stroke:none;stroke-width:0.393136;stroke-dasharray:none;stroke-opacity:1" />
                    <path
                        d="m 30.033305,30.82393 c -7.798123,0.0064 -13.890426,6.486522 -13.123159,13.958504 0.09844,0.476231 0.532509,0.819221 1.036817,0.819264 h 24.173624 c 1.113831,0.01168 1.160401,-1.297016 1.126563,-2.054743 -0.0082,-7.023429 -5.919419,-12.71509 -13.213845,-12.723025 z M 41.132328,43.565374 H 18.935222 c 0.58904,-14.203463 21.61128,-14.195501 22.197106,0 z"
                        id="path3" sodipodi:nodetypes="ccccccccc"
                        style="fill:#ffffff;fill-opacity:1;stroke:none;stroke-width:0.393136;stroke-dasharray:none;stroke-opacity:1" />
                </svg>
            </figure>
            <h2 class="gradient title">
                <?php esc_html_e('Login', 'woocommerce'); ?>
            </h2>
            <?php do_action('woocommerce_login_form_start'); ?>

            <div class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="username">
                    <?php esc_html_e('Username or email address', 'woocommerce'); ?>&nbsp;<span
                        class="required">*</span>
                </label>
                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username"
                    id="username" autocomplete="username"
                    value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
            </div>
            <div class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="password">
                    <?php esc_html_e('Password', 'woocommerce'); ?>&nbsp;<span class="required">*</span>
                </label>
                <input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password"
                    id="password" autocomplete="current-password" />
            </div>

            <?php do_action('woocommerce_login_form'); ?>

            <div class="form-row login-action-buttons" style="
                display: flex;
                flex-direction: column;
                justify-content: center;
                padding-bottom: 2em;
            ">
                <label
                    class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
                    <input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme"
                        type="checkbox" id="rememberme" value="forever" /> <span>
                        <?php esc_html_e('Remember me', 'woocommerce'); ?>
                    </span>
                </label>
                <?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
                <button type="submit"
                    style="margin: 3px;"
                    class="woocommerce-button button woocommerce-form-login__submit<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>"
                    name="login" value="<?php esc_attr_e('Log in', 'woocommerce'); ?>"><?php esc_html_e('Log in', 'woocommerce'); ?></button>
            </div>

            <a class="woocommerce-LostPassword lost_password"
                style="text-align: center;"
                href="<?php echo esc_url(wp_lostpassword_url()); ?>"><?php esc_html_e('Lost your password?', 'woocommerce'); ?></a>

            <?php do_action('woocommerce_login_form_end'); ?>

        </form>

        <?php if ('yes' === get_option('woocommerce_enable_myaccount_registration')): ?>

        </div>
        <div class="u-column2 col-2">

            <form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action('woocommerce_register_form_tag'); ?> style="
                padding: 2em 3em 3em 3em;
                border-radius: 30px;
                position: relative;
                display: flex;
                flex-direction: column;
                border-color: #f1f1f1;"
            >
                <figure style="
                    position: absolute;
                    width: 100%;
                    top: -40px;
                    left: 0;
                    display: flex;
                    justify-content: center;
                ">
                    <svg viewBox="0 0 60.098999 59.745998" style="width: fit-content;" height="80">
                        <rect style="fill:#1e84af;stroke:#ffffff;stroke-width:4.002px" id="rect7" width="56.097"
                            height="55.743999" x="2.0009999" y="2.0009999" ry="27.872" />
                        <path
                            d="m 30.033305,29.499566 a 8.0130214,7.7153269 0 0 0 8.00417,-7.706316 C 37.661679,11.593719 22.404612,11.595338 22.030149,21.793382 a 8.0127888,7.7151029 0 0 0 8.003156,7.706184 z m 0,-13.376797 c 7.782815,0.238097 7.781493,11.103699 -1.27e-4,11.340473 -7.78179,-0.23825 -7.779516,-11.103251 1.27e-4,-11.340514 z"
                            id="path7"
                            style="fill:#ffffff;fill-opacity:1;stroke:none;stroke-width:0.393136;stroke-dasharray:none;stroke-opacity:1" />
                        <path
                            d="m 30.033305,30.82393 c -7.798123,0.0064 -13.890426,6.486522 -13.123159,13.958504 0.09844,0.476231 0.532509,0.819221 1.036817,0.819264 h 24.173624 c 1.113831,0.01168 1.160401,-1.297016 1.126563,-2.054743 -0.0082,-7.023429 -5.919419,-12.71509 -13.213845,-12.723025 z M 41.132328,43.565374 H 18.935222 c 0.58904,-14.203463 21.61128,-14.195501 22.197106,0 z"
                            id="path185" sodipodi:nodetypes="ccccccccc"
                            style="fill:#ffffff;fill-opacity:1;stroke:none;stroke-width:0.393136;stroke-dasharray:none;stroke-opacity:1;paint-order:markers fill stroke" />
                        <g id="path114">
                            <path
                                style="color:#000000;fill:#1e84af;stroke-linecap:square;stroke-linejoin:round;-inkscape-stroke:none;paint-order:markers fill stroke"
                                d="m 36.227922,29.351078 v 3.839844 h -3.839844 l 1.399747,4.642048 h 3.839844 l 0.400339,5.141929 h 3.395988 a 0.8483426,0.8483426 135 0 0 0.848343,-0.848343 v -2.993454 h 2.839653 L 44.711653,34.590783 H 40.87181 v -3.839844 z"
                                id="path190" sodipodi:nodetypes="ccccccccccccc" inkscape:path-effect="#path-effect195"
                                inkscape:original-d="m 36.227922,29.351078 v 3.839844 h -3.839844 l 1.399747,4.642048 h 3.839844 l 0.400339,5.141929 h 4.244331 v -3.841797 h 2.839653 L 44.711653,34.590783 H 40.87181 v -3.839844 z" />
                            <path
                                style="color:#000000;fill:#ffffff;stroke-linecap:square;stroke-linejoin:round;-inkscape-stroke:none;paint-order:markers stroke fill;stroke:none;stroke-opacity:1;fill-opacity:1"
                                d="m 37.626953,30.208984 c -0.298444,8.45e-4 -0.540171,0.242572 -0.541016,0.541016 v 3.298828 h -3.298828 c -0.298444,8.46e-4 -0.54017,0.242572 -0.541015,0.541016 v 3.242187 c -2.3e-4,0.299206 0.24181,0.54212 0.541015,0.542969 h 3.298828 v 3.298828 c -2.3e-4,0.299207 0.241811,0.542121 0.541016,0.542969 h 3.244141 c 0.299968,2.29e-4 0.543197,-0.243001 0.542968,-0.542969 V 38.375 h 3.296875 c 0.299968,2.29e-4 0.543198,-0.243001 0.542969,-0.542969 v -3.242187 c -8.48e-4,-0.299205 -0.243762,-0.541246 -0.542969,-0.541016 H 41.414062 V 30.75 C 41.413214,30.450795 41.1703,30.208755 40.871094,30.208984 Z m 0.542969,1.083985 h 2.160156 v 3.296875 c -2.29e-4,0.299206 0.241811,0.54212 0.541016,0.542968 h 3.298828 v 2.158204 h -3.298828 c -0.298444,8.45e-4 -0.54017,0.242571 -0.541016,0.541015 v 3.300781 h -2.160156 v -3.300781 c -8.49e-4,-0.299205 -0.243763,-0.541245 -0.542969,-0.541015 h -3.296875 v -2.158204 h 3.296875 c 0.299968,2.29e-4 0.543198,-0.243 0.542969,-0.542968 z"
                                id="path191" inkscape:path-effect="#path-effect193"
                                inkscape:original-d="M 37.626953,30.208984 A 0.54255427,0.54255427 0 0 0 37.085937,30.75 v 3.298828 h -3.298828 a 0.54255427,0.54255427 0 0 0 -0.541015,0.541016 v 3.242187 A 0.54255427,0.54255427 0 0 0 33.787109,38.375 h 3.298828 v 3.298828 a 0.54255427,0.54255427 0 0 0 0.541016,0.542969 h 3.244141 a 0.54255427,0.54255427 0 0 0 0.542968,-0.542969 V 38.375 h 3.296875 a 0.54255427,0.54255427 0 0 0 0.542969,-0.542969 V 34.589844 A 0.54255427,0.54255427 0 0 0 44.710937,34.048828 H 41.414062 V 30.75 a 0.54255427,0.54255427 0 0 0 -0.542968,-0.541016 z m 0.542969,1.083985 h 2.160156 v 3.296875 a 0.54255427,0.54255427 0 0 0 0.541016,0.542968 h 3.298828 v 2.158204 h -3.298828 a 0.54255427,0.54255427 0 0 0 -0.541016,0.541015 v 3.300781 h -2.160156 v -3.300781 a 0.54255427,0.54255427 0 0 0 -0.542969,-0.541015 h -3.296875 v -2.158204 h 3.296875 a 0.54255427,0.54255427 0 0 0 0.542969,-0.542968 z" />
                        </g>
                    </svg>

                </figure>
                <h2 class="gradient title">
                    <?php esc_html_e('Register', 'woocommerce'); ?>
                </h2>
                <?php do_action('woocommerce_register_form_start'); ?>

                <?php if ('no' === get_option('woocommerce_registration_generate_username')): ?>

                    <div class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <label for="reg_username">
                            <?php esc_html_e('Username', 'woocommerce'); ?>&nbsp;<span class="required">*</span>
                        </label>
                        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username"
                            id="reg_username" autocomplete="username"
                            value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
                    </div>

                <?php endif; ?>

                <div class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                    <label for="reg_email">
                        <?php esc_html_e('Email address', 'woocommerce'); ?>&nbsp;<span class="required">*</span>
                    </label>
                    <input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email"
                        id="reg_email" autocomplete="email"
                        value="<?php echo (!empty($_POST['email'])) ? esc_attr(wp_unslash($_POST['email'])) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
                </div>

                <?php if ('no' === get_option('woocommerce_registration_generate_password')): ?>

                    <div class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <label for="reg_password">
                            <?php esc_html_e('Password', 'woocommerce'); ?>&nbsp;<span class="required">*</span>
                        </label>
                        <input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password"
                            id="reg_password" autocomplete="new-password" />
                    </div>

                <?php else: ?>

                    <p>
                        <?php esc_html_e('A link to set a new password will be sent to your email address.', 'woocommerce'); ?>
                    </p>

                <?php endif; ?>

                <?php do_action('woocommerce_register_form'); ?>

                <div class="woocommerce-form-row form-row register-action-buttons">
                    <?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
                    <button type="submit"
                        style="width: 100%;"
                        class="woocommerce-Button woocommerce-button button<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?> woocommerce-form-register__submit"
                        name="register" value="<?php esc_attr_e('Register', 'woocommerce'); ?>"><?php esc_html_e('Register', 'woocommerce'); ?></button>
                </div>

                <?php do_action('woocommerce_register_form_end'); ?>

            </form>

        </div>
    </div>
<?php endif; ?>

<?php do_action('woocommerce_after_customer_login_form'); ?>
