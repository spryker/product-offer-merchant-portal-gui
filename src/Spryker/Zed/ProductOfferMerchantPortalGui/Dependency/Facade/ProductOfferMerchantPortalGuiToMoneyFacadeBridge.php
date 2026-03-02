<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade;

class ProductOfferMerchantPortalGuiToMoneyFacadeBridge implements ProductOfferMerchantPortalGuiToMoneyFacadeInterface
{
    /**
     * @var \Spryker\Zed\Money\Business\MoneyFacadeInterface
     */
    protected $moneyFacade;

    /**
     * @param \Spryker\Zed\Money\Business\MoneyFacadeInterface $moneyFacade
     */
    public function __construct($moneyFacade)
    {
        $this->moneyFacade = $moneyFacade;
    }

    public function convertIntegerToDecimal(int $value): float
    {
        return $this->moneyFacade->convertIntegerToDecimal($value);
    }

    public function convertDecimalToInteger(float $value): int
    {
        return $this->moneyFacade->convertDecimalToInteger($value);
    }
}
