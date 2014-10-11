#!/usr/bin/env php
<?php

/**
 * This file is part of the Contao Community Alliance Build System tools.
 *
 * @copyright 2014 Contao Community Alliance <https://c-c-a.org>
 * @author    Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @package   contao-community-alliance/build-system-check-travis-configuration
 * @license   MIT
 * @link      https://c-c-a.org
 */

error_reporting(E_ALL);

/**
 * Try to load the autoloader from the given file.
 *
 * @param string $file The file to include.
 *
 * @return bool|\Composer\Autoload\ClassLoader
 *
 * @codingStandardsIgnoreStart
 */
function includeIfExists($file)
{
    return file_exists($file) ? include $file : false;
}
if ((!$loader = includeIfExists(__DIR__.'/vendor/autoload.php'))
    && (!$loader = includeIfExists(__DIR__.'/../../autoload.php'))
) {
    echo 'You must set up the project dependencies, run the following commands:'.PHP_EOL.
        'curl -sS https://getcomposer.org/installer | php'.PHP_EOL.
        'php composer.phar install'.PHP_EOL;
    exit(1);
}

set_error_handler(
    function ($errno, $errstr, $errfile, $errline) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
);
// @codingStandardsIgnoreEnd

/**
 * Download the given url and return as string.
 *
 * @param string $url The url to download.
 *
 * @return null|string
 *
 * @throws \Exception When a redirect location could not be retrieved.
 *
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 *
 * @codingStandardsIgnoreStart
 */
function fgetDownload($url)
{
    $return = file_get_contents($url);
    // $http_response_header becomes available by magic when issuing the fopen() call above.
    $headers              = $http_response_header;
    $firstHeaderLine      = $headers[0];
    $firstHeaderLineParts = explode(' ', $firstHeaderLine);

    if ($firstHeaderLineParts[1] == 301 || $firstHeaderLineParts[1] == 302) {
        foreach ($headers as $header) {
            $matches = array();
            preg_match('/^Location:(.*?)$/', $header, $matches);
            $url = trim(array_pop($matches));
            return fgetDownload($url);
        }
        throw new \Exception('Can\'t get the redirect location');
    }

    return $return;
}
// @codingStandardsIgnoreEnd

use ContaoCommunityAlliance\BuildSystem\Tool\TravisConfigurationCheck\TravisEnvironmentInformation;
use Symfony\Component\Process\Process;

echo "Checking information for travis-ci...\n";

$travisBuildSetup = fgetDownload(
    'https://raw.githubusercontent.com/travis-ci/docs-travis-ci-com/gh-pages/user/ci-environment.md'
);

$phpSection = substr($travisBuildSetup, (strpos($travisBuildSetup, '## PHP VM images') + 16));
$phpSection = substr($phpSection, 0, strpos($phpSection, "\n## ", 5) ?: 10);

$sections = array();
$pos      = strpos($phpSection, '### ');
while ($pos !== false) {

    $eol              = strpos($phpSection, "\n", $pos);
    $title            = strtolower(trim(substr($phpSection, ($pos + 4), ($eol - $pos - 4))));
    $pos              = strpos($phpSection, '### ', $eol);
    $sections[$title] = trim(substr($phpSection, $eol, ($pos - $eol)));
}

preg_match_all("!^\*\s([0-9.]*)\n!m", $sections['php versions'], $matches, PREG_PATTERN_ORDER);
$time       = date('D Y-m-d H-n-s');
$versions   = implode(',', $matches[1]);
$extensions = implode(',', array_filter(array_map(
    function ($val) {
        // skip subsection.
        if (strpos($val, '[') !== false) {
            return null;
        }
        return strtolower(trim($val));
    },
    explode("\n", $sections['extensions'])
)));

// @codingStandardsIgnoreStart

echo "Checking versions for php.net...\n";

$content =
    substr(
        file_get_contents(
            'https://raw.githubusercontent.com/php/web-php/master/include/version.inc'
        ),
        5
    );

eval($content);

$branches = array();
foreach ($GLOBALS['RELEASES'] as $major => $releases) {
    foreach ($releases as $version => $release) {
        if (empty($release['eol'])) {
            // This branch isn't EOL: add it to our array.
            $branches[] = implode('.', array_slice(explode('.', $version), 0, 2));
        }
    }
}

sort($branches);
$activePHPVersions = implode(',', $branches);
// @codingStandardsIgnoreEnd

if (($activePHPVersions == TravisEnvironmentInformation::PHP_VERSIONS_MAINTAINED)
    && ($versions == TravisEnvironmentInformation::PHP_VERSIONS)
    && ($extensions == TravisEnvironmentInformation::PHP_EXTENSIONS)
) {
    echo "No changes at travis-ci or php.net found - TravisEnvironmentInformation.php not updated.\n";
    return;
}

file_put_contents(
    __DIR__ . '/src/TravisEnvironmentInformation.php',
    <<<EOF
<?php

/*
 * This file is auto generated, DO NOT MODIFY!!!!!!
 *
 * Run update-travis-constants.php from the project root to update the information.
 *
 * Last time generated: $time
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
    const PHP_VERSIONS_MAINTAINED = '$activePHPVersions';

    /**
     * The PHP versions in use at travis-ci.
     */
    const PHP_VERSIONS = '$versions';

    /**
     * The PHP extensions available at travis-ci.
     * @codingStandardsIgnoreStart
     */
    const PHP_EXTENSIONS = '$extensions';
    // @codingStandardsIgnoreEnd
}

EOF
);

echo "TravisEnvironmentInformation.php has been updated with the latest information.\n";
echo "Please commit the new version to git and tag a release.\n";
$process = new Process('git diff');
$process->run();
echo $process->getOutput();
exit(1);
