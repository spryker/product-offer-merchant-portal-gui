<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Validator\PriceProductOffer\PropertyPath;

interface PriceProductOfferPropertyPathAnalyzerInterface
{
    public function transformPropertyPathToColumnId(string $propertyPath): string;

    public function isRowViolation(string $propertyPath): bool;

    public function isVolumePriceViolation(string $propertyPath): bool;

    public function isPriceRowError(string $propertyPath): bool;

    public function isVolumePriceRowError(string $propertyPath): bool;

    public function getPriceProductOfferIndex(string $propertyPath): int;

    public function getPriceProductIndex(string $propertyPath): int;

    public function getVolumePriceIndex(string $propertyPath): int;
}
