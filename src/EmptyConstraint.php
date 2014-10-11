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
 * Defines an absence of constraints.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class EmptyConstraint implements LinkConstraintInterface
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
        return '[]';
    }
}
