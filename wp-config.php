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
define('DB_NAME', 'viewlift');

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
define('AUTH_KEY',         '2gvIEaRs$yMfKen$$:=Q$O<a{0 po@K;QdNu(>E@JX/`9}O<9*d2dAjI/(y>+SI<');
define('SECURE_AUTH_KEY',  'sY^p)H8=T A6ylJOyo-T<#v,}M!*5c.qRJz^fiL<e=>FzGzd@J<~@_`-4jmvZtpx');
define('LOGGED_IN_KEY',    '!hQ Hhg`M%ZpwHJNYtVp8YdY7$eC&b&NjEbV8TprYpvkytL^?Hl*`tm>JllD+j?%');
define('NONCE_KEY',        ':og8Ph6(q7vvIqtwCLM?#>v{F%{8VvBSY/|NL~({F+40KfZeW>#%:I9EfvaCTiqx');
define('AUTH_SALT',        '%qdtc)`wrj/TuZF][M7Kl@AzJ?H?O#xx&}[&% MA$Ktc#C*;ky?%)p&I[4J}BUIO');
define('SECURE_AUTH_SALT', 'F)]f~!mq<{1:5^AgD]_jep%zBtGSPKM9ps4N=#x[a}Sr(pT(&@R,AjW#7GWNOi0*');
define('LOGGED_IN_SALT',   'EgsFtP%UDAzSh<qNoRxO9uR$b+f _ir,uB>4Bxgq}s0ca@P?)h]!aJy>863BrTv]');
define('NONCE_SALT',       'bqw6ty&{rJ=9kmrYw@XRkO,;}xVPVP=BkQ? !C5RkO=$]%^,StQ(>JTxMZodQFWm');

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
define('WP_DEBUG', true);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
