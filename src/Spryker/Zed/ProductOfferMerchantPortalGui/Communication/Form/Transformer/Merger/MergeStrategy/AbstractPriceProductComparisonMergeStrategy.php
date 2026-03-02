<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Form\Transformer\Merger\MergeStrategy;

use Generated\Shared\Transfer\MoneyValueTransfer;
use Generated\Shared\Transfer\PriceProductTransfer;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Service\ProductOfferMerchantPortalGuiToPriceProductVolumeServiceInterface;

abstract class AbstractPriceProductComparisonMergeStrategy implements PriceProductMergeStrategyInterface
{
    /**
     * @var \Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Service\ProductOfferMerchantPortalGuiToPriceProductVolumeServiceInterface
     */
    protected ProductOfferMerchantPortalGuiToPriceProductVolumeServiceInterface $priceProductVolumeService;

    public function __construct(
        ProductOfferMerchantPortalGuiToPriceProductVolumeServiceInterface $priceProductVolumeService
    ) {
        $this->priceProductVolumeService = $priceProductVolumeService;
    }

    public function isSame(
        PriceProductTransfer $priceProductTransferA,
        PriceProductTransfer $priceProductTransferB
    ): bool {
        $isSameMoneyValue = $this->getIsSameMoneyValue(
            $priceProductTransferA->getMoneyValueOrFail(),
            $priceProductTransferB->getMoneyValueOrFail(),
        );
        if (!$isSameMoneyValue) {
            return false;
        }

        return $this->hasSamePriceType($priceProductTransferA, $priceProductTransferB);
    }

    protected function getIsSameMoneyValue(
        MoneyValueTransfer $moneyValueTransferA,
        MoneyValueTransfer $moneyValueTransferB
    ): bool {
        return (
            $moneyValueTransferA->getFkStoreOrFail() == $moneyValueTransferB->getFkStoreOrFail()
            && $moneyValueTransferA->getFkCurrencyOrFail() == $moneyValueTransferB->getFkCurrencyOrFail()
        );
    }

    protected function hasSamePriceType(
        PriceProductTransfer $priceProductTransferA,
        PriceProductTransfer $priceProductTransferB
    ): bool {
        $idPriceTypeA = $priceProductTransferA->getPriceTypeOrFail()->getIdPriceTypeOrFail();
        $idPriceTypeB = $priceProductTransferB->getPriceTypeOrFail()->getIdPriceTypeOrFail();

        return $idPriceTypeA === $idPriceTypeB;
    }
}
