<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Service;

interface ProductOfferMerchantPortalGuiToUtilEncodingServiceInterface
{
    /**
     * @param array<mixed> $value
     */
    public function encodeJson(array $value, ?int $options = null, ?int $depth = null): ?string;

    /**
     * @param bool $assoc Deprecated: `false` is deprecated, always use `true` for array return.
     *
     * @return array<mixed>|null
     */
    public function decodeJson(string $jsonValue, bool $assoc = false, ?int $depth = null, ?int $options = null): ?array;
}
