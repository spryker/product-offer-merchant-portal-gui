<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferMerchantPortalGui\Communication\GuiTable\Column;

use Generated\Shared\Transfer\MoneyValueTransfer;
use Generated\Shared\Transfer\PriceProductOfferTableViewTransfer;
use Generated\Shared\Transfer\PriceProductTransfer;
use Laminas\Filter\Word\CamelCaseToUnderscore;

class ColumnIdCreator implements ColumnIdCreatorInterface
{
    public function createStoreColumnId(): string
    {
        return PriceProductOfferTableViewTransfer::STORE;
    }

    public function createCurrencyColumnId(): string
    {
        return PriceProductOfferTableViewTransfer::CURRENCY;
    }

    public function createGrossAmountColumnId(string $priceTypeName): string
    {
        return $this->createPriceKey(
            $priceTypeName,
            MoneyValueTransfer::GROSS_AMOUNT,
        );
    }

    public function createNetAmountColumnId(string $priceTypeName): string
    {
        return $this->createPriceKey(
            $priceTypeName,
            MoneyValueTransfer::NET_AMOUNT,
        );
    }

    public function createVolumeQuantityColumnId(): string
    {
        /** @var string $idVolumeQuantity */
        $idVolumeQuantity = (new CamelCaseToUnderscore())
            ->filter(PriceProductOfferTableViewTransfer::VOLUME_QUANTITY);

        return strtolower($idVolumeQuantity);
    }

    public function createPriceKey(string $priceTypeName, string $moneyValueType): string
    {
        return sprintf(
            '%s[%s][%s]',
            mb_strtolower($priceTypeName),
            PriceProductTransfer::MONEY_VALUE,
            $moneyValueType,
        );
    }
}
