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
namespace ContaoCommunityAlliance\BuildSystem\Tool\TravisConfigurationCheck\Command;

use ContaoCommunityAlliance\BuildSystem\Tool\TravisConfigurationCheck\TravisEnvironmentInformation;
use ContaoCommunityAlliance\BuildSystem\Tool\TravisConfigurationCheck\VersionParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Check that the information in .travis.yml match the definitions in the composer.json.
 */
class CheckTravisConfiguration extends Command
{
    /**
     * The current input interface.
     *
     * @var InputInterface
     */
    protected $input;

    /**
     * The current output interface.
     *
     * @var OutputInterface
     */
    protected $output;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ccabs:tools:check-travis-configuration')
            ->setDescription('Validation tool to ensure that the contents of a .travis.yml make sense.')
            ->addArgument(
                'project-dir',
                InputArgument::OPTIONAL,
                'The directory where the .travis.yml and composer.json are located at.',
                '.'
            )
            ->addOption(
                'unmaintained-version-error',
                null,
                InputOption::VALUE_NONE,
                'If present, unmaintained PHP versions in .travis.yml and composer.json will cause an error.'
            );
    }

    /**
     * Retrieve the composer.json file and return it as associative array.
     *
     * @return array
     */
    protected function readComposerJson()
    {
        return json_decode(file_get_contents($this->input->getArgument('project-dir') . '/composer.json'), true);
    }

    /**
     * Retrieve the .travis.yml file and return it as associative array.
     *
     * @return array
     */
    protected function readTravisYml()
    {
        return Yaml::parse(file_get_contents($this->input->getArgument('project-dir') . '/.travis.yml'));
    }

    /**
     * Ensure a proper PHP version has been set in composer.json.
     *
     * @param array $composerJson The contents of the composer.json.
     *
     * @return bool
     */
    public function validatePhpVersionComposerJson($composerJson)
    {
        if (!isset($composerJson['require']['php'])) {
            $this->output->writeln(
                '<error>No PHP version mentioned in composer.json!</error>'
            );

            return false;
        }

        $versionParser = new VersionParser();
        try {
            $versionParser->parseConstraints($composerJson['require']['php']);
        } catch (\Exception $e) {
            $this->output->writeln('<error>' . $e->getMessage() . '</error>');

            return false;
        }

        return true;
    }
    /**
     * Ensure proper PHP versions have been set in .travis.yml.
     *
     * @param array $travisYml The contents of the .travis.yml.
     *
     * @return bool
     */
    public function validatePhpVersionTravisYml($travisYml)
    {
        if (!isset($travisYml['php'])) {
            $this->output->writeln(
                '<info>No PHP version mentioned in .travis.yml!</info>'
            );

            return true;
        }

        if (!is_array($travisYml['php'])) {
            $this->output->writeln(
                '<error>PHP version mentioned in .travis.yml must be an array!</error>'
            );

            return false;
        }

        $versionParser = new VersionParser();
        foreach ($travisYml['php'] as $version) {
            try {
                $versionParser->parseConstraints($version);
            } catch (\Exception $e) {
                $this->output->writeln('<error>' . $e->getMessage() . '</error>');

                return false;
            }
        }

        return true;
    }

    /**
     * Ensure all the .travis.yml PHP versions are marked as maintained versions.
     *
     * @param array $travisYml          The contents of the .travis.yml.
     *
     * @param array $maintainedVersions The currently maintained version list.
     *
     * @return bool
     */
    public function validateNoUnmaintainedPhpVersionsInTravis($travisYml, $maintainedVersions)
    {
        if (empty($travisYml['php'])) {
            $this->output->writeln('<info>travis.yml specifies no PHP version - check skipped.</info>');

            return true;
        }

        $failedTravis = array();
        // First pass - check the .travis.yml constraints against the maintained versions.
        foreach ($travisYml['php'] as $travisVersion) {
            if (!in_array($travisVersion, $maintainedVersions)) {
                $failedTravis[] = $travisVersion;
            }
        }

        if (!empty($failedTravis)) {
            $this->output->writeln(
                sprintf(
                    '<error>travis.yml specifies PHP version "%s" but only "%s" are actively maintained.</error>',
                    implode(', ', $failedTravis),
                    implode(', ', $maintainedVersions)
                )
            );

            return false;
        }

        return true;
    }

    /**
     * Ensure all the PHP versions in composer.json are marked as maintained versions.
     *
     * @param array $composerJson       The contents of the composer.json.
     *
     * @param array $maintainedVersions The currently maintained version list.
     *
     * @return bool
     */
    public function validateNoUnmaintainedPhpVersionsInComposer($composerJson, $maintainedVersions)
    {
        if (empty($composerJson['require']['php'])) {
            $this->output->writeln('<info>composer.json specifies no PHP version - check skipped.</info>');

            return true;
        }

        $versionParser       = new VersionParser();
        $constraintsComposer = $versionParser->parseConstraints($composerJson['require']['php']);
        $versionOk           = false;
        // Second pass - check all maintainedVersions against the composer.json constraint.
        foreach ($maintainedVersions as $maintainedVersion) {
            $constraintsMaintainedVersion = $versionParser->parseConstraints($maintainedVersion . '.9999999.9999999');
            if ($constraintsComposer->matches($constraintsMaintainedVersion)) {
                $versionOk = true;

                break;
            }
        }

        if (!$versionOk) {
            $this->output->writeln(
                sprintf(
                    '<error>composer.json specifies PHP version "%s" but only "%s" are actively maintained.</error>',
                    $constraintsComposer->getPrettyString(),
                    implode(', ', $maintainedVersions)
                )
            );

            return false;
        }

        return true;
    }

    /**
     * Ensure all the .travis.yml PHP versions are covered via the constraint in composer.json.
     *
     * @param array $composerJson       The contents of the composer.json.
     *
     * @param array $travisYml          The contents of the .travis.yml.
     *
     * @param array $maintainedVersions The currently maintained version list.
     *
     * @return bool
     */
    public function validateNoUnmaintainedPhpVersions($composerJson, $travisYml, $maintainedVersions)
    {
        $versionOk = $this->validateNoUnmaintainedPhpVersionsInTravis($travisYml, $maintainedVersions);
        $versionOk = $this->validateNoUnmaintainedPhpVersionsInComposer($composerJson, $maintainedVersions)
            && $versionOk;

        return $versionOk;
    }

    /**
     * Ensure all the .travis.yml PHP versions are covered via the constraint in composer.json.
     *
     * @param array $composerJson The contents of the composer.json.
     *
     * @param array $travisYml    The contents of the .travis.yml.
     *
     * @return bool
     */
    public function validatePhpVersionAgainstTravis($composerJson, $travisYml)
    {
        if (!($this->validatePhpVersionComposerJson($composerJson) && $this->validatePhpVersionTravisYml($travisYml))) {
            return false;
        }

        $versionParser       = new VersionParser();
        $constraintsComposer = $versionParser->parseConstraints($composerJson['require']['php']);

        foreach (!empty($travisYml['php']) ? $travisYml['php'] : array() as $version) {
            // Travis only allows major.minor specification.
            $constraintsTravis = $versionParser->parseConstraints($version . '.9999999.9999999');
            if (!$constraintsComposer->matches($constraintsTravis)) {
                $this->output->writeln(
                    sprintf(
                        '<error>composer.json "%s" does not match travis.yml "%s". ' .
                        'Please remove "%s" from .travis.yml</error>',
                        $constraintsComposer->getPrettyString(),
                        $version,
                        $version
                    )
                );

                return false;
            }
        }

        return true;
    }

    /**
     * Ensure all PHP versions covered by the constraint in composer.json are noted in .travis.yml.
     *
     * @param array $composerJson         The contents of the composer.json.
     *
     * @param array $travisYml            The contents of the .travis.yml.
     *
     * @param array $supportedPhpByTravis The available versions at travis-ci.
     *
     * @return bool
     */
    public function validateTravisContainsAllSupportedPhpVersions($composerJson, $travisYml, $supportedPhpByTravis)
    {
        $travisVersions      = !empty($travisYml['php']) ? $travisYml['php'] : array();
        $unsupportedVersions = array_diff($travisVersions, $supportedPhpByTravis);
        if ($unsupportedVersions) {
            $this->output->writeln(
                sprintf(
                    '<error>travis.yml contains a php version which is unavailable at travis-ci: %s</error>',
                    implode(', ', $unsupportedVersions)
                )
            );

            return false;
        }

        $versionParser       = new VersionParser();
        $constraintsComposer = $versionParser->parseConstraints($composerJson['require']['php']);

        $missingVersions = array();
        foreach (array_diff($supportedPhpByTravis, $travisVersions) as $version) {
            // Travis only allows major.minor specification.
            $constraintsTravis = $versionParser->parseConstraints($version . '.9999999.9999999');
            if ($constraintsComposer->matches($constraintsTravis)) {
                $missingVersions[] = $version;
            }
        }

        if (!empty($missingVersions)) {
            $this->output->writeln(
                sprintf(
                    '<error>composer.json version constraint "%s" covers more versions than defined in travis.yml. ' .
                    'Please add the missing PHP versions "%s" to .travis.yml</error>',
                    $constraintsComposer->getPrettyString(),
                    implode(',', $missingVersions)
                )
            );

            return false;
        }

        return true;
    }

    /**
     * Run the command and perform all tests.
     *
     * @param InputInterface  $input  The input interface.
     *
     * @param OutputInterface $output The output interface.
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;
        $composerJson = $this->readComposerJson($input, $output);
        $travisYml    = $this->readTravisYml($input, $output);
        $exitCode     = 0;

        if ($this->input->hasOption('unmaintained-version-error')) {
            if (!$this->validateNoUnmaintainedPhpVersions(
                $composerJson,
                $travisYml,
                explode(',', TravisEnvironmentInformation::PHP_VERSIONS)
            )) {
                $exitCode = 1;
            }
        }

        if (!$this->validatePhpVersionAgainstTravis($composerJson, $travisYml)) {
            $exitCode = 1;
        }

        if (!$this->validateTravisContainsAllSupportedPhpVersions(
            $composerJson,
            $travisYml,
            explode(',', TravisEnvironmentInformation::PHP_VERSIONS)
        )) {
            $exitCode = 1;
        }

        return $exitCode;
    }
}
