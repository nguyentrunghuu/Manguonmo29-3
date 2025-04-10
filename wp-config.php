<?php
//Begin Really Simple Security session cookie settings
@ini_set('session.cookie_httponly', true);
@ini_set('session.cookie_secure', true);
@ini_set('session.use_only_cookies', true);
//END Really Simple Security cookie settings
//Begin Really Simple Security key
define('RSSSL_KEY', 'KgLYzBb2h6qxfnEP4fGEg6qNYZjw4mP294YVPBbsy7biLQ6T8VB8WP5xEqnid9b2');
//END Really Simple Security key

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress1' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

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
define( 'AUTH_KEY',         '.eF>vb0Doz^%Dsa*u<eF|M/CSIATMcT`44x>[3hFEX7CB$BXpV%#R_|?;Q.m/u>s' );
define( 'SECURE_AUTH_KEY',  'P`IXVrqaz;eF^>H #_N9v-<a(J&tZ9e?Z$yNhst8u*w5shAA-!>[h Zp%*MNf@uI' );
define( 'LOGGED_IN_KEY',    'cpSzdXp49|:4Njp<F:QO}*kCQT^f.} <us~vM>_Q=Y}sno(a$~KVHnJ6<;>`4jt|' );
define( 'NONCE_KEY',        ')KJ_F<3Bu*&:$U{vQk~+Guj)K$Z/R5:%@TP ~B>5(k==7jZf/}Z3V3 p&8djgW$a' );
define( 'AUTH_SALT',        'DrUkj-(IB[G8-v~mA<e>0l0&T<O}EqUj=#9a0TUN}`B+ln=0>0>}[nzvSXBX(<^r' );
define( 'SECURE_AUTH_SALT', 'InW/^_xkGs)Ccuruioksu0Ja)+B3HMXG.WF@Gq<R}50QF?[:(nX<0kuL6YCRaPs|' );
define( 'LOGGED_IN_SALT',   'fFA/CEKy}vt2^.D/(1/9wj~8{:V9%!m$JiO!Yi-<k*orT{9SFK.3v;H$w`r{ vC.' );
define( 'NONCE_SALT',       'Rng#wYC;x23c9UZCi`=>@Y<p.gymIN@*jjKgKQag3~4&k_)]VZt:#_0],Oxc]aYj' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
