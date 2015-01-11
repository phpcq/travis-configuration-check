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
 * Defines a constraint on a link between two packages.
 *
 * @author Nils Adermann <naderman@naderman.de>
 */
interface LinkConstraintInterface
{
    /**
     * Check that the constraint matches the passed constraint.
     *
     * @param LinkConstraintInterface $provider The constraint to match against.
     *
     * @return bool
     */
    public function matches(LinkConstraintInterface $provider);

    /**
     * Set the pretty string representation of the constraint.
     *
     * @param string $prettyString The pretty string.
     *
     * @return void
     */
    public function setPrettyString($prettyString);

    /**
     * Retrieve the pretty string representation of the constraint.
     *
     * @return string
     */
    public function getPrettyString();

    /**
     * Convert the instance to a string value.
     *
     * @return string
     */
    public function __toString();
}
