<?php

/**
 * This file is part of phpcq/travis-configuration-check.
 *
 * (c) 2014 Christian Schiffler, Tristan Lins
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    phpcq/travis-configuration-check
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan@lins.io>
 * @copyright  2015 Christian Schiffler <c.schiffler@cyberspectrum.de>, Tristan Lins <tristan@lins.io>
 * @license    https://github.com/phpcq/travis-configuration-check/blob/master/LICENSE MIT
 * @link       https://github.com/phpcq/travis-configuration-check
 * @filesource
 */

namespace PhpCodeQuality\TravisConfigurationCheck;

/**
 * Simple container to keep hold of the versions of PHP in travis-ci and the active extensions.
 *
 * Auto generated via update-travis-constants.php.
 *
 * @author Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
interface TravisEnvironmentInformation
{
    /**
     * The PHP versions currently maintained by php.org.
     */
    const PHP_VERSIONS_MAINTAINED = '5.4,5.5,5.6';

    /**
     * The PHP versions in use at travis-ci.
     */
    const PHP_VERSIONS = 'nightly,7.0,5.6,5.5,5.4,5.3';

    //@codingStandardsIgnoreStart
    /**
     * The PHP extensions available at travis-ci.
     */
    const PHP_EXTENSIONS = 'bcmath,bz2,core,ctype,curl,date,dom,ereg,exif,fileinfo,filter,ftp,gd,gettext,hash,iconv,intl,json,libxml,mbstring,mcrypt,mysql,mysqli,mysqlnd,openssl,pcntl,pcre,pdo,pdo_mysql,pdo_pgsql,pdo_sqlite,pgsql,phar,posix,readline,reflection,session,shmop,simplexml,soap';
    // @codingStandardsIgnoreEnd
}
