<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferMerchantPortalGui\Communication\DataProvider;

use ArrayObject;

interface PriceProductOfferDataProviderInterface
{
    /**
     * @param int $idProductOffer
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\PriceProductTransfer>
     */
    public function getPriceProductTransfers(int $idProductOffer): ArrayObject;
}
