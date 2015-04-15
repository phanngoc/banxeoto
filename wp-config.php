<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, and ABSPATH. You can find more information by visiting
 * {@link http://codex.wordpress.org/Editing_wp-config.php Editing wp-config.php}
 * Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wptest');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         'gr|4jIe+tX2hHVgg?y8FdtTc3zbi`ymzJeholn/PDt}uIQc:&yR]- ~AKY;Tkm6+');
define('SECURE_AUTH_KEY',  '|%TDuxlZdf8:dRA0{wuCy.k#L3/`FP+R),9/]s<].<{ozv6X6_Je%*]|y<&Gn6Uv');
define('LOGGED_IN_KEY',    '@,}gKT89X,@y-XMpv y<f:z2-=|?Ed2H1eOCw)g$10MSyqo|ZBw%&][RlClz[wU,');
define('NONCE_KEY',        '+fms#Mqr}A9D~=r3k,v{+K7yC`9;yU]#]%[5/{d|9g>?E}9tnfDL z ,39,>Jyp+');
define('AUTH_SALT',        '|Xrmsa$<=SNk#QvJNo^cQWDOym+n1=8!9CTTsh/D}eZr78pCV cYjXZZDN=|R4A-');
define('SECURE_AUTH_SALT', 'Vo]iyY;jvy6UPWOfb(|`J!Z06#k:d/Ha-L@0K(7u?n,|l;#szq1]N+8<-:MnLd++');
define('LOGGED_IN_SALT',   'ilD)dQe#kZ8(SBU+<;19:8(T+e(xG4:vcVYJ<e!+hZC,e:#>1hNOve5_[uV|FJ<:');
define('NONCE_SALT',       'JRWGL6Q/a wc,ig+(@;O{oIR>}J:9WSL60lqPsSmhdD|)*,a[]*(T mmVa4X`Gnj');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Disable display of errors and warnings 
define('WP_DEBUG_DISPLAY', true);
/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
