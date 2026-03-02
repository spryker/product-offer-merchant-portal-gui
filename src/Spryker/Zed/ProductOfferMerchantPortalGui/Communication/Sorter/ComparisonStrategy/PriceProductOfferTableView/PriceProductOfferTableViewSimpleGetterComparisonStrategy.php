<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Sorter\ComparisonStrategy\PriceProductOfferTableView;

use Generated\Shared\Transfer\PriceProductOfferTableViewTransfer;
use Laminas\Filter\Word\UnderscoreToCamelCase;

class PriceProductOfferTableViewSimpleGetterComparisonStrategy implements PriceProductOfferTableViewComparisonStrategyInterface
{
    public function isApplicable(string $fieldName): bool
    {
        return true;
    }

    public function getValueExtractorFunction(string $fieldName): callable
    {
        $methodName = $this->getMethodName($fieldName);

        return function (PriceProductOfferTableViewTransfer $priceProductOfferTableViewTransfer) use ($methodName) {
            if (!method_exists($priceProductOfferTableViewTransfer, $methodName)) {
                return null;
            }

            return $priceProductOfferTableViewTransfer->{$methodName}();
        };
    }

    protected function getMethodName(string $fieldName): string
    {
        $underscoreToCamelCaseFilter = new UnderscoreToCamelCase();
        /** @var string $sortFieldCamelCase */
        $sortFieldCamelCase = $underscoreToCamelCaseFilter->filter($fieldName);

        return sprintf('get%s', $sortFieldCamelCase);
    }
}
