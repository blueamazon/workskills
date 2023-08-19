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
define( 'DB_NAME', 'workskills' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'password' );

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
define( 'AUTH_KEY',         'yA!3#,*L1d?ZISo4UUV.9b2g|nZ](3~CyxM~7nLRJC01XArnaTYg~-,l#zGGo@ D' );
define( 'SECURE_AUTH_KEY',  'QKVbzRJEP$i}K3c_YS&de/JbSA5Sq@;Lg/_@&zy,Tt0!u>VlSTvDO?f5g{C;=4>>' );
define( 'LOGGED_IN_KEY',    ':!K|YaYz&|1jW1zYHH5gV_M({]hik@u9 LO2_58%n+T$/sXDaexc+{PG.1.er[H2' );
define( 'NONCE_KEY',        'U}p{/%t+ g6bKPCn9#q(mFx(om>fF/kAb-=:FoCY9J|Np&|J*Z<[eb pKc8wF?7y' );
define( 'AUTH_SALT',        'UV5bRYfNiGQq{*v/ o=3&+nuQHEIAw>P6.5kjW@/|j+jC#93G;RtSX@.dAF:L~8$' );
define( 'SECURE_AUTH_SALT', '^aky6{l,%N3@-H8+91MB~H*FTp%cTv5d2gf67?;fc1Ffdd:%*v qJ-%[C(2 ~*9Q' );
define( 'LOGGED_IN_SALT',   ' zI<xOY}wxg~D3ScuDx<__NN#G-mErH4,_G[|2Q:*11OVC+r+`8@*((W.5.i7P#9' );
define( 'NONCE_SALT',       'XF_P1W`Cnpg[SkJ/1>PLk<.-( A)}+Ux_cELyP^S*<|<=ON+d{a.gD$:*3{iqo13' );

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
define( 'WP_DEBUG', true );
define('WP_DEBUG_DISPLAY', false);
define('WP_DEBUG_LOG', true);
/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
