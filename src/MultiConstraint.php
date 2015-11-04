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
 * Defines a conjunctive or disjunctive set of constraints on the target of a package link.
 *
 * @author Nils Adermann <naderman@naderman.de>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class MultiConstraint implements LinkConstraintInterface
{
    /**
     * The list of constraints.
     *
     * @var LinkConstraintInterface[]
     */
    protected $constraints;

    /**
     * The pretty string representation.
     *
     * @var string
     */
    protected $prettyString;

    /**
     * Flag if the contained constraints are conjunctive or disjunctive.
     *
     * @var bool
     */
    protected $conjunctive;

    /**
     * Sets operator and version to compare a package with.
     *
     * @param LinkConstraintInterface[] $constraints A set of constraints.
     *
     * @param bool                      $conjunctive Whether the constraints should be treated as conjunctive or
     *                                               disjunctive.
     */
    public function __construct($constraints, $conjunctive = true)
    {
        $this->constraints = $constraints;
        $this->conjunctive = $conjunctive;
    }

    /**
     * {@inheritdoc}
     */
    public function matches(LinkConstraintInterface $provider)
    {
        if (false === $this->conjunctive) {
            foreach ($this->constraints as $constraint) {
                if ($constraint->matches($provider)) {
                    return true;
                }
            }

            return false;
        }

        foreach ($this->constraints as $constraint) {
            if (!$constraint->matches($provider)) {
                return false;
            }
        }

        return true;
    }

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

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $constraints = array();
        foreach ($this->constraints as $constraint) {
            $constraints[] = $constraint->__toString();
        }

        return '['.implode($this->conjunctive ? ', ' : ' | ', $constraints).']';
    }
}
