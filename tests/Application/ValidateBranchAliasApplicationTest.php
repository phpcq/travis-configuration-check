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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2014-2020 Christian Schiffler <c.schiffler@cyberspectrum.de>, Tristan Lins <tristan@lins.io>
 * @license    https://github.com/phpcq/travis-configuration-check/blob/master/LICENSE MIT
 * @link       https://github.com/phpcq/travis-configuration-check
 * @filesource
 */

namespace PhpCodeQuality\TravisConfigurationCheck\Test\Application;

use PhpCodeQuality\TravisConfigurationCheck\Application\ValidateBranchAliasApplication;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * @covers \PhpCodeQuality\TravisConfigurationCheck\Application\ValidateBranchAliasApplication
 * @covers \PhpCodeQuality\TravisConfigurationCheck\Command\CheckTravisConfiguration
 */
class ValidateBranchAliasApplicationTest extends TestCase
{
    public function testApplication()
    {
        $input = new ArrayInput(['--help' => '']);
        $output = new TestOutput();

        $application = new ValidateBranchAliasApplication();
        self::assertSame($application->doRun($input, $output), 0);
        self::assertNotEmpty($output->output);
        self::assertTrue($application->has('phpcq:check-travis-configuration'));
        $application->setAutoExit(false);
        self::assertFalse($application->isAutoExitEnabled());
        $application->setAutoExit(true);
        self::assertTrue($application->isAutoExitEnabled());
    }
}
