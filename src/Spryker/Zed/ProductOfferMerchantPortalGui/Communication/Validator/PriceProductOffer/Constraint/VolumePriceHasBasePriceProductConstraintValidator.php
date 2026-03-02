<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Validator\PriceProductOffer\Constraint;

use Generated\Shared\Transfer\MoneyValueTransfer;
use Generated\Shared\Transfer\PriceProductOfferCollectionTransfer;
use Generated\Shared\Transfer\PriceProductOfferTransfer;
use Generated\Shared\Transfer\PriceProductTransfer;
use Generated\Shared\Transfer\ProductOfferTransfer;
use Spryker\Zed\Kernel\Communication\Validator\AbstractConstraintValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @method \Spryker\Zed\ProductOfferMerchantPortalGui\Communication\ProductOfferMerchantPortalGuiCommunicationFactory getFactory()
 */
class VolumePriceHasBasePriceProductConstraintValidator extends AbstractConstraintValidator
{
    /**
     * @uses \Spryker\Shared\PriceProductOfferVolume\PriceProductOfferVolumeConfig::VOLUME_PRICE_TYPE
     *
     * @var string
     */
    protected const VOLUME_PRICE_TYPE = 'volume_prices';

    /**
     * @param \Generated\Shared\Transfer\PriceProductOfferCollectionTransfer|mixed $value
     * @param \Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Validator\PriceProductOffer\Constraint\VolumePriceHasBasePriceProductConstraint $constraint
     *
     * @throws \Symfony\Component\Validator\Exception\UnexpectedTypeException
     *
     * @return void
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof PriceProductOfferCollectionTransfer) {
            throw new UnexpectedTypeException($value, PriceProductOfferCollectionTransfer::class);
        }

        // @phpstan-ignore instanceof.alwaysTrue (defensive programming)
        if (!$constraint instanceof VolumePriceHasBasePriceProductConstraint) {
            throw new UnexpectedTypeException($constraint, VolumePriceHasBasePriceProductConstraint::class);
        }

        foreach ($value->getPriceProductOffers() as $priceProductOfferIndex => $priceProductOfferTransfer) {
            $this->validatePriceProductOffer(
                $priceProductOfferTransfer,
                $constraint,
                $priceProductOfferIndex,
            );
        }
    }

    protected function validatePriceProductOffer(
        PriceProductOfferTransfer $priceProductOfferTransfer,
        VolumePriceHasBasePriceProductConstraint $volumePriceHasBasePriceProductConstraint,
        int $priceProductOfferIndex
    ): void {
        $priceProductTransfers = $priceProductOfferTransfer
            ->getProductOfferOrFail()
            ->getPrices();

        foreach ($priceProductTransfers as $priceProductIndex => $priceProductTransfer) {
            $this->validatePriceProduct(
                $priceProductTransfer,
                $volumePriceHasBasePriceProductConstraint,
                $priceProductOfferIndex,
                $priceProductIndex,
            );
        }
    }

    protected function validatePriceProduct(
        PriceProductTransfer $priceProductTransfer,
        VolumePriceHasBasePriceProductConstraint $volumePriceHasBasePriceProductConstraint,
        int $priceProductOfferIndex,
        int $priceProductIndex
    ): void {
        if (
            $this->isPersistedPrice($priceProductTransfer)
            || $this->isBasePrice($priceProductTransfer)
        ) {
            return;
        }

        $volumePriceProductTransfers = $this->getFactory()
            ->getPriceProductOfferVolumeFacade()
            ->extractVolumePrices([$priceProductTransfer]);

        if (!$volumePriceProductTransfers) {
            return;
        }

        foreach ($volumePriceProductTransfers as $volumePriceIndex => $volumePrice) {
            if ((int)$volumePrice->getVolumeQuantity() === 0) {
                continue;
            }

            $violationPath = $this->createViolationPath(
                $priceProductOfferIndex,
                $priceProductIndex,
                $volumePriceIndex,
            );

            $this->context
                ->buildViolation($volumePriceHasBasePriceProductConstraint->getMessage())
                ->atPath($violationPath)
                ->addViolation();
        }
    }

    protected function isPersistedPrice(PriceProductTransfer $priceProductTransfer): bool
    {
        return $priceProductTransfer->getIdPriceProduct() !== null;
    }

    protected function isBasePrice(PriceProductTransfer $priceProductTransfer): bool
    {
        return $priceProductTransfer->getVolumeQuantity() === 1;
    }

    protected function createViolationPath(
        int $priceProductOfferIndex,
        int $priceProductIndex,
        int $volumePriceIndex
    ): string {
        return sprintf(
            '[%s][%d][%s][%s][%d][%s][%s][%s][%d]',
            PriceProductOfferCollectionTransfer::PRICE_PRODUCT_OFFERS,
            $priceProductOfferIndex,
            PriceProductOfferTransfer::PRODUCT_OFFER,
            ProductOfferTransfer::PRICES,
            $priceProductIndex,
            PriceProductTransfer::MONEY_VALUE,
            MoneyValueTransfer::PRICE_DATA,
            static::VOLUME_PRICE_TYPE,
            $volumePriceIndex,
        );
    }
}
