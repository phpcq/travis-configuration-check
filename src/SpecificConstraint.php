<?php

/*
 * This file is copied from composer.
 *
 * @copyright 2014 Contao Community Alliance <https://c-c-a.org>
 * @author    Nils Adermann <naderman@naderman.de>
 * @author    Jordi Boggiano <j.boggiano@seld.be>
 * @package   contao-community-alliance/build-system-tool-travis-configuration-check
 * @license   MIT
 * @link      https://c-c-a.org
 */

namespace ContaoCommunityAlliance\BuildSystem\Tool\TravisConfigurationCheck;

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
