<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Form\DataProvider;

use Generated\Shared\Transfer\ProductOfferCriteriaTransfer;
use Generated\Shared\Transfer\ProductOfferTransfer;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade\ProductOfferMerchantPortalGuiToMerchantStockFacadeInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade\ProductOfferMerchantPortalGuiToMerchantUserFacadeInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade\ProductOfferMerchantPortalGuiToProductFacadeInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade\ProductOfferMerchantPortalGuiToProductOfferFacadeInterface;

class ProductOfferUpdateFormDataProvider extends AbstractProductOfferFormDataProvider implements ProductOfferUpdateFormDataProviderInterface
{
    protected ProductOfferMerchantPortalGuiToProductOfferFacadeInterface $productOfferFacade;

    public function __construct(
        ProductOfferMerchantPortalGuiToProductFacadeInterface $productFacade,
        ProductOfferMerchantPortalGuiToProductOfferFacadeInterface $productOfferFacade,
        ProductOfferMerchantPortalGuiToMerchantStockFacadeInterface $merchantStockFacade,
        ProductOfferMerchantPortalGuiToMerchantUserFacadeInterface $merchantUserFacade
    ) {
        parent::__construct($productFacade, $merchantUserFacade, $merchantStockFacade);
        $this->productOfferFacade = $productOfferFacade;
    }

    public function getData(int $idProductOffer): ?ProductOfferTransfer
    {
        $currentMerchantReference = $this->merchantUserFacade
            ->getCurrentMerchantUser()
            ->getMerchantOrFail()
            ->getMerchantReferenceOrFail();
        $productOfferTransfer = $this->productOfferFacade->findOne(
            (new ProductOfferCriteriaTransfer())
                ->setIdProductOffer($idProductOffer)
                ->addMerchantReference($currentMerchantReference),
        );

        if (!$productOfferTransfer) {
            return null;
        }

        /** @var string $sku */
        $sku = $productOfferTransfer->requireConcreteSku()->getConcreteSku();
        /** @var int $idProductConcrete */
        $idProductConcrete = $this->productFacade->findProductConcreteIdBySku($sku);

        $productOfferTransfer->setIdProductConcrete($idProductConcrete);
        $productOfferTransfer = $this->setDefaultMerchantStock($productOfferTransfer);

        return $productOfferTransfer;
    }
}
