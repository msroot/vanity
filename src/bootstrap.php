<?php
/**
 * Copyright (c) 2009-2012 [Ryan Parman](http://ryanparman.com)
 * Copyright (c) 2011-2012 [Ryan McCue](http://ryanmccue.info)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * <http://www.opensource.org/licenses/mit-license.php>
 */


/********************************************************/
// PREPARATION

if (php_sapi_name() !== 'cli') {
	die('Must run from command line');
}

$start_time = time();
error_reporting(-1);

ini_set('display_errors', 1);
ini_set('log_errors', 0);
ini_set('html_errors', 0);


/********************************************************/
// CONSTANTS

define('TAB', '    ');

define('VANITY_VERSION', '3.0alpha-' . gmdate('Ymd', filemtime(__FILE__)));

define('VANITY_SYSTEM', dirname(__DIR__)              );
define('VANITY_VENDOR', VANITY_SYSTEM . '/src/vendor' );

define('USER_PROFILE', $_SERVER['HOME'] ?: VANITY_SYSTEM );
define('USER_DATA',    USER_PROFILE . '/.vanity'         );

define('VANITY_CACHE_DIR',         USER_DATA . '/cache'                    );
define('VANITY_PHPREF_DIR',        VANITY_CACHE_DIR . '/php'               );
define('VANITY_ENTITY_GLOBAL_DIR', VANITY_CACHE_DIR . '/entities'          );
define('VANITY_ENTITY_LANG_DIR',   VANITY_CACHE_DIR . '/language-entities' );

define('VANITY_PROJECT_WORKING_DIR', getcwd()                                );
define('VANITY_PROJECT_CONFIG_DIR',  VANITY_PROJECT_WORKING_DIR . '/_vanity' );


/********************************************************/
// INCLUDES & NAMESPACES

require_once VANITY_VENDOR . '/symfony/class-loader/Symfony/Component/ClassLoader/UniversalClassLoader.php';
require_once VANITY_VENDOR . '/symfony/class-loader/Symfony/Component/ClassLoader/ApcUniversalClassLoader.php';

use Symfony\Component\ClassLoader\ApcUniversalClassLoader,
	Symfony\Component\ClassLoader\UniversalClassLoader;


/********************************************************/
// APP

if (extension_loaded('apc'))
{
	$loader = new ApcUniversalClassLoader('apc.prefix.');
}
else
{
	$loader = new UniversalClassLoader();
}

$loader->registerNamespaces(array(
	'Vanity'                               => __DIR__ . '/src',
	'Symfony\\Component\\ClassLoader'      => VANITY_VENDOR . '/symfony/class-loader',
	'Symfony\\Component\\Console'          => VANITY_VENDOR . '/symfony/console',
	'Symfony\\Component\\EventDispatcher'  => VANITY_VENDOR . '/symfony/event-dispatcher',
	'Symfony\\Component\\Filesystem'       => VANITY_VENDOR . '/symfony/filesystem',
	'Symfony\\Component\\Process'          => VANITY_VENDOR . '/symfony/process',
	'Doctrine\\Common'                     => VANITY_VENDOR . '/doctrine/common/lib',
	'Docblox'                              => VANITY_VENDOR . '/docblox/docblox/src',
));

$loader->register();
