<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade;

use Generated\Shared\Transfer\CurrencyTransfer;

class ProductOfferMerchantPortalGuiToCurrencyFacadeBridge implements ProductOfferMerchantPortalGuiToCurrencyFacadeInterface
{
    /**
     * @var \Spryker\Zed\Currency\Business\CurrencyFacadeInterface
     */
    protected $currencyFacade;

    /**
     * @param \Spryker\Zed\Currency\Business\CurrencyFacadeInterface $currencyFacade
     */
    public function __construct($currencyFacade)
    {
        $this->currencyFacade = $currencyFacade;
    }

    /**
     * @return array<\Generated\Shared\Transfer\StoreWithCurrencyTransfer>
     */
    public function getAllStoresWithCurrencies(): array
    {
        return $this->currencyFacade->getAllStoresWithCurrencies();
    }

    public function findCurrencyByIsoCode(string $isoCode): ?CurrencyTransfer
    {
        return $this->currencyFacade->findCurrencyByIsoCode($isoCode);
    }

    public function getByIdCurrency(int $idCurrency): CurrencyTransfer
    {
        return $this->currencyFacade->getByIdCurrency($idCurrency);
    }
}
