<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Table\ProductOfferTable\DataProvider;

use Generated\Shared\Transfer\GuiTableDataTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;
use Generated\Shared\Transfer\ProductOfferCriteriaFilterTransfer;
use Generated\Shared\Transfer\ProductOfferTransfer;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Builder\ProductNameBuilderInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Table\ProductOfferTable\ProductOfferTable;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade\ProductOfferMerchantPortalGuiToTranslatorFacadeInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Service\ProductOfferMerchantPortalGuiToUtilDateTimeServiceInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Persistence\ProductOfferMerchantPortalGuiRepositoryInterface;

class ProductOfferTableDataProvider implements ProductOfferTableDataProviderInterface
{
    protected const COLUMN_DATA_IS_NEVER_OUT_OF_STOCK = 'always in stock';
    protected const COLUMN_DATA_VISIBILITY_ONLINE = 'online';
    protected const COLUMN_DATA_VISIBILITY_OFFLINE = 'offline';

    /**
     * @var \Spryker\Zed\ProductOfferMerchantPortalGui\Persistence\ProductOfferMerchantPortalGuiRepositoryInterface
     */
    protected $productOfferMerchantPortalGuiRepository;

    /**
     * @var \Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade\ProductOfferMerchantPortalGuiToTranslatorFacadeInterface
     */
    protected $translatorFacade;

    /**
     * @var \Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Builder\ProductNameBuilderInterface
     */
    protected $productNameBuilder;

    /**
     * @var \Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Service\ProductOfferMerchantPortalGuiToUtilDateTimeServiceInterface
     */
    protected $utilDateTimeService;

    /**
     * @param \Spryker\Zed\ProductOfferMerchantPortalGui\Persistence\ProductOfferMerchantPortalGuiRepositoryInterface $productOfferMerchantPortalGuiRepository
     * @param \Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade\ProductOfferMerchantPortalGuiToTranslatorFacadeInterface $translatorFacade
     * @param \Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Service\ProductOfferMerchantPortalGuiToUtilDateTimeServiceInterface $utilDateTimeService
     * @param \Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Builder\ProductNameBuilderInterface $productNameBuilder
     */
    public function __construct(
        ProductOfferMerchantPortalGuiRepositoryInterface $productOfferMerchantPortalGuiRepository,
        ProductOfferMerchantPortalGuiToTranslatorFacadeInterface $translatorFacade,
        ProductOfferMerchantPortalGuiToUtilDateTimeServiceInterface $utilDateTimeService,
        ProductNameBuilderInterface $productNameBuilder
    ) {
        $this->productOfferMerchantPortalGuiRepository = $productOfferMerchantPortalGuiRepository;
        $this->translatorFacade = $translatorFacade;
        $this->productNameBuilder = $productNameBuilder;
        $this->utilDateTimeService = $utilDateTimeService;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductOfferCriteriaFilterTransfer $productOfferCriteriaFilterTransfer
     *
     * @return \Generated\Shared\Transfer\GuiTableDataTransfer
     */
    public function getProductOfferTableData(ProductOfferCriteriaFilterTransfer $productOfferCriteriaFilterTransfer): GuiTableDataTransfer
    {
        $productOfferCollectionTransfer = $this->productOfferMerchantPortalGuiRepository->getProductOfferTableData($productOfferCriteriaFilterTransfer);
        $productTableDataArray = [];

        foreach ($productOfferCollectionTransfer->getProductOffers() as $productOfferTransfer) {
            $productTableDataArray[] = [
                ProductOfferTable::COL_KEY_OFFER_REFERENCE => $productOfferTransfer->getProductOfferReference(),
                ProductOfferTable::COL_KEY_MERCHANT_SKU => $productOfferTransfer->getMerchantSku(),
                ProductOfferTable::COL_KEY_CONCRETE_SKU => $productOfferTransfer->getConcreteSku(),
                ProductOfferTable::COL_KEY_IMAGE => $this->getImageUrl($productOfferTransfer),
                ProductOfferTable::COL_KEY_PRODUCT_NAME => $this->getNameColumnData($productOfferTransfer),
                ProductOfferTable::COL_KEY_STORES => $this->getStoresColumnData($productOfferTransfer),
                ProductOfferTable::COL_KEY_STOCK => $this->getStockColumnData($productOfferTransfer),
                ProductOfferTable::COL_KEY_VISIBILITY => $this->getVisibilityColumnData($productOfferTransfer),
                ProductOfferTable::COL_KEY_VALID_FROM => $this->getValidFromColumnData($productOfferTransfer),
                ProductOfferTable::COL_KEY_VALID_TO => $this->getValidToColumnData($productOfferTransfer),
                ProductOfferTable::COL_KEY_CREATED_AT => $this->getFormattedDateTime($productOfferTransfer->getCreatedAt()),
                ProductOfferTable::COL_KEY_UPDATED_AT => $this->getFormattedDateTime($productOfferTransfer->getUpdatedAt()),
            ];
        }

        $paginationTransfer = $productOfferCollectionTransfer->getPagination();

        return (new GuiTableDataTransfer())->setData($productTableDataArray)
            ->setPage($paginationTransfer->getPage())
            ->setPageSize($paginationTransfer->getMaxPerPage())
            ->setTotal($paginationTransfer->getNbResults());
    }

    /**
     * @param \Generated\Shared\Transfer\ProductOfferTransfer $productOfferTransfer
     *
     * @return string|null
     */
    protected function getNameColumnData(ProductOfferTransfer $productOfferTransfer): ?string
    {
        $productConcreteTransfer = (new ProductConcreteTransfer())
            ->setAttributes($productOfferTransfer->getProductAttributes())
            ->setLocalizedAttributes($productOfferTransfer->getProductLocalizedAttributes());

        return $this->productNameBuilder->buildProductName($productConcreteTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\ProductOfferTransfer $productOfferTransfer
     *
     * @return string
     */
    protected function getStoresColumnData(ProductOfferTransfer $productOfferTransfer): string
    {
        $storeTransfers = $productOfferTransfer->getStores();
        $storeNames = [];

        foreach ($storeTransfers as $storeTransfer) {
            $storeNames[] = $storeTransfer->getName();
        }

        return implode(', ', $storeNames);
    }

    /**
     * @param \Generated\Shared\Transfer\ProductOfferTransfer $productOfferTransfer
     *
     * @return int|string|null
     */
    protected function getStockColumnData(ProductOfferTransfer $productOfferTransfer)
    {
        $productOfferStockTransfer = $productOfferTransfer->getProductOfferStock();

        if (!$productOfferStockTransfer) {
            return null;
        }

        if ($productOfferStockTransfer->getIsNeverOutOfStock()) {
            return $this->translatorFacade->trans(static::COLUMN_DATA_IS_NEVER_OUT_OF_STOCK);
        }

        $quantity = $productOfferStockTransfer->getQuantity();

        return $quantity === null ? $quantity : $quantity->toInt();
    }

    /**
     * @param \Generated\Shared\Transfer\ProductOfferTransfer $productOfferTransfer
     *
     * @return string
     */
    protected function getVisibilityColumnData(ProductOfferTransfer $productOfferTransfer): string
    {
        if ($productOfferTransfer->getIsActive()) {
            return $this->translatorFacade->trans(static::COLUMN_DATA_VISIBILITY_ONLINE);
        }

        return $this->translatorFacade->trans(static::COLUMN_DATA_VISIBILITY_OFFLINE);
    }

    /**
     * @param \Generated\Shared\Transfer\ProductOfferTransfer $productOfferTransfer
     *
     * @return string|null
     */
    protected function getValidFromColumnData(ProductOfferTransfer $productOfferTransfer): ?string
    {
        $productOfferValidityTransfer = $productOfferTransfer->getProductOfferValidity();

        if (!$productOfferValidityTransfer) {
            return null;
        }

        return $this->getFormattedDateTime($productOfferValidityTransfer->getValidFrom());
    }

    /**
     * @param \Generated\Shared\Transfer\ProductOfferTransfer $productOfferTransfer
     *
     * @return string|null
     */
    protected function getValidToColumnData(ProductOfferTransfer $productOfferTransfer): ?string
    {
        $productOfferValidityTransfer = $productOfferTransfer->getProductOfferValidity();

        if (!$productOfferValidityTransfer) {
            return null;
        }

        return $this->getFormattedDateTime($productOfferValidityTransfer->getValidTo());
    }

    /**
     * @param string|null $dateTime
     *
     * @return string|null
     */
    protected function getFormattedDateTime(?string $dateTime): ?string
    {
        return $dateTime ? $this->utilDateTimeService->formatDateTimeToIso8601($dateTime) : null;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductOfferTransfer $productOfferTransfer
     *
     * @return string|null
     */
    protected function getImageUrl(ProductOfferTransfer $productOfferTransfer): ?string
    {
        return isset($productOfferTransfer->getProductImages()[0])
            ? $productOfferTransfer->getProductImages()[0]->getExternalUrlSmall()
            : null;
    }
}
