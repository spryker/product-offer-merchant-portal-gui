<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Sorter;

use Generated\Shared\Transfer\PriceProductOfferTableCriteriaTransfer;
use Generated\Shared\Transfer\PriceProductOfferTableViewCollectionTransfer;

interface PriceProductOfferTableViewSorterInterface
{
    public function sortPriceProductOfferTableViews(
        PriceProductOfferTableViewCollectionTransfer $priceProductOfferTableViewCollectionTransfer,
        PriceProductOfferTableCriteriaTransfer $criteriaTransfer
    ): PriceProductOfferTableViewCollectionTransfer;
}
