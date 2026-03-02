<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferMerchantPortalGui\Communication\GuiTable\Column;

interface ColumnIdCreatorInterface
{
    public function createStoreColumnId(): string;

    public function createCurrencyColumnId(): string;

    public function createGrossAmountColumnId(string $priceTypeName): string;

    public function createNetAmountColumnId(string $priceTypeName): string;

    public function createVolumeQuantityColumnId(): string;

    public function createPriceKey(string $priceTypeName, string $moneyValueType): string;
}
