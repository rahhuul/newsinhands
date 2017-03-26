<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'newssite');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '5 0$4*]~M[{@1gLn9o(?*NRJQw6c7Z4t2wccdscl7@oLz%Xzd`v0ap]z3f {oZDk');
define('SECURE_AUTH_KEY',  '8`YS^?qX8)Su-/FTrL630u8GK:%}8E#k>y+2Of+.2l_U[W/@pD%z4?]#UkFW}oB ');
define('LOGGED_IN_KEY',    '*!^5@r]5JkMiiAw%/7zaRn vuPzyXd$_li5~WB~UD%H#x(BwhP#@.v%0p4,Enp&{');
define('NONCE_KEY',        '~mH4Op~c>0ti=o-$`7Dwuv<:rH/Ap}~}.mlBE;D|g-X({&IiZF8}Xz!p_h$eXfOm');
define('AUTH_SALT',        '@LbO,~-k{^Va1Es%Zyl#=+pZ`&C{)w7^^w-q9EE!4AMD8ufD.`SL#0Mdm1<[`0aE');
define('SECURE_AUTH_SALT', '#ph`~;~DxqmI(i_Qw3:>UQZqr/`Fp+?^|GZ;`2e5aKk?rki<2=zNWwOcG5*w=ym=');
define('LOGGED_IN_SALT',   'Npqn8)KTgX[BGBmZV<:x`T/m?EykHejdA.`K<;?62u$A[SV)4Y=3{y`/6NA-p/9T');
define('NONCE_SALT',       'h/UC0G$Ta}K3P7E9W1f]ifrJzeEPee+P(VA{W}V@|Aw#r-QGGkt+hN-[$dFg&DwG');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
