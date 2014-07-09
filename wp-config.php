<?php

/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'blogjobrely');

/** MySQL database username */
define('DB_USER', 'blogjobrely');

/** MySQL database password */
define('DB_PASSWORD', 'testas123');

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
define('AUTH_KEY',         '|(16j-/H|+vJg]3CHnCmHK<,T/`E4.gt~JD^f|@u -zWfzRppRILXm I{.o6 {lj');
define('SECURE_AUTH_KEY',  '*<;`(xuGPd2G&5D<y+c<5YNeAPxE$[Eb%84b(cN&r==z~H:fEDGn+R-40!jby<c#');
define('LOGGED_IN_KEY',    'nCQAUGb9e:5<Id8X)H!V? #JH9- Bk=18y]z7UsST+7T%;::BBz?Gqjc^$S2J[A#');
define('NONCE_KEY',        '4[3EBT~JL*?q;g9osQU%WhgGJ 4$|z3FW>Mf0JQ;==K-E?5C3]:uG|ve ,SOvZ&6');
define('AUTH_SALT',        '+iR|m Dy|2%Oaz,Bl&RpU1@ ! 36rB_/+MJFU$rMKMlaAnasBR0TOok-A{_ck^YB');
define('SECURE_AUTH_SALT', 'RlZE4G{vweoD|f0-GdJmo7D|+3YFz4*%gE`YW:1&uXAX>kX&KQ:{S4 TNZG-g(c+');
define('LOGGED_IN_SALT',   'E3- @YZr+3`5M=`A cOVc$@a$~n#MD^Ec|jWL2[d|>kl={0=_c_Rlj.Lo$|u@<-3');
define('NONCE_SALT',       '.`-<OB<Xf~m2 lO5X5+EJ)Ro:t[oi@U>fAGm&]Aq^/XRzdU>D7ZnriQd+u}H+0N^');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
define('WP_TEMP_DIR', ABSPATH . 'wp-content/');

