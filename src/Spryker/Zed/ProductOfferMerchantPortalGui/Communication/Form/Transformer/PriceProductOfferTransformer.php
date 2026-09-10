<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Form\Transformer;

use ArrayObject;
use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\MoneyValueTransfer;
use Generated\Shared\Transfer\PriceProductDimensionTransfer;
use Generated\Shared\Transfer\PriceProductOfferTableViewTransfer;
use Generated\Shared\Transfer\PriceProductTransfer;
use Generated\Shared\Transfer\PriceTypeTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\DataProvider\PriceProductOfferDataProviderInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Form\Transformer\Merger\PriceProductsMergerInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\GuiTable\Column\ColumnIdCreatorInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade\ProductOfferMerchantPortalGuiToCurrencyFacadeInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade\ProductOfferMerchantPortalGuiToMoneyFacadeInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade\ProductOfferMerchantPortalGuiToPriceProductFacadeInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Service\ProductOfferMerchantPortalGuiToUtilEncodingServiceInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements \Symfony\Component\Form\DataTransformerInterface<\ArrayObject<int, \Generated\Shared\Transfer\PriceProductTransfer>, string>
 */
class PriceProductOfferTransformer implements DataTransformerInterface
{
    /**
     * @uses \Spryker\Shared\PriceProduct\PriceProductConfig::PRICE_TYPE_DEFAULT
     *
     * @var string
     */
    protected const PRICE_TYPE_DEFAULT = 'DEFAULT';

    protected ?int $idProductOffer;

    protected ProductOfferMerchantPortalGuiToUtilEncodingServiceInterface $utilEncodingService;

    protected ProductOfferMerchantPortalGuiToPriceProductFacadeInterface $priceProductFacade;

    protected ProductOfferMerchantPortalGuiToCurrencyFacadeInterface $currencyFacade;

    protected ProductOfferMerchantPortalGuiToMoneyFacadeInterface $moneyFacade;

    protected PriceProductsMergerInterface $priceProductsMerger;

    protected ColumnIdCreatorInterface $columnIdCreator;

    protected PriceProductOfferDataProviderInterface $priceProductOfferDataProvider;

    public function __construct(
        ProductOfferMerchantPortalGuiToUtilEncodingServiceInterface $utilEncodingService,
        ProductOfferMerchantPortalGuiToPriceProductFacadeInterface $priceProductFacade,
        ProductOfferMerchantPortalGuiToCurrencyFacadeInterface $currencyFacade,
        ProductOfferMerchantPortalGuiToMoneyFacadeInterface $moneyFacade,
        PriceProductsMergerInterface $priceProductsMerger,
        ColumnIdCreatorInterface $columnIdCreator,
        PriceProductOfferDataProviderInterface $priceProductOfferDataProvider,
        ?int $idProductOffer = null
    ) {
        $this->utilEncodingService = $utilEncodingService;
        $this->priceProductFacade = $priceProductFacade;
        $this->currencyFacade = $currencyFacade;
        $this->moneyFacade = $moneyFacade;
        $this->idProductOffer = $idProductOffer;
        $this->priceProductsMerger = $priceProductsMerger;
        $this->columnIdCreator = $columnIdCreator;
        $this->priceProductOfferDataProvider = $priceProductOfferDataProvider;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function transform($value)
    {
        $prices = [];
        $priceTypes = $this->priceProductFacade->getPriceTypeValues();

        /** @var \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer */
        foreach ($value as $priceProductTransfer) {
            if ($priceProductTransfer->getIdPriceProduct()) {
                continue;
            }

            $key = sprintf(
                '%d_%d',
                $priceProductTransfer->getMoneyValueOrFail()->getFkStoreOrFail(),
                $priceProductTransfer->getMoneyValueOrFail()->getFkCurrencyOrFail(),
            );

            $prices[$key][PriceProductOfferTableViewTransfer::STORE] = $priceProductTransfer->getMoneyValueOrFail()->getFkStoreOrFail();
            $prices[$key][PriceProductOfferTableViewTransfer::CURRENCY] = $priceProductTransfer->getMoneyValueOrFail()->getFkCurrencyOrFail();

            $prices = $this->addPrices($priceProductTransfer, $prices[$key], $priceTypes);
        }

        return $this->utilEncodingService->encodeJson($prices);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function reverseTransform($value)
    {
        $priceProductTransfers = $this->getPersistedPriceProductTransfers();

        $newPriceProductOffers = $this->utilEncodingService->decodeJson($value, true);
        if (!$newPriceProductOffers) {
            return $priceProductTransfers;
        }

        $priceProductDimensionTransfer = (new PriceProductDimensionTransfer())
            ->setIdProductOffer($this->idProductOffer);
        $priceTypes = $this->priceProductFacade->getPriceTypeValues();

        foreach ($newPriceProductOffers as $newPriceProductOffer) {
            $priceProductTransfers = $this->addPriceProductTransfers(
                $newPriceProductOffer,
                $priceProductDimensionTransfer,
                $priceProductTransfers,
                $priceTypes,
            );
        }

        return $priceProductTransfers;
    }

    /**
     * @return \ArrayObject<int, \Generated\Shared\Transfer\PriceProductTransfer>
     */
    protected function getPersistedPriceProductTransfers(): ArrayObject
    {
        if ($this->idProductOffer === null) {
            return new ArrayObject();
        }

        return $this->priceProductOfferDataProvider->getPriceProductTransfers($this->idProductOffer);
    }

    /**
     * @param array<mixed> $newPriceProductOffer
     * @param \ArrayObject<int, \Generated\Shared\Transfer\PriceProductTransfer> $priceProductTransfers
     * @param array<\Generated\Shared\Transfer\PriceTypeTransfer> $priceTypes
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\PriceProductTransfer>
     */
    protected function addPriceProductTransfers(
        array $newPriceProductOffer,
        PriceProductDimensionTransfer $priceProductDimensionTransfer,
        ArrayObject $priceProductTransfers,
        array $priceTypes
    ): ArrayObject {
        $idCurrency = $newPriceProductOffer[PriceProductOfferTableViewTransfer::CURRENCY];
        $currencyTransfer = $idCurrency ?
            $this->currencyFacade->getByIdCurrency($idCurrency)
            : null;

        foreach ($priceTypes as $priceTypeTransfer) {
            $newPriceProductTransfer = $this->createPriceProductTransfer(
                $newPriceProductOffer,
                $priceProductDimensionTransfer,
                $currencyTransfer,
                $priceTypeTransfer,
            );

            if ($newPriceProductTransfer === null) {
                continue;
            }

            $priceProductTransfers = $this->priceProductsMerger
                ->merge($priceProductTransfers, $newPriceProductTransfer);
        }

        return $priceProductTransfers;
    }

    /**
     * @param array<mixed> $newPriceProductOfferData
     */
    protected function createPriceProductTransfer(
        array $newPriceProductOfferData,
        PriceProductDimensionTransfer $priceProductDimensionTransfer,
        ?CurrencyTransfer $currencyTransfer,
        PriceTypeTransfer $priceTypeTransfer
    ): ?PriceProductTransfer {
        $storeTransfer = (new StoreTransfer())
            ->setIdStore($newPriceProductOfferData[$this->columnIdCreator->createStoreColumnId()]);

        $moneyValueTransfer = (new MoneyValueTransfer())
            ->setCurrency($currencyTransfer)
            ->setStore($storeTransfer)
            ->setFkStore($newPriceProductOfferData[$this->columnIdCreator->createStoreColumnId()])
            ->setFkCurrency($newPriceProductOfferData[$this->columnIdCreator->createCurrencyColumnId()]);

        $priceTypeName = (string)$priceTypeTransfer->getName();
        $netAmountKey = $this->columnIdCreator->createNetAmountColumnId($priceTypeName);
        $grossAmountKey = $this->columnIdCreator->createGrossAmountColumnId($priceTypeName);

        $netAmount = $newPriceProductOfferData[$netAmountKey] ?
            $this->moneyFacade->convertDecimalToInteger((float)$newPriceProductOfferData[$netAmountKey]) : null;
        $grossAmount = $newPriceProductOfferData[$grossAmountKey] ?
            $this->moneyFacade->convertDecimalToInteger((float)$newPriceProductOfferData[$grossAmountKey]) : null;
        $volumeQuantity = (int)$newPriceProductOfferData[$this->columnIdCreator->createVolumeQuantityColumnId()];

        $isEmptyNonDefaultVolumePrice = (
            $volumeQuantity !== 1 && $priceTypeName !== static::PRICE_TYPE_DEFAULT
            && $grossAmount === null && $netAmount === null
        );

        if ($isEmptyNonDefaultVolumePrice) {
            return null;
        }

        $moneyValueTransfer
            ->setNetAmount($netAmount)
            ->setGrossAmount($grossAmount);

        return (new PriceProductTransfer())
            ->setFkPriceType($priceTypeTransfer->getIdPriceType())
            ->setPriceType($priceTypeTransfer)
            ->setMoneyValue($moneyValueTransfer)
            ->setPriceDimension($priceProductDimensionTransfer)
            ->setVolumeQuantity($volumeQuantity);
    }

    /**
     * @param array<mixed> $prices
     * @param array<\Generated\Shared\Transfer\PriceTypeTransfer> $priceTypes
     *
     * @return array<mixed>
     */
    protected function addPrices(PriceProductTransfer $priceProductTransfer, array $prices, array $priceTypes): array
    {
        foreach ($priceTypes as $priceTypeTransfer) {
            $priceTypeName = $priceTypeTransfer->getNameOrFail();

            $currentPriceTypeTransfer = $priceProductTransfer->getPriceTypeOrFail();
            if ($currentPriceTypeTransfer->getName() !== $priceTypeName) {
                continue;
            }

            $netAmountKey = $this->columnIdCreator->createNetAmountColumnId($priceTypeName);
            $grossAmountKey = $this->columnIdCreator->createGrossAmountColumnId($priceTypeName);
            $moneyValueTransfer = $priceProductTransfer->getMoneyValueOrFail();

            $prices[$netAmountKey] = $this->moneyFacade->convertIntegerToDecimal((int)$moneyValueTransfer->getNetAmount());
            $prices[$grossAmountKey] = $this->moneyFacade->convertIntegerToDecimal((int)$moneyValueTransfer->getGrossAmount());
        }

        return $prices;
    }
}
