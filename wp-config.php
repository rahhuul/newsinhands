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

//define('WP_ALLOW_REPAIR', true);
/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'Nx,xdU@%:$QIeC](<`V3}} g38a6F2qC`1wBIZN]5/{bQUq|h567O@CN./3J/d*!');
define('SECURE_AUTH_KEY',  'D}9XEYD3>yjK5de$muB_aQEqyw:P?-]_p4WDLoe9.ad7F>T-muegDk#lKIHTK):O');
define('LOGGED_IN_KEY',    'c=OJz$Qdi6&^jU6Llo}8vG(|yCDott>:w#3*X|x:wCh(v!7o#XVdB(r{4GK=DAHf');
define('NONCE_KEY',        '_2-j-H%9G;DD:d1+ePxo{})[lfBpQGA@Qq8o_~k7s{HJZBYmQMSi]H<^@5~*Er}D');
define('AUTH_SALT',        'SA]r,zAWh>gz5AlVxP^lGM=q$-3_JvEU9AmzI$K|C?{_5R|B~)>3`I4t!=(?~}Q_');
define('SECURE_AUTH_SALT', '0Y<m~,~|]2(@%Vrkz9WFT2ea u;IjpfiUO1.)1$:0P;F%?e[|]xi^;.ww]g1umyL');
define('LOGGED_IN_SALT',   'ddZ@1$[bkrDylT!@>WUM@j2E<8&1Q_Ldu_W)_nx9@^XkD_n$j|w? pMqNqc#Zd[s');
define('NONCE_SALT',       'f*u)X{/Ka;tyyy7E^hymQna8FFXhK6hH,X:x]eO~7hv9U?X?<|VM2Z3j $W`wi<c');

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
