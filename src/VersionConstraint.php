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
 * Constrains a package link based on package version.
 *
 * Version numbers must be compatible with version_compare
 *
 * @author Nils Adermann <naderman@naderman.de>
 */
class VersionConstraint extends SpecificConstraint
{
    /**
     * A comparison operator.
     *
     * @var string
     */
    private $operator;

    /**
     * A version to compare to.
     *
     * @var string
     */
    private $version;

    /**
     * Sets operator and version to compare a package with.
     *
     * @param string $operator A comparison operator.
     * @param string $version  A version to compare to.
     */
    public function __construct($operator, $version)
    {
        if ('=' === $operator) {
            $operator = '==';
        }

        if ('<>' === $operator) {
            $operator = '!=';
        }

        $this->operator = $operator;
        $this->version  = $version;
    }

    /**
     * Compare version a to version b using the operator.
     *
     * @param string $a               Version a.
     *
     * @param string $b               Version b.
     *
     * @param string $operator        The operator to use.
     *
     * @param bool   $compareBranches Flag to enable check if either version is a dev version and the same.
     *
     * @return bool|mixed
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function versionCompare($a, $b, $operator, $compareBranches = false)
    {
        $aIsBranch = 'dev-' === substr($a, 0, 4);
        $bIsBranch = 'dev-' === substr($b, 0, 4);
        if ($aIsBranch && $bIsBranch) {
            return $operator == '==' && $a === $b;
        }

        // when branches are not comparable, we make sure dev branches never match anything
        if (!$compareBranches && ($aIsBranch || $bIsBranch)) {
            return false;
        }

        return version_compare($a, $b, $operator);
    }

    /**
     * Match the specific version.
     *
     * @param VersionConstraint $provider        The constraint to match against.
     *
     * @param bool              $compareBranches Flag to enable check if either version is a dev version and the same.
     *
     * @return bool
     */
    public function matchSpecific(VersionConstraint $provider, $compareBranches = false)
    {
        static $cache = array();
        if (isset($cache[$this->operator][$this->version][$provider->operator][$provider->version][$compareBranches])) {
            return $cache[$this->operator][$this->version][$provider->operator][$provider->version][$compareBranches];
        }

        return $cache[$this->operator][$this->version][$provider->operator][$provider->version][$compareBranches] =
            $this->doMatchSpecific($provider, $compareBranches);
    }

    /**
     * Match a specific version.
     *
     * @param VersionConstraint $provider        The constraint to match against.
     *
     * @param bool              $compareBranches Flag to enable check if either version is a dev version and the same.
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function doMatchSpecific(VersionConstraint $provider, $compareBranches = false)
    {
        $noEqualOp            = str_replace('=', '', $this->operator);
        $providerNoEqualOp    = str_replace('=', '', $provider->operator);
        $isEqualOp            = '==' === $this->operator;
        $isNonEqualOp         = '!=' === $this->operator;
        $isProviderEqualOp    = '==' === $provider->operator;
        $isProviderNonEqualOp = '!=' === $provider->operator;

        // '!=' operator is match when other operator is not '==' operator or version is not match
        // these kinds of comparisons always have a solution
        if ($isNonEqualOp || $isProviderNonEqualOp) {
            return !$isEqualOp && !$isProviderEqualOp
            || $this->versionCompare($provider->version, $this->version, '!=', $compareBranches);
        }

        // an example for the condition is <= 2.0 & < 1.0
        // these kinds of comparisons always have a solution
        if ($this->operator != '==' && $noEqualOp == $providerNoEqualOp) {
            return true;
        }

        if ($this->versionCompare($provider->version, $this->version, $this->operator, $compareBranches)) {
            // special case, e.g. require >= 1.0 and provide < 1.0
            // 1.0 >= 1.0 but 1.0 is outside of the provided interval
            if ($provider->version == $this->version
                && $provider->operator == $providerNoEqualOp
                && $this->operator != $noEqualOp
            ) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->operator.' '.$this->version;
    }
}
