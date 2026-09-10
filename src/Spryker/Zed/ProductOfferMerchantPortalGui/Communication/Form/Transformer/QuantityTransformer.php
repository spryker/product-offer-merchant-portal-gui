<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Form\Transformer;

use Spryker\DecimalObject\Decimal;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements \Symfony\Component\Form\DataTransformerInterface<\Spryker\DecimalObject\Decimal|null, float|null>
 */
class QuantityTransformer implements DataTransformerInterface
{
    /**
     * @param \Spryker\DecimalObject\Decimal|mixed $value
     */
    public function transform($value): ?float
    {
        if ($value === null) {
            return null;
        }

        return $value->toFloat();
    }

    /**
     * @param mixed|float|null $value
     */
    public function reverseTransform($value): ?Decimal
    {
        if ($value === null) {
            return null;
        }

        return (new Decimal($value));
    }
}
