<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */

if ($_SERVER['SERVER_NAME'] === 'localhost') {
	/** CONFIG DEV */
	define( 'DB_NAME', 'dbs11227724' );
	define( 'DB_USER', 'root' );
	define( 'DB_PASSWORD', '' );
	define( 'DB_HOST', 'localhost' );
} elseif ($_SERVER['SERVER_NAME'] === 'liligrow.es') {
	/** CONFIG PRODUCCION */
	define('DB_NAME', 'dbs11227724');
	define('DB_USER', 'dbu1351222');
	define('DB_PASSWORD', 'liligrow_hosting');
	define('DB_HOST', 'db5013392565.hosting-data.io');

    /* Add any custom values between this line and the "stop editing" line. */

    define('WP_SITEURL', 'https://www.liligrow.es');
    define('WP_HOME', 'https://www.liligrow.es');
    define('WP_MEMORY_LIMIT', '512M');
    define('WP_DEBUG_LOG', true);
    define('WP_DEBUG_DISPLAY', true);
    define('WP_CACHE_KEY_SALT', 'liligrow.es:');
    define('CK_WOO', 'ck_968f11dd925c7b4e39e8739606395e17753d940d');
    define('CS_WOO', 'cs_faa2de6e8fc676becdf65ed16bd4c46f1a97a90c');

    define( 'WP_REDIS_HOST', 'redis-15768.c309.us-east-2-1.ec2.cloud.redislabs.com' );
    define( 'WP_REDIS_PORT', 15768 );
    define( 'WP_REDIS_PASSWORD', 'LCEiyB3Xby122IV2G1Pg2pxxRbN8oecX' );
    define( 'WP_REDIS_PREFIX', 'llgr_' );
    define( 'WP_REDIS_DATABASE', 0 );
}

/** Database charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The database collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', 'q{JjwIs+2WnGrGihu+^[Y*#/.{cove;c-zmC!!&WX+r7r#Y(2q|Vhj|wn7zDvbPz');
define('SECURE_AUTH_KEY', '_+7V^@I.>MJ27uPOK8^m6ohtB3mU9>:zO?~2fm <[/D~M3,pa|K]`U;QhF9$E[=q');
define('LOGGED_IN_KEY', 'cF#e,J4)`-_B*E-y>%rkUll4:(uY@;}Hq(^1cC%o,D{)y4?xX~z0&# /3/zJk^kP');
define('NONCE_KEY', 'DfjyIC2Cp+|Z?sqY;|BjGx2?P$<s&/O{+?Bvz[qYLvK6D$r)2f9yGil.r9`C3%}>');
define('AUTH_SALT', 'ID@]tZR8rV6vLt$ghxnl7{brE#;+:T3kEe6M}tS9:<>Z`foiN<~`<>}8hizNW>xd');
define('SECURE_AUTH_SALT', 'mY8f;eLc14}!~QR9L>hW7AFE:+SIV:aXEln--vFK9nLbA)akVekww|=<v0r,B=hp');
define('LOGGED_IN_SALT', 'TG-mh;_-|Z}x%q:(&|q2-]0_r/$S-Cjop%!/|fSr*VecNjM8*IR<{oB7~UC.G2rJ');
define('NONCE_SALT', 'kFs*-;7?$K:!J)go}_ZVhC8l!|AK5y-qIllr~1bnk`1G|s$&oy{/FJAZ1v+G]g!(');

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */




/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if (!defined('ABSPATH')) {
	define('ABSPATH', __DIR__ . '/');
}

/** Enables page caching for Cache Enabler. */
if (!defined('WP_CACHE')) {
	}

define('WP_DEBUG', false);

/** Enables page caching for Cache Enabler. */
if ( ! defined( 'WP_CACHE' ) ) {
	define( 'WP_CACHE', true );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';