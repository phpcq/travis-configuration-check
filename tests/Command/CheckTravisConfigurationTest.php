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
namespace ContaoCommunityAlliance\BuildSystem\Tool\TravisConfigurationCheck\Test\Command;

use ContaoCommunityAlliance\BuildSystem\Tool\TravisConfigurationCheck\Command\CheckTravisConfiguration;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Tests for class CheckTravisConfiguration.
 *
 * @codingStandardsIgnoreStart
 * @coversDefaultClass \ContaoCommunityAlliance\BuildSystem\Tool\TravisConfigurationCheck\Command\CheckTravisConfiguration
 * @codingStandardsIgnoreEnd
 */
class CheckTravisConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Create a command instance with dummy input and output.
     *
     * @return CheckTravisConfiguration
     */
    protected function getCommand()
    {
        $command = new CheckTravisConfiguration();

        $reflection = new \ReflectionProperty(
            'ContaoCommunityAlliance\BuildSystem\Tool\TravisConfigurationCheck\Command\CheckTravisConfiguration',
            'input'
        );
        $reflection->setAccessible(true);
        $reflection->setValue($command, new StringInput(''));

        $reflection = new \ReflectionProperty(
            'ContaoCommunityAlliance\BuildSystem\Tool\TravisConfigurationCheck\Command\CheckTravisConfiguration',
            'output'
        );
        $reflection->setAccessible(true);
        $reflection->setValue($command, new BufferedOutput());

        return $command;
    }

    /**
     * Read the output buffer from the output attached to the command.
     *
     * @param CheckTravisConfiguration $command The command.
     *
     * @return string
     */
    protected function getOutputFromCommand($command)
    {
        $reflection = new \ReflectionProperty(
            'ContaoCommunityAlliance\BuildSystem\Tool\TravisConfigurationCheck\Command\CheckTravisConfiguration',
            'output'
        );
        $reflection->setAccessible(true);

        $output = $reflection->getValue($command);

        return $output->fetch();
    }

    /**
     * Test CheckTravisConfiguration::validatePhpVersionComposerJson.
     *
     * @covers CheckTravisConfiguration::validatePhpVersionComposerJson
     *
     * @return void
     */
    public function testValidatePhpVersionInComposerJson()
    {
        $command = $this->getCommand();

        $this->assertTrue(
            $command->validatePhpVersionComposerJson(
                array(
                    'require' => array(
                        'php' => '~5.3'
                    )
                )
            ),
            $this->getOutputFromCommand($command)
        );

        $this->assertTrue(
            $command->validatePhpVersionComposerJson(
                array(
                    'require' => array(
                        'php' => '>=5.3,<1.8'
                    )
                )
            ),
            $this->getOutputFromCommand($command)
        );

        $this->assertFalse(
            $command->validatePhpVersionComposerJson(
                array(
                    'require' => array(
                        'symfony/console' => '~2.3'
                    )
                )
            ),
            $this->getOutputFromCommand($command)
        );

        $this->assertFalse(
            $command->validatePhpVersionComposerJson(
                array(
                    'require' => array(
                        'php' => 'invalid-constraint'
                    )
                )
            ),
            $this->getOutputFromCommand($command)
        );
    }

    /**
     * Test CheckTravisConfiguration::validatePhpVersionTravisYml.
     *
     * @covers CheckTravisConfiguration::validatePhpVersionTravisYml
     *
     * @return void
     */
    public function testValidatePhpVersionTravisYml()
    {
        $command = $this->getCommand();

        $this->assertTrue(
            $command->validatePhpVersionTravisYml(
                array(
                    'php' => array('5.3')
                )
            ),
            $this->getOutputFromCommand($command)
        );

        $this->assertTrue(
            $command->validatePhpVersionTravisYml(
                array(
                    'php' => array(
                        '5.3',
                        '5.4',
                        '5.5',
                        '5.6'
                    )
                )
            ),
            $this->getOutputFromCommand($command)
        );

        $this->assertFalse(
            $command->validatePhpVersionTravisYml(
                array(
                )
            )
        );

        $this->assertFalse(
            $command->validatePhpVersionTravisYml(
                array(
                    'php' => array('invalid-constraint')
                )
            ),
            $this->getOutputFromCommand($command)
        );
    }

    /**
     * Provide data for testValidatePhpVersionAgainstTravis.
     *
     * @return array
     */
    public function prepareVersionsForValidatePhpVersionAgainstTravis()
    {
        $versions = array
        (
            array(
                'composer' => '>=5.3',
                'travis'   => array('5.3'),
                'expect'   => true
            ),
            array(
                'composer' => '>=5.3',
                'travis'   => array(
                    '5.3'
                ),
                'expect'   => true
            ),
            array(
                'composer' => '>=5.4',
                'travis'   => array(
                    '5.3',
                    '5.4',
                    '5.5',
                    '5.6'
                ),
                'expect'   => false
            ),
            array(
                'composer' => '>=5.4',
                'travis'   => array(
                    '5.5',
                    '5.6'
                ),
                'expect'   => true
            ),
             array(
                'composer' => '>=5.4.3',
                'travis'   => array(
                    '5.4',
                    '5.5',
                    '5.6'
                ),
                'expect'   => true
            ),
             array(
                'composer' => '>=5.4.3',
                'travis'   => array(
                    '5.3',
                    '5.4',
                    '5.5',
                    '5.6'
                ),
                'expect'   => false
            ),
        );

        return array_map(function ($arr) {
            return array(
                array(
                    'require' => array(
                        'php' => $arr['composer']
                    )
                ),
                array(
                    'php' => $arr['travis'],
                ),
                $arr['expect']
            );
        }, $versions);
    }

    /**
     * Test CheckTravisConfiguration::validatePhpVersionAgainstTravis.
     *
     * @param array $composer The contents of the composer.json.
     *
     * @param array $travis   The contents of the .travis.yml.
     *
     * @param bool  $expected The expected result.
     *
     * @return void
     *
     * @dataProvider prepareVersionsForValidatePhpVersionAgainstTravis
     *
     * @covers       CheckTravisConfiguration::validatePhpVersionAgainstTravis
     */
    public function testValidatePhpVersionAgainstTravis($composer, $travis, $expected)
    {
        $command = $this->getCommand();

        $this->assertEquals(
            $expected,
            $command->validatePhpVersionAgainstTravis($composer, $travis),
            'composer.json: ' . $composer['require']['php'] . ' .travis.yml: ' . implode(',', $travis['php']) .
            ' should ' . ($expected ? 'validate ' : 'not validate. ') . $this->getOutputFromCommand($command)
        );
    }

    /**
     * Provide data for testValidateTravisContainsAllSupportedPhpVersions.
     *
     * @return array
     */
    public function prepareValidateTravisContainsAllSupportedPhpVersions()
    {
        $versions = array
        (
            array(
                'composer'  => '>=5.3',
                'travis'    => array('5.3'),
                'supported' => array(
                ),
                'expect'    => false
            ),
            array(
                'composer'  => '>=5.3',
                'travis'    => array(
                    '5.3',
                    '5.4'
                ),
                'supported' => array(),
                'expect'    => false
            ),
            array(
                'composer'  => '>=5.2',
                'travis'    => array(
                    '5.2',
                    '5.3',
                    '5.4'
                ),
                'supported' => array(
                    '5.3',
                    '5.4'
                ),
                'expect'    => false
            ),
            array(
                'composer'  => '>=5.3',
                'travis'    => array(
                    '5.4'
                ),
                'supported' => array(
                    '5.3',
                    '5.4'
                ),
                'expect'    => false
            ),
            array(
                'composer'  => '>=5.3',
                'travis'    => array(
                    '5.3',
                    '5.4'
                ),
                'supported' => array(
                    '5.3',
                    '5.4'
                ),
                'expect'    => true
            ),
            array(
                'composer'  => '>=5.4,<5.6',
                'travis'    => array(
                    '5.4',
                    '5.5',
                ),
                'supported' => array(
                    '5.3',
                    '5.4',
                    '5.5',
                    '5.6',
                ),
                'expect'    => true
            ),
        );

        return array_map(function ($arr) {
            return array(
                array(
                    'require' => array(
                        'php' => $arr['composer']
                    )
                ),
                array(
                    'php' => $arr['travis'],
                ),
                $arr['supported'],
                $arr['expect']
            );
        }, $versions);
    }

    /**
     * Test CheckTravisConfiguration::validateTravisContainsAllSupportedPhpVersions.
     *
     * @param array $composer          The contents of the composer.json.
     *
     * @param array $travis            The contents of the .travis.yml.
     *
     * @param array $supportedVersions The available php versions at travis-ci.
     *
     * @param bool  $expected          The expected result.
     *
     * @return void
     *
     * @dataProvider prepareValidateTravisContainsAllSupportedPhpVersions
     *
     * @covers       CheckTravisConfiguration::validateTravisContainsAllSupportedPhpVersions
     */
    public function testValidateTravisContainsAllSupportedPhpVersions($composer, $travis, $supportedVersions, $expected)
    {
        $command = $this->getCommand();

        $this->assertEquals(
            $expected,
            $command->validateTravisContainsAllSupportedPhpVersions($composer, $travis, $supportedVersions),
            sprintf(
                'travis-ci knows: "%s" .travis.yml: "%s" should %s - command output: "%s"',
                implode(',', $supportedVersions),
                implode(',', $travis['php']),
                ($expected ? 'validate. ' : 'not validate. '),
                $this->getOutputFromCommand($command)
            )
        );
    }

    /**
     * Provide data for testValidateNoUnmaintainedPhpVersions.
     *
     * @return array
     */
    public function prepareValidateNoUnmaintainedPhpVersions()
    {
        $versions = array
        (
            array(
                'composer'  => '>=5.3',
                'travis'    => array(
                    '5.3'
                ),
                'supported' => array(
                ),
                'expect'    => false
            ),
            array(
                'composer'  => '>=5.3',
                'travis'    => array(
                    '5.3',
                    '5.4'
                ),
                'supported' => array(
                ),
                'expect'    => false
            ),
            array(
                'composer'  => '>=5.2',
                'travis'    => array(
                    '5.2',
                    '5.3',
                    '5.4'
                ),
                'supported' => array(
                    '5.3',
                    '5.4'
                ),
                'expect'    => false
            ),
            array(
                'composer'  => '>=5.3',
                'travis'    => array(
                    '5.4'
                ),
                'supported' => array(
                    '5.3',
                    '5.4'
                ),
                'expect'    => true
            ),
            array(
                'composer'  => '>=5.3',
                'travis'    => array(
                    '5.3',
                    '5.4'
                ),
                'supported' => array(
                    '5.3',
                    '5.4'
                ),
                'expect'    => true
            ),
            array(
                'composer'  => '>=5.4,<5.6',
                'travis'    => array(
                    '5.4',
                    '5.5',
                ),
                'supported' => array(
                    '5.3',
                    '5.4',
                    '5.5',
                    '5.6',
                ),
                'expect'    => true
            ),
            array(
                'composer'  => null,
                'travis'    => array(
                    '5.4',
                    '5.5',
                ),
                'supported' => array(
                    '5.3',
                    '5.4',
                    '5.5',
                    '5.6',
                ),
                'expect'    => true
            ),
            array(
                'composer'  => null,
                'travis'    => null,
                'supported' => array(
                    '5.3',
                    '5.4',
                    '5.5',
                    '5.6',
                ),
                'expect'    => true
            ),
        );

        return array_map(function ($arr) {
            return array(
                array(
                    'require' => $arr['composer'] ? array(
                        'php' => $arr['composer']
                    ) : array()
                ),
                $arr['travis'] ? array(
                    'php' => $arr['travis'],
                ) : array(),
                $arr['supported'],
                $arr['expect']
            );
        }, $versions);
    }

    /**
     * Test CheckTravisConfiguration::validateNoUnmaintainedPhpVersions.
     *
     * @param array $composer          The contents of the composer.json.
     *
     * @param array $travis            The contents of the .travis.yml.
     *
     * @param array $supportedVersions The maintained php versions from php.net.
     *
     * @param bool  $expected          The expected result.
     *
     * @return void
     *
     * @dataProvider prepareValidateNoUnmaintainedPhpVersions
     *
     * @covers       CheckTravisConfiguration::validateNoUnmaintainedPhpVersions
     */
    public function testValidateNoUnmaintainedPhpVersions($composer, $travis, $supportedVersions, $expected)
    {
        $command = $this->getCommand();

        $this->assertEquals(
            $expected,
            $command->validateNoUnmaintainedPhpVersions($composer, $travis, $supportedVersions),
            sprintf(
                'maintained PHP versions: "%s" .travis.yml specifies: "%s" composer.json specifies: "%s" ' .
                ' should %s - command output: "%s"',
                implode(',', $supportedVersions),
                implode(',', isset($travis['php']) ? $travis['php'] : array()),
                isset($composer['require']['php']) ? $composer['require']['php'] : '',
                ($expected ? 'validate. ' : 'not validate. '),
                $this->getOutputFromCommand($command)
            )
        );
    }
}
