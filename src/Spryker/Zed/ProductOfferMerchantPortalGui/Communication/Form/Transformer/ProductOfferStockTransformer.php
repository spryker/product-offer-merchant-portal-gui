<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Form\Transformer;

use ArrayObject;
use Generated\Shared\Transfer\ProductOfferStockTransfer;
use Symfony\Component\Form\DataTransformerInterface;

class ProductOfferStockTransformer implements DataTransformerInterface
{
    /**
     * @phpstan-param array<\Generated\Shared\Transfer\ProductOfferStockTransfer> $productOfferStockTransfers
     *
     * @param \Generated\Shared\Transfer\ProductOfferStockTransfer[]|\ArrayObject $productOfferStockTransfers
     *
     * @return \Generated\Shared\Transfer\ProductOfferStockTransfer
     */
    public function transform($productOfferStockTransfers): ProductOfferStockTransfer
    {
        return $productOfferStockTransfers[0];
    }

    /**
     * @phpstan-return \ArrayObject<int, \Generated\Shared\Transfer\ProductOfferStockTransfer>
     *
     * @param \Generated\Shared\Transfer\ProductOfferStockTransfer $productOfferStockTransfer
     *
     * @return \Generated\Shared\Transfer\ProductOfferStockTransfer[]|\ArrayObject
     */
    public function reverseTransform($productOfferStockTransfer): ArrayObject
    {
        return (new ArrayObject([$productOfferStockTransfer]));
    }
}
