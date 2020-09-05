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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2014-2020 Christian Schiffler <c.schiffler@cyberspectrum.de>, Tristan Lins <tristan@lins.io>
 * @link       https://github.com/phpcq/travis-configuration-check
 * @license    https://github.com/phpcq/travis-configuration-check/blob/master/LICENSE MIT
 * @filesource
 */

namespace PhpCodeQuality\TravisConfigurationCheck\Test\Command;

use PhpCodeQuality\TravisConfigurationCheck\Command\CheckTravisConfiguration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Tests for class CheckTravisConfiguration.
 *
 * @covers  \PhpCodeQuality\TravisConfigurationCheck\Command\CheckTravisConfiguration
 */
class CheckTravisConfigurationTest extends TestCase
{
    /**
     * Create a command instance with dummy input and output.
     *
     * @return CheckTravisConfiguration
     */
    protected function getCommand()
    {
        $command = new CheckTravisConfiguration();

        $reflection = new \ReflectionProperty(CheckTravisConfiguration::class, 'input');
        $reflection->setAccessible(true);
        $reflection->setValue($command, new StringInput(''));

        $reflection = new \ReflectionProperty(CheckTravisConfiguration::class, 'output');
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
        $reflection = new \ReflectionProperty(CheckTravisConfiguration::class, 'output');
        $reflection->setAccessible(true);

        $output = $reflection->getValue($command);

        return $output->fetch();
    }

    /**
     * Test CheckTravisConfiguration::validatePhpVersionComposerJson.
     *
     * @return void
     */
    public function testValidatePhpVersionInComposerJson()
    {
        $command = $this->getCommand();

        self::assertTrue(
            $command->validatePhpVersionComposerJson(
                [
                    'require' => [
                        'php' => '~5.3'
                    ]
                ]
            ),
            $this->getOutputFromCommand($command)
        );

        self::assertTrue(
            $command->validatePhpVersionComposerJson(
                [
                    'require' => [
                        'php' => '>=5.3,<1.8'
                    ]
                ]
            ),
            $this->getOutputFromCommand($command)
        );

        self::assertTrue(
            $command->validatePhpVersionComposerJson(
                [
                    'require' => [
                        'symfony/console' => '~2.3'
                    ]
                ]
            ),
            $this->getOutputFromCommand($command)
        );

        self::assertFalse(
            $command->validatePhpVersionComposerJson(
                [
                    'require' => [
                        'php' => 'invalid-constraint'
                    ]
                ]
            ),
            $this->getOutputFromCommand($command)
        );
    }

    /**
     * Test CheckTravisConfiguration::validatePhpVersionTravisYml.
     *
     * @return void
     */
    public function testValidatePhpVersionTravisYml()
    {
        $command = $this->getCommand();

        self::assertTrue(
            $command->validatePhpVersionTravisYml(
                [
                    'php' => ['5.3']
                ]
            ),
            $this->getOutputFromCommand($command)
        );

        self::assertTrue(
            $command->validatePhpVersionTravisYml(
                [
                    'php' => [
                        '5.3',
                        '5.4',
                        '5.5',
                        '5.6'
                    ]
                ]
            ),
            $this->getOutputFromCommand($command)
        );

        self::assertTrue(
            $command->validatePhpVersionTravisYml([])
        );

        self::assertFalse(
            $command->validatePhpVersionTravisYml(
                [
                    'php' => ['invalid-constraint']
                ]
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
        $versions = [
            [
                'composer' => '>=5.3',
                'travis'   => ['5.3'],
                'expect'   => true
            ],
            [
                'composer' => '>=5.3',
                'travis'   => [
                    '5.3'
                ],
                'expect'   => true
            ],
            [
                'composer' => '>=5.4',
                'travis'   => [
                    '5.3',
                    '5.4',
                    '5.5',
                    '5.6'
                ],
                'expect'   => false
            ],
            [
                'composer' => '>=5.4',
                'travis'   => [
                    '5.5',
                    '5.6'
                ],
                'expect'   => true
            ],
            [
                'composer' => '>=5.4.3',
                'travis'   => [
                    '5.4',
                    '5.5',
                    '5.6'
                ],
                'expect'   => true
            ],
            [
                'composer' => '>=5.4.3',
                'travis'   => [
                    '5.3',
                    '5.4',
                    '5.5',
                    '5.6'
                ],
                'expect'   => false
            ],
        ];

        return \array_map(function ($arr) {
            return [
                [
                    'require' => [
                        'php' => $arr['composer']
                    ]
                ],
                [
                    'php' => $arr['travis'],
                ],
                $arr['expect']
            ];
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
     */
    public function testValidatePhpVersionAgainstTravis($composer, $travis, $expected)
    {
        $command = $this->getCommand();

        self::assertEquals(
            $expected,
            $command->validatePhpVersionAgainstTravis($composer, $travis),
            'composer.json: ' . $composer['require']['php'] . ' .travis.yml: ' . \implode(',', $travis['php']) .
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
        $versions = [
            [
                'composer'  => '>=5.3',
                'travis'    => ['5.3'],
                'supported' => [
                ],
                'expect'    => false
            ],
            [
                'composer'  => '>=5.3',
                'travis'    => [
                    '5.3',
                    '5.4'
                ],
                'supported' => [],
                'expect'    => false
            ],
            [
                'composer'  => '>=5.2',
                'travis'    => [
                    '5.2',
                    '5.3',
                    '5.4'
                ],
                'supported' => [
                    '5.3',
                    '5.4'
                ],
                'expect'    => false
            ],
            [
                'composer'  => '>=5.3',
                'travis'    => [
                    '5.4'
                ],
                'supported' => [
                    '5.3',
                    '5.4'
                ],
                'expect'    => false
            ],
            [
                'composer'  => '>=5.3',
                'travis'    => [
                    '5.3',
                    '5.4'
                ],
                'supported' => [
                    '5.3',
                    '5.4'
                ],
                'expect'    => true
            ],
            [
                'composer'  => '>=5.4,<5.6',
                'travis'    => [
                    '5.4',
                    '5.5',
                ],
                'supported' => [
                    '5.3',
                    '5.4',
                    '5.5',
                    '5.6',
                ],
                'expect'    => true
            ],
        ];

        return \array_map(function ($arr) {
            return [
                [
                    'require' => [
                        'php' => $arr['composer']
                    ]
                ],
                [
                    'php' => $arr['travis'],
                ],
                $arr['supported'],
                $arr['expect']
            ];
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
     */
    public function testValidateTravisContainsAllSupportedPhpVersions($composer, $travis, $supportedVersions, $expected)
    {
        $command = $this->getCommand();

        self::assertEquals(
            $expected,
            $command->validateTravisContainsAllSupportedPhpVersions($composer, $travis, $supportedVersions),
            \sprintf(
                'travis-ci knows: "%s" .travis.yml: "%s" should %s - command output: "%s"',
                \implode(',', $supportedVersions),
                \implode(',', $travis['php']),
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
        $versions = [
            [
                'composer'  => '>=5.3',
                'travis'    => [
                    '5.3'
                ],
                'supported' => [
                ],
                'expect'    => false
            ],
            [
                'composer'  => '>=5.3',
                'travis'    => [
                    '5.3',
                    '5.4'
                ],
                'supported' => [
                ],
                'expect'    => false
            ],
            [
                'composer'  => '>=5.2',
                'travis'    => [
                    '5.2',
                    '5.3',
                    '5.4'
                ],
                'supported' => [
                    '5.3',
                    '5.4'
                ],
                'expect'    => false
            ],
            [
                'composer'  => '>=5.3',
                'travis'    => [
                    '5.4'
                ],
                'supported' => [
                    '5.3',
                    '5.4'
                ],
                'expect'    => true
            ],
            [
                'composer'  => '>=5.3',
                'travis'    => [
                    '5.3',
                    '5.4'
                ],
                'supported' => [
                    '5.3',
                    '5.4'
                ],
                'expect'    => true
            ],
            [
                'composer'  => '>=5.4,<5.6',
                'travis'    => [
                    '5.4',
                    '5.5',
                ],
                'supported' => [
                    '5.3',
                    '5.4',
                    '5.5',
                    '5.6',
                ],
                'expect'    => true
            ],
            [
                'composer'  => null,
                'travis'    => [
                    '5.4',
                    '5.5',
                ],
                'supported' => [
                    '5.3',
                    '5.4',
                    '5.5',
                    '5.6',
                ],
                'expect'    => true
            ],
            [
                'composer'  => null,
                'travis'    => null,
                'supported' => [
                    '5.3',
                    '5.4',
                    '5.5',
                    '5.6',
                ],
                'expect'    => true
            ],
        ];

        return \array_map(function ($arr) {
            return [
                [
                    'require' => $arr['composer'] ? [
                        'php' => $arr['composer']
                    ] : []
                ],
                $arr['travis'] ? [
                    'php' => $arr['travis'],
                ] : [],
                $arr['supported'],
                $arr['expect']
            ];
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
     */
    public function testValidateNoUnmaintainedPhpVersions($composer, $travis, $supportedVersions, $expected)
    {
        $command = $this->getCommand();

        self::assertEquals(
            $expected,
            $command->validateNoUnmaintainedPhpVersions($composer, $travis, $supportedVersions),
            \sprintf(
                'maintained PHP versions: "%s" .travis.yml specifies: "%s" composer.json specifies: "%s" ' .
                ' should %s - command output: "%s"',
                \implode(',', $supportedVersions),
                \implode(',', isset($travis['php']) ? $travis['php'] : []),
                isset($composer['require']['php']) ? $composer['require']['php'] : '',
                ($expected ? 'validate. ' : 'not validate. '),
                $this->getOutputFromCommand($command)
            )
        );
    }
}
