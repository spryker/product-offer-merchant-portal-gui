<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade;

use Generated\Shared\Transfer\CurrencyTransfer;

interface ProductOfferMerchantPortalGuiToCurrencyFacadeInterface
{
    /**
     * @return \Generated\Shared\Transfer\StoreWithCurrencyTransfer[]
     */
    public function getAllStoresWithCurrencies();

    /**
     * @param string $isoCode
     *
     * @return \Generated\Shared\Transfer\CurrencyTransfer|null
     */
    public function findCurrencyByIsoCode(string $isoCode): ?CurrencyTransfer;
}
