<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferMerchantPortalGui;

use Spryker\Zed\Kernel\AbstractBundleConfig;

class ProductOfferMerchantPortalGuiConfig extends AbstractBundleConfig
{
    /**
     * @api
     */
    public function getDashboardExpiringOffersDaysThreshold(): int
    {
        return 5;
    }

    /**
     * @api
     */
    public function getDashboardLowStockThreshold(): int
    {
        return 5;
    }
}
