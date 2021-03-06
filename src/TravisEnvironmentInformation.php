<?php

/**
 * This file is part of phpcq/travis-configuration-check.
 *
 * (c) 2014-2020 Christian Schiffler, Tristan Lins
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    phpcq/travis-configuration-check
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan@lins.io>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2015-2020 Christian Schiffler <c.schiffler@cyberspectrum.de>, Tristan Lins <tristan@lins.io>
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
    const PHP_VERSIONS_MAINTAINED = '5.6,7.1,7.2,7.3,7.4';

    /**
     * The PHP versions in use at travis-ci.
     */
    const PHP_VERSIONS = 'nightly,7.4,7.3,7.2,7.1,5.6,5.5,5.4';

    //@codingStandardsIgnoreStart
    /**
     * The PHP extensions available at travis-ci.
     */
    const PHP_EXTENSIONS = 'bcmath,bz2,core,ctype,curl,date,dom,ereg,exif,fileinfo,filter,ftp,gd,gettext,hash,iconv,intl,json,libxml,mbstring,mcrypt,mysql,mysqli,mysqlnd,openssl,pcntl,pcre,pdo,pdo_mysql,pdo_pgsql,pdo_sqlite,pgsql,phar,posix,readline,reflection,session,shmop,simplexml,soap';
    // @codingStandardsIgnoreEnd
}
