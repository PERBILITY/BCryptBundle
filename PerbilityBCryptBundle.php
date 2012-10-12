<?php

/*
 * This file is part of the PerbilityBCryptBundle package.
 *
 * (c) PERBILITY GmbH <http://www.perbility.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Perbility\Bundle\BCryptBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Perbility\Bundle\BCryptBundle\DependencyInjection\PerbilityBCryptExtension;

/**
 * @author Benjamin Zikarsky <benjamin.zikarsky@perbility.de>
 * @codeCoverageIgnore
 */
class PerbilityBCryptBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function getContainerExtension()
    {
        return new PerbilityBCryptExtension();
    }
}
