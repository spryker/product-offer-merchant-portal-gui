<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferMerchantPortalGui\Communication\GuiTable\ConfigurationProvider;

use Generated\Shared\Transfer\GuiTableConfigurationTransfer;

interface PriceProductOfferCreateGuiTableConfigurationProviderInterface
{
    /**
     * @param array<mixed> $initialData
     */
    public function getConfiguration(array $initialData = []): GuiTableConfigurationTransfer;
}
