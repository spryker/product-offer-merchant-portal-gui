<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade;

use Generated\Shared\Transfer\ProductOfferCollectionTransfer;
use Generated\Shared\Transfer\ProductOfferCriteriaTransfer;
use Generated\Shared\Transfer\ProductOfferResponseTransfer;
use Generated\Shared\Transfer\ProductOfferTransfer;

interface ProductOfferMerchantPortalGuiToProductOfferFacadeInterface
{
    public function get(ProductOfferCriteriaTransfer $productOfferCriteria): ProductOfferCollectionTransfer;

    public function findOne(ProductOfferCriteriaTransfer $productOfferCriteria): ?ProductOfferTransfer;

    public function create(ProductOfferTransfer $productOfferTransfer): ProductOfferTransfer;

    public function update(ProductOfferTransfer $productOfferTransfer): ProductOfferResponseTransfer;
}
