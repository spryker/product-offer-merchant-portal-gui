<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferMerchantPortalGui\Communication\GuiTable\DataProvider;

use ArrayObject;
use Generated\Shared\Transfer\GuiTableDataRequestTransfer;
use Generated\Shared\Transfer\GuiTableDataResponseTransfer;
use Generated\Shared\Transfer\GuiTableRowDataResponseTransfer;
use Generated\Shared\Transfer\MoneyValueTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Generated\Shared\Transfer\PriceProductOfferCriteriaTransfer;
use Generated\Shared\Transfer\PriceProductOfferTableCriteriaTransfer;
use Generated\Shared\Transfer\PriceProductOfferTableViewCollectionTransfer;
use Spryker\Shared\GuiTable\DataProvider\AbstractGuiTableDataProvider;
use Spryker\Shared\Kernel\Transfer\AbstractTransfer;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Mapper\PriceProductOfferTableDataMapperInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Reader\PriceProductReaderInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Sorter\PriceProductOfferTableViewSorterInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade\ProductOfferMerchantPortalGuiToMerchantUserFacadeInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade\ProductOfferMerchantPortalGuiToMoneyFacadeInterface;

class ProductOfferPriceGuiTableDataProvider extends AbstractGuiTableDataProvider
{
    protected ?int $idProductOffer;

    protected ProductOfferMerchantPortalGuiToMerchantUserFacadeInterface $merchantUserFacade;

    protected ProductOfferMerchantPortalGuiToMoneyFacadeInterface $moneyFacade;

    protected PriceProductOfferTableDataMapperInterface $priceProductOfferTableDataMapper;

    protected PriceProductReaderInterface $priceProductReader;

    protected PriceProductOfferTableViewSorterInterface $priceProductOfferTableViewSorter;

    public function __construct(
        ProductOfferMerchantPortalGuiToMerchantUserFacadeInterface $merchantUserFacade,
        ProductOfferMerchantPortalGuiToMoneyFacadeInterface $moneyFacade,
        PriceProductOfferTableDataMapperInterface $priceProductOfferTableDataMapper,
        PriceProductReaderInterface $priceProductReader,
        PriceProductOfferTableViewSorterInterface $priceProductOfferTableViewSorter,
        ?int $idProductOffer = null
    ) {
        $this->merchantUserFacade = $merchantUserFacade;
        $this->idProductOffer = $idProductOffer;
        $this->moneyFacade = $moneyFacade;
        $this->priceProductOfferTableDataMapper = $priceProductOfferTableDataMapper;
        $this->priceProductReader = $priceProductReader;
        $this->priceProductOfferTableViewSorter = $priceProductOfferTableViewSorter;
    }

    protected function createCriteria(GuiTableDataRequestTransfer $guiTableDataRequestTransfer): AbstractTransfer
    {
        $idMerchant = $this->merchantUserFacade->getCurrentMerchantUser()->getIdMerchantOrFail();

        return (new PriceProductOfferTableCriteriaTransfer())
            ->setIdProductOffer($this->idProductOffer)
            ->setIdMerchant($idMerchant);
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductOfferTableCriteriaTransfer $criteriaTransfer
     */
    protected function fetchData(AbstractTransfer $criteriaTransfer): GuiTableDataResponseTransfer
    {
        if (!$criteriaTransfer->getIdProductOffer()) {
            return new GuiTableDataResponseTransfer();
        }

        $criteriaTransfer = $this->replaceSortingFields($criteriaTransfer);

        $priceProductOfferTableViewCollectionTransfer = $this
            ->createPriceProductOfferTableViewCollectionTransfer($criteriaTransfer);

        $guiTableDataResponseTransfer = new GuiTableDataResponseTransfer();

        foreach ($priceProductOfferTableViewCollectionTransfer->getPriceProductOfferTableViews() as $priceProductOfferTableViewTransfer) {
            $responseData = $priceProductOfferTableViewTransfer->toArray();

            foreach ($priceProductOfferTableViewTransfer->getPrices() as $priceType => $priceValue) {
                $responseData[$priceType] = $this->convertIntegerToDecimal($priceValue);
            }

            $guiTableDataResponseTransfer->addRow((new GuiTableRowDataResponseTransfer())->setResponseData($responseData));
        }

        $paginationTransfer = $priceProductOfferTableViewCollectionTransfer->getPaginationOrFail();

        return $guiTableDataResponseTransfer
            ->setPage($paginationTransfer->getPage())
            ->setPageSize($paginationTransfer->getMaxPerPage())
            ->setTotal($paginationTransfer->getNbResults());
    }

    protected function createPriceProductOfferTableViewCollectionTransfer(
        PriceProductOfferTableCriteriaTransfer $criteriaTransfer
    ): PriceProductOfferTableViewCollectionTransfer {
        $priceProductTransfers = $this->priceProductReader
            ->getPriceProductTransfers((
                (new PriceProductOfferCriteriaTransfer())
                    ->setStoreIds($criteriaTransfer->getFilterInStores())
                    ->setCurrencyIds($criteriaTransfer->getFilterInCurrencies())
                    ->setIdProductOffer($criteriaTransfer->getIdProductOffer())
            ));

        $priceProductOfferTableViewCollectionTransfer = $this->priceProductOfferTableDataMapper
            ->mapPriceProductTransfersToPriceProductOfferTableViewCollectionTransfer(
                $priceProductTransfers,
                new PriceProductOfferTableViewCollectionTransfer(),
            );

        $this->priceProductOfferTableViewSorter->sortPriceProductOfferTableViews(
            $priceProductOfferTableViewCollectionTransfer,
            $criteriaTransfer,
        );

        $this->updatePaginationTransfer(
            $priceProductOfferTableViewCollectionTransfer,
            $criteriaTransfer,
        );

        $this->applyPagination($priceProductOfferTableViewCollectionTransfer);

        return $priceProductOfferTableViewCollectionTransfer;
    }

    protected function updatePaginationTransfer(
        PriceProductOfferTableViewCollectionTransfer $priceProductOfferTableViewCollectionTransfer,
        PriceProductOfferTableCriteriaTransfer $criteriaTransfer
    ): PaginationTransfer {
        $count = $priceProductOfferTableViewCollectionTransfer->getPriceProductOfferTableViews()->count();

        return $priceProductOfferTableViewCollectionTransfer->getPaginationOrFail()
            ->setPage($criteriaTransfer->getPageOrFail())
            ->setMaxPerPage($criteriaTransfer->getPageSizeOrFail())
            ->setLastPage((int)($count / $criteriaTransfer->getPageSizeOrFail()));
    }

    protected function applyPagination(
        PriceProductOfferTableViewCollectionTransfer $priceProductOfferTableViewCollectionTransfer
    ): PriceProductOfferTableViewCollectionTransfer {
        $priceProductOfferTableViews = $priceProductOfferTableViewCollectionTransfer
            ->getPriceProductOfferTableViews()
            ->getArrayCopy();

        $paginationTransfer = $priceProductOfferTableViewCollectionTransfer->getPaginationOrFail();

        $positionStart = ($paginationTransfer->getPageOrFail() - 1) * $paginationTransfer->getMaxPerPageOrFail();

        $priceProductOfferTableViewsOnCurrentPage = array_slice(
            $priceProductOfferTableViews,
            $positionStart,
            $paginationTransfer->getMaxPerPage(),
        );

        $priceProductOfferTableViewCollectionTransfer->setPriceProductOfferTableViews(
            new ArrayObject($priceProductOfferTableViewsOnCurrentPage),
        );

        return $priceProductOfferTableViewCollectionTransfer;
    }

    protected function replaceSortingFields(PriceProductOfferTableCriteriaTransfer $criteriaTransfer): PriceProductOfferTableCriteriaTransfer
    {
        /** @var string $orderByField */
        $orderByField = $criteriaTransfer->getOrderBy();

        if (!$orderByField) {
            return $criteriaTransfer;
        }

        if (strpos($orderByField, '[') === false) {
            return $criteriaTransfer;
        }

        /** @var string $orderByField */
        $orderByField = str_replace(']', '', $orderByField);
        $orderByField = explode('[', $orderByField);

        if ($orderByField[2] === MoneyValueTransfer::NET_AMOUNT) {
            return $criteriaTransfer->setOrderBy($orderByField[0] . '_net');
        }

        if ($orderByField[2] === MoneyValueTransfer::GROSS_AMOUNT) {
            return $criteriaTransfer->setOrderBy($orderByField[0] . '_gross');
        }

        return $criteriaTransfer;
    }

    /**
     * @param mixed $value
     */
    protected function convertIntegerToDecimal($value): ?float
    {
        if ($value === '' || $value === null) {
            return null;
        }

        return $this->moneyFacade->convertIntegerToDecimal((int)$value);
    }
}
