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
