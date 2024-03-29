<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade;

use ArrayObject;
use Generated\Shared\Transfer\PriceProductOfferCollectionTransfer;
use Generated\Shared\Transfer\PriceProductOfferCriteriaTransfer;
use Generated\Shared\Transfer\ProductOfferTransfer;
use Generated\Shared\Transfer\ValidationResponseTransfer;

class ProductOfferMerchantPortalGuiToPriceProductOfferFacadeBridge implements ProductOfferMerchantPortalGuiToPriceProductOfferFacadeInterface
{
    /**
     * @var \Spryker\Zed\PriceProductOffer\Business\PriceProductOfferFacadeInterface
     */
    protected $priceProductOfferFacade;

    /**
     * @param \Spryker\Zed\PriceProductOffer\Business\PriceProductOfferFacadeInterface $priceProductOfferFacade
     */
    public function __construct($priceProductOfferFacade)
    {
        $this->priceProductOfferFacade = $priceProductOfferFacade;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductOfferTransfer $productOfferTransfer
     *
     * @return \Generated\Shared\Transfer\ProductOfferTransfer
     */
    public function saveProductOfferPrices(ProductOfferTransfer $productOfferTransfer): ProductOfferTransfer
    {
        return $this->priceProductOfferFacade->saveProductOfferPrices($productOfferTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductOfferCollectionTransfer $priceProductOfferCollectionTransfers
     *
     * @return \Generated\Shared\Transfer\ValidationResponseTransfer
     */
    public function validateProductOfferPrices(PriceProductOfferCollectionTransfer $priceProductOfferCollectionTransfers): ValidationResponseTransfer
    {
        return $this->priceProductOfferFacade->validateProductOfferPrices($priceProductOfferCollectionTransfers);
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductOfferCollectionTransfer $priceProductOfferCollectionTransfer
     *
     * @return void
     */
    public function deleteProductOfferPrices(PriceProductOfferCollectionTransfer $priceProductOfferCollectionTransfer): void
    {
        $this->priceProductOfferFacade->deleteProductOfferPrices($priceProductOfferCollectionTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductOfferCriteriaTransfer $priceProductOfferCriteriaTransfer
     *
     * @return int
     */
    public function count(PriceProductOfferCriteriaTransfer $priceProductOfferCriteriaTransfer): int
    {
        return $this->priceProductOfferFacade->count($priceProductOfferCriteriaTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductOfferCriteriaTransfer $priceProductOfferCriteriaTransfer
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\PriceProductTransfer>
     */
    public function getProductOfferPrices(
        PriceProductOfferCriteriaTransfer $priceProductOfferCriteriaTransfer
    ): ArrayObject {
        return $this->priceProductOfferFacade->getProductOfferPrices($priceProductOfferCriteriaTransfer);
    }
}
