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
