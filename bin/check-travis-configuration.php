#!/usr/bin/env php
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
 * @copyright  Christian Schiffler <c.schiffler@cyberspectrum.de>, Tristan Lins <tristan@lins.io>
 * @link       https://github.com/phpcq/travis-configuration-check
 * @license    https://github.com/phpcq/travis-configuration-check/blob/master/LICENSE MIT
 * @filesource
 */

error_reporting(E_ALL & ~ E_USER_DEPRECATED);

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
if ((!$loader = includeIfExists(__DIR__.'/../vendor/autoload.php'))
    && (!$loader = includeIfExists(__DIR__.'/../../../autoload.php'))
) {
    echo 'You must set up the project dependencies, run the following commands:'.PHP_EOL.
        'curl -sS https://getcomposer.org/installer | php'.PHP_EOL.
        'php composer.phar install'.PHP_EOL;
    exit(1);
}

set_error_handler(
    function ($errno, $errstr, $errfile, $errline) {
        if (0 === ($errno & error_reporting())) {
            return;
        }
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
);
// @codingStandardsIgnoreEnd

use PhpCodeQuality\TravisConfigurationCheck\Command\CheckTravisConfiguration;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Inline defined application.
 */
class ValidateBranchAliasApplication extends Application
{
    /**
     * Gets the name of the command based on input.
     *
     * @param InputInterface $input The input interface.
     *
     * @return string The command name
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getCommandName(InputInterface $input)
    {
        return 'phpcq:check-travis-configuration';
    }

    /**
     * Gets the default commands that should always be available.
     *
     * @return array An array of default Command instances
     */
    protected function getDefaultCommands()
    {
        // Keep the core default commands to have the HelpCommand
        // which is used when using the --help option
        $defaultCommands = parent::getDefaultCommands();

        $defaultCommands[] = new CheckTravisConfiguration();

        return $defaultCommands;
    }

    /**
     * {@inheritDoc}
     *
     * Overridden so that the application doesn't expect the command name to be the first argument.
     */
    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        // clear out the normal first argument, which is the command name
        $inputDefinition->setArguments();

        return $inputDefinition;
    }
}

$application = new ValidateBranchAliasApplication();
$application->setAutoExit(true);
$application->run();
