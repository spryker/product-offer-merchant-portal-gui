<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Deleter;

use Generated\Shared\Transfer\PriceProductOfferCollectionTransfer;
use Generated\Shared\Transfer\ValidationResponseTransfer;

interface PriceDeleterInterface
{
    public function deletePriceByQuantity(
        PriceProductOfferCollectionTransfer $priceProductOfferCollectionTransfer,
        int $quantity
    ): ValidationResponseTransfer;
}
