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

namespace PhpCodeQuality\TravisConfigurationCheck;

/**
 * Version parser, based upon the version parser by the composer project.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class VersionParser
{
    /**
     * The regex modifier.
     *
     * @var string
     */
    private static $modifierRegex = '[._-]?(?:(stable|beta|b|RC|alpha|a|patch|pl|p)(?:[.-]?(\d+))?)?([.-]?dev)?';

    /**
     * Stable releases.
     */
    const STABILITY_STABLE = 0;

    /**
     * Release candidates.
     */
    const STABILITY_RC = 5;

    /**
     * Beta releases
     */
    const STABILITY_BETA = 10;

    /**
     * Alpha releases.
     */
    const STABILITY_ALPHA = 15;

    /**
     * Unreleased development version.
     */
    const STABILITY_DEV = 20;

    /**
     * Map string representations of releases into their numeric constant.
     *
     * @var array
     */
    public static $stabilities = array(
        'stable' => self::STABILITY_STABLE,
        'RC'     => self::STABILITY_RC,
        'beta'   => self::STABILITY_BETA,
        'alpha'  => self::STABILITY_ALPHA,
        'dev'    => self::STABILITY_DEV,
    );

    /**
     * Returns the stability of a version.
     *
     * @param string $version The version.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public static function parseStability($version)
    {
        $version = preg_replace('{#.+$}i', '', $version);

        if ('dev-' === substr($version, 0, 4) || '-dev' === substr($version, -4)) {
            return 'dev';
        }

        preg_match('{'.self::$modifierRegex.'$}i', strtolower($version), $match);
        if (!empty($match[3])) {
            return 'dev';
        }

        if (!empty($match[1])) {
            if ('beta' === $match[1] || 'b' === $match[1]) {
                return 'beta';
            }
            if ('alpha' === $match[1] || 'a' === $match[1]) {
                return 'alpha';
            }
            if ('rc' === $match[1]) {
                return 'RC';
            }
        }

        return 'stable';
    }

    /**
     * Normalizes a version string to be able to perform comparisons on it.
     *
     * @param string $version     The version to normalize.
     *
     * @param string $fullVersion Optional complete version string to give more context.
     *
     * @throws \UnexpectedValueException When the version string is invalid.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function normalize($version, $fullVersion = null)
    {
        $version = trim($version);
        if (null === $fullVersion) {
            $fullVersion = $version;
        }

        // ignore aliases and just assume the alias is required instead of the source
        if (preg_match('{^([^,\s]+) +as +([^,\s]+)$}', $version, $match)) {
            $version = $match[1];
        }

        // match master-like branches
        if (preg_match('{^(?:dev-)?(?:master|trunk|default)$}i', $version)) {
            return '9999999-dev';
        }

        if ('dev-' === strtolower(substr($version, 0, 4))) {
            return 'dev-'.substr($version, 4);
        }

        // match classical versioning
        if (preg_match('{^v?(\d{1,3})(\.\d+)?(\.\d+)?(\.\d+)?'.self::$modifierRegex.'$}i', $version, $matches)) {
            $version = $matches[1]
                .(!empty($matches[2]) ? $matches[2] : '.0')
                .(!empty($matches[3]) ? $matches[3] : '.0')
                .(!empty($matches[4]) ? $matches[4] : '.0');
            $index   = 5;
        } elseif (preg_match(
            '{^v?(\d{4}(?:[.:-]?\d{2}){1,6}(?:[.:-]?\d{1,3})?)'.self::$modifierRegex.'$}i',
            $version,
            $matches
        )) {
            // match date-based versioning
            $version = preg_replace('{\D}', '-', $matches[1]);
            $index   = 2;
        } elseif (preg_match('{^v?(\d{4,})(\.\d+)?(\.\d+)?(\.\d+)?'.self::$modifierRegex.'$}i', $version, $matches)) {
            $version = $matches[1]
                .(!empty($matches[2]) ? $matches[2] : '.0')
                .(!empty($matches[3]) ? $matches[3] : '.0')
                .(!empty($matches[4]) ? $matches[4] : '.0');
            $index   = 5;
        }

        // add version modifiers if a version was matched
        if (isset($index)) {
            if (!empty($matches[$index])) {
                if ('stable' === $matches[$index]) {
                    return $version;
                }
                $version .= '-' . $this->expandStability($matches[$index]) .
                    (
                        !empty($matches[($index + 1)])
                            ? $matches[($index + 1)]
                            : ''
                    );
            }

            if (!empty($matches[($index + 2)])) {
                $version .= '-dev';
            }

            return $version;
        }

        // match dev branches
        if (preg_match('{(.*?)[.-]?dev$}i', $version, $match)) {
            try {
                return $this->normalizeBranch($match[1]);
            } catch (\Exception $e) {
                // Will get evaluated below.
            }
        }

        $extraMessage = '';
        if (preg_match('{ +as +'.preg_quote($version).'$}', $fullVersion)) {
            $extraMessage = ' in "'.$fullVersion.'", the alias must be an exact version';
        } elseif (preg_match('{^'.preg_quote($version).' +as +}', $fullVersion)) {
            $extraMessage = ' in "'.$fullVersion.
                '", the alias source must be an exact version, if it is a branch name you should prefix it with dev-';
        }

        throw new \UnexpectedValueException('Invalid version string "'.$version.'"'.$extraMessage);
    }

    /**
     * Normalizes a branch name to be able to perform comparisons on it.
     *
     * @param string $name The branch name.
     *
     * @return string
     */
    public function normalizeBranch($name)
    {
        $name = trim($name);

        if (in_array($name, array('master', 'trunk', 'default'))) {
            return $this->normalize($name);
        }

        if (preg_match('#^v?(\d+)(\.(?:\d+|[x*]))?(\.(?:\d+|[x*]))?(\.(?:\d+|[x*]))?$#i', $name, $matches)) {
            $version = '';
            for ($i = 1; $i < 5; $i++) {
                $version .= isset($matches[$i]) ? str_replace('*', 'x', $matches[$i]) : '.x';
            }

            return str_replace('x', '9999999', $version).'-dev';
        }

        return 'dev-'.$name;
    }

    /**
     * Parses as constraint string into LinkConstraint objects.
     *
     * @param string $constraints The constraints.
     *
     * @return LinkConstraintInterface
     */
    public function parseConstraints($constraints)
    {
        $prettyConstraint = $constraints;

        if (preg_match('{^([^,\s]*?)@('.implode('|', array_keys(self::$stabilities)).')$}i', $constraints, $match)) {
            $constraints = empty($match[1]) ? '*' : $match[1];
        }

        if (preg_match('{^(dev-[^,\s@]+?|[^,\s@]+?\.x-dev)#.+$}i', $constraints, $match)) {
            $constraints = $match[1];
        }

        $orConstraints = preg_split('{\s*\|\s*}', trim($constraints));
        $orGroups      = array();
        foreach ($orConstraints as $constraints) {
            $andConstraints = preg_split('{\s*,\s*}', $constraints);

            if (count($andConstraints) > 1) {
                $constraintObjects = array();
                foreach ($andConstraints as $constraint) {
                    $constraintObjects = array_merge($constraintObjects, $this->parseConstraint($constraint));
                }
            } else {
                $constraintObjects = $this->parseConstraint($andConstraints[0]);
            }

            if (1 === count($constraintObjects)) {
                $constraint = $constraintObjects[0];
            } else {
                $constraint = new MultiConstraint($constraintObjects);
            }

            $orGroups[] = $constraint;
        }

        if (1 === count($orGroups)) {
            $constraint = $orGroups[0];
        } else {
            $constraint = new MultiConstraint($orGroups, false);
        }

        $constraint->setPrettyString($prettyConstraint);

        return $constraint;
    }

    /**
     * Parse a constraint.
     *
     * @param string $constraint The constraint to parse.
     *
     * @return array
     *
     * @throws \UnexpectedValueException When the constraint could not be parsed.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function parseConstraint($constraint)
    {
        if (preg_match('{^([^,\s]+?)@('.implode('|', array_keys(self::$stabilities)).')$}i', $constraint, $match)) {
            $constraint = $match[1];
            if ($match[2] !== 'stable') {
                $stabilityModifier = $match[2];
            }
        }

        if (preg_match('{^[x*](\.[x*])*$}i', $constraint)) {
            return array(new EmptyConstraint);
        }

        // match tilde constraints
        // like wildcard constraints, unsuffixed tilde constraints say that they must be greater than the previous
        // version, to ensure that unstable instances of the current version are allowed.
        // however, if a stability suffix is added to the constraint, then a >= match on the current version is
        // used instead
        if (preg_match(
            '{^~>?(\d+)(?:\.(\d+))?(?:\.(\d+))?(?:\.(\d+))?'.self::$modifierRegex.'?$}i',
            $constraint,
            $matches
        )) {
            if (substr($constraint, 0, 2) === '~>') {
                throw new \UnexpectedValueException(
                    'Could not parse version constraint '.$constraint.': '.
                    'Invalid operator "~>", you probably meant to use the "~" operator'
                );
            }

            // Work out which position in the version we are operating at
            if (isset($matches[4]) && '' !== $matches[4]) {
                $position = 4;
            } elseif (isset($matches[3]) && '' !== $matches[3]) {
                $position = 3;
            } elseif (isset($matches[2]) && '' !== $matches[2]) {
                $position = 2;
            } else {
                $position = 1;
            }

            // Calculate the stability suffix
            $stabilitySuffix = '';
            if (!empty($matches[5])) {
                $stabilitySuffix .= '-' . $this->expandStability($matches[5]) .
                    (!empty($matches[6]) ? $matches[6] : '');
            }

            if (!empty($matches[7])) {
                $stabilitySuffix .= '-dev';
            }

            if (!$stabilitySuffix) {
                $stabilitySuffix = '-dev';
            }
            $lowVersion = $this->manipulateVersionString($matches, $position, 0) . $stabilitySuffix;
            $lowerBound = new VersionConstraint('>=', $lowVersion);

            // For upper bound, we increment the position of one more significance,
            // but highPosition = 0 would be illegal
            $highPosition = max(1, ($position - 1));
            $highVersion  = $this->manipulateVersionString($matches, $highPosition, 1) . '-dev';
            $upperBound   = new VersionConstraint('<', $highVersion);

            return array(
                $lowerBound,
                $upperBound
            );
        }

        // Match wildcard constraints.
        if (preg_match('{^(\d+)(?:\.(\d+))?(?:\.(\d+))?\.[x*]$}', $constraint, $matches)) {
            if (isset($matches[3]) && '' !== $matches[3]) {
                $position = 3;
            } elseif (isset($matches[2]) && '' !== $matches[2]) {
                $position = 2;
            } else {
                $position = 1;
            }

            $lowVersion  = $this->manipulateVersionString($matches, $position) . '-dev';
            $highVersion = $this->manipulateVersionString($matches, $position, 1) . '-dev';

            if ($lowVersion === '0.0.0.0-dev') {
                return array(new VersionConstraint('<', $highVersion));
            }

            return array(
                new VersionConstraint('>=', $lowVersion),
                new VersionConstraint('<', $highVersion),
            );
        }

        // match operators constraints
        if (preg_match('{^(<>|!=|>=?|<=?|==?)?\s*(.*)}', $constraint, $matches)) {
            try {
                $version = $this->normalize($matches[2]);

                if (!empty($stabilityModifier) && $this->parseStability($version) === 'stable') {
                    $version .= '-' . $stabilityModifier;
                } elseif ('<' === $matches[1]) {
                    if (!preg_match('/-stable$/', strtolower($matches[2]))) {
                        $version .= '-dev';
                    }
                }

                return array(new VersionConstraint($matches[1] ?: '=', $version));
            } catch (\Exception $e) {
                // Exception will get evaluated below.
            }
        }

        $message = 'Could not parse version constraint '.$constraint;
        if (isset($e)) {
            $message .= ': '. $e->getMessage();
        }

        throw new \UnexpectedValueException($message);
    }

    /**
     * Increment, decrement, or simply pad a version number.
     *
     * Support function for {@link parseConstraint()}.
     *
     * @param array  $matches   Array with version parts in array indexes 1,2,3,4.
     *
     * @param int    $position  One of 1,2,3,4 - which segment of the version to decrement.
     *
     * @param int    $increment How much the segment shall be incremented, defaults to 0.
     *
     * @param string $pad       The string to pad version parts after $position.
     *
     * @return string The new version.
     */
    private function manipulateVersionString($matches, $position, $increment = 0, $pad = '0')
    {
        for ($i = 4; $i > 0; $i--) {
            if ($i > $position) {
                $matches[$i] = $pad;
            } elseif ($i == $position && $increment) {
                $matches[$i] += $increment;
                // If $matches[$i] was 0, we carry the decrement.
                if ($matches[$i] < 0) {
                    $matches[$i] = $pad;
                    $position--;

                    // Return null on a carry overflow.
                    if ($i == 1) {
                        return null;
                    }
                }
            }
        }

        return $matches[1] . '.' . $matches[2] . '.' . $matches[3] . '.' . $matches[4];
    }

    /**
     * Expand the given stability into a string representation understood by this class..
     *
     * @param string $stability The stability.
     *
     * @return string
     */
    private function expandStability($stability)
    {
        $stability = strtolower($stability);

        switch ($stability) {
            case 'a':
                return 'alpha';
            case 'b':
                return 'beta';
            case 'p':
            case 'pl':
                return 'patch';
            case 'rc':
                return 'RC';
            default:
                return $stability;
        }
    }
}
