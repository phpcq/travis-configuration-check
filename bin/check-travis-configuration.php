#!/usr/bin/env php
<?php

/**
 * This file is part of the Contao Community Alliance Build System tools.
 *
 * @copyright 2014 Contao Community Alliance <https://c-c-a.org>
 * @author    Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @package   contao-community-alliance/build-system-tool-travis-configuration-check
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
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
);
// @codingStandardsIgnoreEnd

use ContaoCommunityAlliance\BuildSystem\Tool\TravisConfigurationCheck\Command\CheckTravisConfiguration;
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
        return 'ccabs:tools:check-travis-configuration';
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
