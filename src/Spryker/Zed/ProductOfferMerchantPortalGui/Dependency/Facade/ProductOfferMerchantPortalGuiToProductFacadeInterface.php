<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade;

use Generated\Shared\Transfer\ProductAbstractTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;
use Generated\Shared\Transfer\RawProductAttributesTransfer;

interface ProductOfferMerchantPortalGuiToProductFacadeInterface
{
    public function findProductConcreteById(int $idProduct): ?ProductConcreteTransfer;

    public function findProductAbstractById(int $idProductAbstract): ?ProductAbstractTransfer;

    public function findProductConcreteIdBySku(string $sku): ?int;

    /**
     * @param \Generated\Shared\Transfer\RawProductAttributesTransfer $rawProductAttributesTransfer
     *
     * @return array<string>
     */
    public function combineRawProductAttributes(RawProductAttributesTransfer $rawProductAttributesTransfer): array;
}
