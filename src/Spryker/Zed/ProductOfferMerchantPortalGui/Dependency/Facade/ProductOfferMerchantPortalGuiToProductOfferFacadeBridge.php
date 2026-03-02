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

class ProductOfferMerchantPortalGuiToProductOfferFacadeBridge implements ProductOfferMerchantPortalGuiToProductOfferFacadeInterface
{
    /**
     * @var \Spryker\Zed\ProductOffer\Business\ProductOfferFacadeInterface
     */
    protected $productOfferFacade;

    /**
     * @param \Spryker\Zed\ProductOffer\Business\ProductOfferFacadeInterface $productOfferFacade
     */
    public function __construct($productOfferFacade)
    {
        $this->productOfferFacade = $productOfferFacade;
    }

    public function get(ProductOfferCriteriaTransfer $productOfferCriteria): ProductOfferCollectionTransfer
    {
        return $this->productOfferFacade->get($productOfferCriteria);
    }

    public function findOne(ProductOfferCriteriaTransfer $productOfferCriteria): ?ProductOfferTransfer
    {
        return $this->productOfferFacade->findOne($productOfferCriteria);
    }

    public function create(ProductOfferTransfer $productOfferTransfer): ProductOfferTransfer
    {
        return $this->productOfferFacade->create($productOfferTransfer);
    }

    public function update(ProductOfferTransfer $productOfferTransfer): ProductOfferResponseTransfer
    {
        return $this->productOfferFacade->update($productOfferTransfer);
    }
}
