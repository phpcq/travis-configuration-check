<?php

/*
 * This file is auto generated, DO NOT MODIFY!!!!!!
 *
 * Run update-travis-constants.php from the project root to update the information.
 *
 * Last time generated: Sat 2014-10-11 09-10-00
 *
 * @copyright 2014 Contao Community Alliance <https://c-c-a.org>
 * @author    Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @package   contao-community-alliance/build-system-tool-travis-configuration-check
 * @license   MIT
 * @link      https://c-c-a.org
 */

namespace ContaoCommunityAlliance\BuildSystem\Tool\TravisConfigurationCheck;

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
    const PHP_VERSIONS = '5.6,5.5,5.4,5.3';

    /**
     * The PHP extensions available at travis-ci.
     * @codingStandardsIgnoreStart
     */
    const PHP_EXTENSIONS = 'bcmath,bz2,core,ctype,curl,date,dom,ereg,exif,fileinfo,filter,ftp,gd,gettext,hash,iconv,intl,json,libxml,mbstring,mcrypt,mysql,mysqli,mysqlnd,openssl,pcntl,pcre,pdo,pdo_mysql,pdo_pgsql,pdo_sqlite,pgsql,phar,posix,readline,reflection,session,shmop,simplexml,soap';
    // @codingStandardsIgnoreEnd
}
