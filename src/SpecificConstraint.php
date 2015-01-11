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
 * Provides a common basis for specific package link constraints.
 *
 * @author Nils Adermann <naderman@naderman.de>
 */
abstract class SpecificConstraint implements LinkConstraintInterface
{
    /**
     * The pretty string representation.
     *
     * @var string
     */
    protected $prettyString;

    /**
     * {@inheritdoc}
     */
    public function matches(LinkConstraintInterface $provider)
    {
        if ($provider instanceof MultiConstraint) {
            // turn matching around to find a match
            return $provider->matches($this);
        } elseif ($provider instanceof $this) {
            return $this->matchSpecific($provider);
        }

        return true;
    }

    /*
     * implementations must implement a method of this format:
     * not declared abstract here because type hinting violates parameter coherence
     * public function matchSpecific(<SpecificConstraintType> $provider);
     */

    /**
     * {@inheritdoc}
     */
    public function setPrettyString($prettyString)
    {
        $this->prettyString = $prettyString;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrettyString()
    {
        if ($this->prettyString) {
            return $this->prettyString;
        }

        return $this->__toString();
    }
}
