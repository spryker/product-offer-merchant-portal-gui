<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferMerchantPortalGui\Persistence;

use DateTime;
use Generated\Shared\Transfer\LocalizedAttributesTransfer;
use Generated\Shared\Transfer\MerchantProductOfferCountsTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Generated\Shared\Transfer\PriceProductOfferTableCriteriaTransfer;
use Generated\Shared\Transfer\PriceProductOfferTableViewCollectionTransfer;
use Generated\Shared\Transfer\PriceProductOfferTableViewTransfer;
use Generated\Shared\Transfer\ProductConcreteCollectionTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;
use Generated\Shared\Transfer\ProductImageTransfer;
use Generated\Shared\Transfer\ProductOfferCollectionTransfer;
use Generated\Shared\Transfer\ProductOfferStockTransfer;
use Generated\Shared\Transfer\ProductOfferTableCriteriaTransfer;
use Generated\Shared\Transfer\ProductOfferTransfer;
use Generated\Shared\Transfer\ProductOfferValidityTransfer;
use Generated\Shared\Transfer\ProductTableCriteriaTransfer;
use Orm\Zed\Currency\Persistence\Map\SpyCurrencyTableMap;
use Orm\Zed\PriceProduct\Persistence\Map\SpyPriceProductStoreTableMap;
use Orm\Zed\PriceProduct\Persistence\Map\SpyPriceProductTableMap;
use Orm\Zed\PriceProduct\Persistence\Map\SpyPriceTypeTableMap;
use Orm\Zed\PriceProduct\Persistence\SpyPriceProductStoreQuery;
use Orm\Zed\PriceProductOffer\Persistence\Map\SpyPriceProductOfferTableMap;
use Orm\Zed\Product\Persistence\Map\SpyProductAbstractTableMap;
use Orm\Zed\Product\Persistence\Map\SpyProductLocalizedAttributesTableMap;
use Orm\Zed\Product\Persistence\Map\SpyProductTableMap;
use Orm\Zed\Product\Persistence\SpyProductQuery;
use Orm\Zed\ProductImage\Persistence\Map\SpyProductImageSetTableMap;
use Orm\Zed\ProductImage\Persistence\Map\SpyProductImageTableMap;
use Orm\Zed\ProductOffer\Persistence\Map\SpyProductOfferStoreTableMap;
use Orm\Zed\ProductOffer\Persistence\Map\SpyProductOfferTableMap;
use Orm\Zed\ProductOffer\Persistence\SpyProductOfferQuery;
use Orm\Zed\ProductOfferStock\Persistence\Map\SpyProductOfferStockTableMap;
use Orm\Zed\ProductOfferValidity\Persistence\Map\SpyProductOfferValidityTableMap;
use Orm\Zed\ProductValidity\Persistence\Map\SpyProductValidityTableMap;
use Orm\Zed\Store\Persistence\Map\SpyStoreTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Criterion\LikeCriterion;
use Propel\Runtime\ActiveQuery\Join;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Util\PropelModelPager;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;
use Spryker\Zed\ProductOfferMerchantPortalGui\Persistence\Propel\ProductOfferTableDataMapper;
use Spryker\Zed\ProductOfferMerchantPortalGui\Persistence\Propel\ProductTableDataMapper;

/**
 * @method \Spryker\Zed\ProductOfferMerchantPortalGui\Persistence\ProductOfferMerchantPortalGuiPersistenceFactory getFactory()
 */
class ProductOfferMerchantPortalGuiRepository extends AbstractRepository implements ProductOfferMerchantPortalGuiRepositoryInterface
{
    /**
     * @uses \Spryker\Zed\ProductOfferMerchantPortalGui\Communication\GuiTable\ConfigurationProvider\ProductOfferGuiTableConfigurationProvider::COL_KEY_OFFER_REFERENCE
     */
    protected const COL_KEY_OFFER_REFERENCE = 'offerReference';

    /**
     * @uses \Spryker\Zed\ProductOfferMerchantPortalGui\Communication\GuiTable\ConfigurationProvider\ProductGuiTableConfigurationProvider::COL_KEY_SKU
     */
    protected const COL_KEY_PRODUCT_SKU = 'sku';

    protected const SUFFIX_PRICE_TYPE_NET = '_net';
    protected const SUFFIX_PRICE_TYPE_GROSS = '_gross';

    protected const COL_PRICE_PRODUCT_OFFER_IDS = 'price_product_offer_ids';
    protected const COL_TYPE_PRICE_PRODUCT_OFFER_IDS = 'type_price_product_offer_ids';

    /**
     * @param \Generated\Shared\Transfer\ProductTableCriteriaTransfer $productTableCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\ProductConcreteCollectionTransfer
     */
    public function getProductTableData(ProductTableCriteriaTransfer $productTableCriteriaTransfer): ProductConcreteCollectionTransfer
    {
        $productConcreteMapper = $this->getFactory()->createProductTableDataMapper();

        $productConcreteQuery = $this->buildProductTableBaseQuery($productTableCriteriaTransfer);
        $productConcreteQuery = $this->applyProductSearch($productConcreteQuery, $productTableCriteriaTransfer);
        $productConcreteQuery = $this->addProductFilters($productConcreteQuery, $productTableCriteriaTransfer);
        $productConcreteQuery = $this->addProductSorting($productConcreteQuery, $productTableCriteriaTransfer);

        $propelPager = $productConcreteQuery->paginate(
            $productTableCriteriaTransfer->requirePage()->getPage(),
            $productTableCriteriaTransfer->requirePageSize()->getPageSize()
        );

        $paginationTransfer = $this->hydratePaginationTransfer($propelPager);

        $productConcreteCollectionTransfer = $productConcreteMapper->mapProductTableDataArrayToProductConcreteCollectionTransfer(
            $propelPager->getResults()->getData(),
            new ProductConcreteCollectionTransfer()
        );
        $productConcreteCollectionTransfer->setPagination($paginationTransfer);

        return $productConcreteCollectionTransfer;
    }

    /**
     * @module ProductOffer
     * @module ProductImage
     * @module ProductValidity
     *
     * @param \Generated\Shared\Transfer\ProductTableCriteriaTransfer $productTableCriteriaTransfer
     *
     * @return \Orm\Zed\Product\Persistence\SpyProductQuery
     */
    protected function buildProductTableBaseQuery(ProductTableCriteriaTransfer $productTableCriteriaTransfer): SpyProductQuery
    {
        $productConcreteQuery = $this->getFactory()->getProductConcretePropelQuery();

        /** @var \Generated\Shared\Transfer\LocaleTransfer $localeTransfer */
        $localeTransfer = $productTableCriteriaTransfer->requireLocale()->getLocale();
        /** @var int $idLocale */
        $idLocale = $localeTransfer->requireIdLocale()->getIdLocale();
        /** @var int $idMerchant */
        $idMerchant = $productTableCriteriaTransfer->requireIdMerchant()->getIdMerchant();

        $productConcreteQuery = $this->addLocalizedAttributesToProductTableQuery($productConcreteQuery, $idLocale);
        $productConcreteQuery->leftJoinSpyProductValidity()
            ->addAsColumn(ProductConcreteTransfer::ID_PRODUCT_CONCRETE, SpyProductTableMap::COL_ID_PRODUCT)
            ->addAsColumn(ProductConcreteTransfer::SKU, SpyProductTableMap::COL_SKU)
            ->addAsColumn(ProductConcreteTransfer::ATTRIBUTES, SpyProductTableMap::COL_ATTRIBUTES)
            ->addAsColumn(ProductConcreteTransfer::LOCALIZED_ATTRIBUTES, SpyProductLocalizedAttributesTableMap::COL_ATTRIBUTES)
            ->addAsColumn(ProductConcreteTransfer::IS_ACTIVE, SpyProductTableMap::COL_IS_ACTIVE)
            ->addAsColumn(LocalizedAttributesTransfer::NAME, SpyProductLocalizedAttributesTableMap::COL_NAME)
            ->addAsColumn(ProductConcreteTransfer::STORES, sprintf('(%s)', $this->createProductStoresSubquery()))
            ->addAsColumn(ProductImageTransfer::EXTERNAL_URL_SMALL, sprintf('(%s)', $this->createProductImagesSubquery($idLocale)))
            ->addAsColumn(ProductConcreteTransfer::NUMBER_OF_OFFERS, sprintf('(%s)', $this->createProductOffersCountSubquery($idMerchant)))
            ->addAsColumn(ProductConcreteTransfer::VALID_FROM, SpyProductValidityTableMap::COL_VALID_FROM)
            ->addAsColumn(ProductConcreteTransfer::VALID_TO, SpyProductValidityTableMap::COL_VALID_TO)
            ->where(sprintf('(%s) IS NOT NULL', $this->createProductStoresSubquery()))
            ->setFormatter(ModelCriteria::FORMAT_ARRAY);

        return $productConcreteQuery;
    }

    /**
     * @param \Orm\Zed\Product\Persistence\SpyProductQuery $productConcreteQuery
     * @param int $idLocale
     *
     * @return \Orm\Zed\Product\Persistence\SpyProductQuery
     */
    protected function addLocalizedAttributesToProductTableQuery(SpyProductQuery $productConcreteQuery, int $idLocale): SpyProductQuery
    {
        $productConcreteQuery->joinSpyProductLocalizedAttributes()
            ->addJoinCondition(
                'SpyProductLocalizedAttributes',
                sprintf('%s = ?', SpyProductLocalizedAttributesTableMap::COL_FK_LOCALE),
                $idLocale
            );

        return $productConcreteQuery;
    }

    /**
     * @return string
     */
    protected function createProductStoresSubquery(): string
    {
        $productStoresSubquery = $this->getFactory()->getStorePropelQuery()
            ->joinSpyProductAbstractStore()
            ->useSpyProductAbstractStoreQuery()
            ->joinSpyProductAbstract()
            ->endUse()
            ->addAsColumn('stores', sprintf('GROUP_CONCAT(DISTINCT %s)', SpyStoreTableMap::COL_NAME))
            ->where(sprintf('%s = %s', SpyProductAbstractTableMap::COL_ID_PRODUCT_ABSTRACT, SpyProductTableMap::COL_FK_PRODUCT_ABSTRACT));
        $params = [];

        return $productStoresSubquery->createSelectSql($params);
    }

    /**
     * @param int $idLocale
     *
     * @return string
     */
    protected function createProductImagesSubquery(int $idLocale): string
    {
        $productImagesSubquery = $this->getFactory()->getProductImagePropelQuery()
            ->joinSpyProductImageSetToProductImage()
            ->useSpyProductImageSetToProductImageQuery()
            ->joinSpyProductImageSet()
            ->endUse()
            ->where(sprintf(
                '%1$s = %2$s AND (%3$s = %4$d OR %3$s IS NULL)',
                SpyProductImageSetTableMap::COL_FK_PRODUCT,
                SpyProductTableMap::COL_ID_PRODUCT,
                SpyProductImageSetTableMap::COL_FK_LOCALE,
                $idLocale
            ))
            ->addSelectColumn(SpyProductImageTableMap::COL_EXTERNAL_URL_SMALL)
            ->orderBy(SpyProductImageSetTableMap::COL_FK_LOCALE)
            ->limit(1);
        $params = [];

        return $productImagesSubquery->createSelectSql($params);
    }

    /**
     * @param int $idMerchant
     *
     * @return string
     */
    protected function createProductOffersCountSubquery(int $idMerchant): string
    {
        $productOffersSubquery = $this->getFactory()->getProductOfferPropelQuery()
            ->addAsColumn('offers_count', 'COUNT(*)')
            ->where(sprintf(
                '%s = %s AND %s = %s',
                SpyProductOfferTableMap::COL_CONCRETE_SKU,
                SpyProductTableMap::COL_SKU,
                SpyProductOfferTableMap::COL_FK_MERCHANT,
                $idMerchant
            ));
        $params = [];

        return $productOffersSubquery->createSelectSql($params);
    }

    /**
     * @param \Orm\Zed\Product\Persistence\SpyProductQuery $productConcreteQuery
     * @param \Generated\Shared\Transfer\ProductTableCriteriaTransfer $productTableCriteriaTransfer
     *
     * @return \Orm\Zed\Product\Persistence\SpyProductQuery
     */
    protected function addProductSorting(
        SpyProductQuery $productConcreteQuery,
        ProductTableCriteriaTransfer $productTableCriteriaTransfer
    ): SpyProductQuery {
        $orderColumn = $productTableCriteriaTransfer->getOrderBy() ?? static::COL_KEY_PRODUCT_SKU;
        $orderDirection = $productTableCriteriaTransfer->getOrderDirection() ?? Criteria::DESC;

        if (!$orderColumn || !$orderDirection) {
            return $productConcreteQuery;
        }

        $orderColumn = ProductTableDataMapper::PRODUCT_DATA_COLUMN_MAP[$orderColumn] ?? $orderColumn;

        if ($orderColumn === SpyProductTableMap::COL_SKU) {
            $productConcreteQuery = $this->addNaturalSorting($productConcreteQuery, $orderColumn, $orderDirection);
        }

        $productConcreteQuery->orderBy($orderColumn, $orderDirection);

        return $productConcreteQuery;
    }

    /**
     * @param \Propel\Runtime\Util\PropelModelPager $propelPager
     *
     * @return \Generated\Shared\Transfer\PaginationTransfer
     */
    protected function hydratePaginationTransfer(
        PropelModelPager $propelPager
    ): PaginationTransfer {
        return (new PaginationTransfer())
            ->setNbResults($propelPager->getNbResults())
            ->setPage($propelPager->getPage())
            ->setMaxPerPage($propelPager->getMaxPerPage())
            ->setFirstIndex($propelPager->getFirstIndex())
            ->setFirstIndex($propelPager->getFirstIndex())
            ->setLastIndex($propelPager->getLastIndex())
            ->setFirstPage($propelPager->getFirstPage())
            ->setLastPage($propelPager->getLastPage())
            ->setNextPage($propelPager->getNextPage())
            ->setPreviousPage($propelPager->getPreviousPage());
    }

    /**
     * @param \Orm\Zed\Product\Persistence\SpyProductQuery $productConcreteQuery
     * @param \Generated\Shared\Transfer\ProductTableCriteriaTransfer $productTableCriteriaTransfer
     *
     * @return \Orm\Zed\Product\Persistence\SpyProductQuery
     */
    protected function addProductFilters(
        SpyProductQuery $productConcreteQuery,
        ProductTableCriteriaTransfer $productTableCriteriaTransfer
    ): SpyProductQuery {
        $productConcreteQuery = $this->addIsActiveProductFilter($productConcreteQuery, $productTableCriteriaTransfer);
        $productConcreteQuery = $this->addHasOffersProductFilter($productConcreteQuery, $productTableCriteriaTransfer);

        return $productConcreteQuery;
    }

    /**
     * @param \Orm\Zed\Product\Persistence\SpyProductQuery $productConcreteQuery
     * @param \Generated\Shared\Transfer\ProductTableCriteriaTransfer $productTableCriteriaTransfer
     *
     * @return \Orm\Zed\Product\Persistence\SpyProductQuery
     */
    protected function applyProductSearch(
        SpyProductQuery $productConcreteQuery,
        ProductTableCriteriaTransfer $productTableCriteriaTransfer
    ): SpyProductQuery {
        $searchTerm = $productTableCriteriaTransfer->getSearchTerm();

        if (!$searchTerm) {
            return $productConcreteQuery;
        }

        $criteria = new Criteria();
        $productNameSearchCriterion = $this->getProductNameSearchCriteria($criteria, $searchTerm);
        $productSkuSearchCriterion = $this->getProductSkuSearchCriteria($criteria, $searchTerm);
        $productNameSearchCriterion->addOr($productSkuSearchCriterion);

        return $productConcreteQuery->addAnd($productNameSearchCriterion);
    }

    /**
     * @param \Orm\Zed\ProductOffer\Persistence\SpyProductOfferQuery $productOfferQuery
     * @param \Generated\Shared\Transfer\ProductOfferTableCriteriaTransfer $productOfferTableCriteriaTransfer
     *
     * @return \Orm\Zed\ProductOffer\Persistence\SpyProductOfferQuery
     */
    protected function applyProductOfferSearch(
        SpyProductOfferQuery $productOfferQuery,
        ProductOfferTableCriteriaTransfer $productOfferTableCriteriaTransfer
    ): SpyProductOfferQuery {
        $searchTerm = $productOfferTableCriteriaTransfer->getSearchTerm();

        if (!$searchTerm) {
            return $productOfferQuery;
        }

        $criteria = new Criteria();
        $productNameSearchCriterion = $this->getProductNameSearchCriteria($criteria, $searchTerm);
        $productSkuSearchCriterion = $this->getProductSkuSearchCriteria($criteria, $searchTerm);
        $productOfferReferenceSearchCriterion = $this->getProductOfferReferenceSearchCriteria($criteria, $searchTerm);
        $productOfferMerchantSkuSearchCriterion = $this->getProductOfferMerchantSkuSearchCriteria($criteria, $searchTerm);

        $productNameSearchCriterion->addOr($productSkuSearchCriterion);
        $productSkuSearchCriterion->addOr($productOfferReferenceSearchCriterion);
        $productOfferReferenceSearchCriterion->addOr($productOfferMerchantSkuSearchCriterion);

        return $productOfferQuery->add($productNameSearchCriterion);
    }

    /**
     * @param \Orm\Zed\Product\Persistence\SpyProductQuery $productConcreteQuery
     * @param \Generated\Shared\Transfer\ProductTableCriteriaTransfer $productTableCriteriaTransfer
     *
     * @return \Orm\Zed\Product\Persistence\SpyProductQuery
     */
    protected function addIsActiveProductFilter(
        SpyProductQuery $productConcreteQuery,
        ProductTableCriteriaTransfer $productTableCriteriaTransfer
    ): SpyProductQuery {
        $filterValue = $productTableCriteriaTransfer->getFilterIsActive();
        if (!isset($filterValue)) {
            return $productConcreteQuery;
        }

        $productConcreteQuery->filterByIsActive($filterValue);

        return $productConcreteQuery;
    }

    /**
     * @module ProductOffer
     *
     * @param \Orm\Zed\Product\Persistence\SpyProductQuery $productConcreteQuery
     * @param \Generated\Shared\Transfer\ProductTableCriteriaTransfer $productTableCriteriaTransfer
     *
     * @return \Orm\Zed\Product\Persistence\SpyProductQuery
     */
    protected function addHasOffersProductFilter(
        SpyProductQuery $productConcreteQuery,
        ProductTableCriteriaTransfer $productTableCriteriaTransfer
    ): SpyProductQuery {
        $productConcreteHasOffers = $productTableCriteriaTransfer->getFilterHasOffers();

        if ($productConcreteHasOffers === null) {
            return $productConcreteQuery;
        }

        /** @var int $merchantUserId */
        $merchantUserId = $productTableCriteriaTransfer->requireIdMerchant()->getIdMerchant();

        $productConcreteQuery->where(
            sprintf(
                '(%s) %s 0',
                $this->createProductOffersCountSubquery($merchantUserId),
                $productConcreteHasOffers ? '>' : '='
            )
        );

        return $productConcreteQuery;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductOfferTableCriteriaTransfer $productOfferTableCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\ProductOfferCollectionTransfer
     */
    public function getProductOfferTableData(
        ProductOfferTableCriteriaTransfer $productOfferTableCriteriaTransfer
    ): ProductOfferCollectionTransfer {
        $productOfferQuery = $this->buildProductOfferTableBaseQuery($productOfferTableCriteriaTransfer);
        $productOfferQuery = $this->applyProductOfferSearch($productOfferQuery, $productOfferTableCriteriaTransfer);
        $productOfferQuery = $this->addProductOfferFilters($productOfferQuery, $productOfferTableCriteriaTransfer);
        $productOfferQuery = $this->addProductOfferSorting($productOfferQuery, $productOfferTableCriteriaTransfer);

        $propelPager = $productOfferQuery->paginate(
            $productOfferTableCriteriaTransfer->requirePage()->getPage(),
            $productOfferTableCriteriaTransfer->requirePageSize()->getPageSize()
        );
        $paginationTransfer = $this->hydratePaginationTransfer($propelPager);

        $productOfferCollectionTransfer = $this->getFactory()
            ->createProductOfferTableDataMapper()
            ->mapProductOfferTableDataArrayToProductOfferCollectionTransfer(
                $propelPager->getResults()->getData(),
                new ProductOfferCollectionTransfer()
            );
        $productOfferCollectionTransfer->setPagination($paginationTransfer);

        return $productOfferCollectionTransfer;
    }

    /**
     * @module ProductOffer
     * @module MerchantProductOffer
     * @module ProductOfferValidity
     * @module ProductOfferStock
     * @module Product
     * @module ProductImage
     *
     * @param \Generated\Shared\Transfer\ProductOfferTableCriteriaTransfer $productOfferTableCriteriaTransfer
     *
     * @return \Orm\Zed\ProductOffer\Persistence\SpyProductOfferQuery
     */
    protected function buildProductOfferTableBaseQuery(
        ProductOfferTableCriteriaTransfer $productOfferTableCriteriaTransfer
    ): SpyProductOfferQuery {
        $productOfferQuery = $this->getFactory()->getProductOfferPropelQuery();
        /** @var int $idMerchant */
        $idMerchant = $productOfferTableCriteriaTransfer->requireIdMerchant()->getIdMerchant();
        /** @var \Generated\Shared\Transfer\LocaleTransfer $localeTransfer */
        $localeTransfer = $productOfferTableCriteriaTransfer->requireLocale()->getLocale();
        /** @var int $idLocale */
        $idLocale = $localeTransfer->requireIdLocale()->getIdLocale();

        $productOfferQuery = $this->joinProductLocalizedAttributesToProductOfferQuery($productOfferQuery, $idLocale);
        $productOfferQuery->filterByFkMerchant($idMerchant)
            ->leftJoinSpyProductOfferValidity()
            ->leftJoinProductOfferStock()
            ->useProductOfferStockQuery(null, Criteria::LEFT_JOIN)
                ->useStockQuery()
                    ->useSpyMerchantStockQuery()
                        ->filterByIsDefault(true)
                    ->endUse()
                ->endUse()
            ->endUse()
            ->addAsColumn(ProductOfferTransfer::ID_PRODUCT_OFFER, SpyProductOfferTableMap::COL_ID_PRODUCT_OFFER)
            ->addAsColumn(ProductOfferTransfer::PRODUCT_OFFER_REFERENCE, SpyProductOfferTableMap::COL_PRODUCT_OFFER_REFERENCE)
            ->addAsColumn(ProductOfferTransfer::MERCHANT_SKU, SpyProductOfferTableMap::COL_MERCHANT_SKU)
            ->addAsColumn(ProductOfferTransfer::CONCRETE_SKU, SpyProductOfferTableMap::COL_CONCRETE_SKU)
            ->addAsColumn(ProductImageTransfer::EXTERNAL_URL_SMALL, sprintf('(%s)', $this->createProductImagesSubquery($idLocale)))
            ->addAsColumn(ProductOfferTransfer::STORES, sprintf('(%s)', $this->createProductOfferStoresSubquery()))
            ->addAsColumn(ProductOfferStockTransfer::QUANTITY, SpyProductOfferStockTableMap::COL_QUANTITY)
            ->addAsColumn(ProductOfferTransfer::IS_ACTIVE, SpyProductOfferTableMap::COL_IS_ACTIVE)
            ->addAsColumn(LocalizedAttributesTransfer::NAME, SpyProductLocalizedAttributesTableMap::COL_NAME)
            ->addAsColumn(ProductOfferTransfer::PRODUCT_ATTRIBUTES, SpyProductTableMap::COL_ATTRIBUTES)
            ->addAsColumn(ProductOfferTransfer::PRODUCT_LOCALIZED_ATTRIBUTES, SpyProductLocalizedAttributesTableMap::COL_ATTRIBUTES)
            ->addAsColumn(ProductOfferValidityTransfer::VALID_FROM, SpyProductOfferValidityTableMap::COL_VALID_FROM)
            ->addAsColumn(ProductOfferValidityTransfer::VALID_TO, SpyProductOfferValidityTableMap::COL_VALID_TO)
            ->addAsColumn(ProductOfferTransfer::CREATED_AT, SpyProductOfferTableMap::COL_CREATED_AT)
            ->addAsColumn(ProductOfferTransfer::UPDATED_AT, SpyProductOfferTableMap::COL_UPDATED_AT)
            ->select([
                ProductOfferTransfer::PRODUCT_OFFER_REFERENCE,
                ProductOfferTransfer::MERCHANT_SKU,
                ProductOfferTransfer::CONCRETE_SKU,
                ProductImageTransfer::EXTERNAL_URL_SMALL,
                ProductOfferTransfer::STORES,
                ProductOfferStockTransfer::QUANTITY,
                ProductOfferTransfer::IS_ACTIVE,
                LocalizedAttributesTransfer::NAME,
                ProductOfferTransfer::PRODUCT_ATTRIBUTES,
                ProductOfferTransfer::PRODUCT_LOCALIZED_ATTRIBUTES,
                ProductOfferValidityTransfer::VALID_FROM,
                ProductOfferValidityTransfer::VALID_TO,
                ProductOfferTransfer::CREATED_AT,
                ProductOfferTransfer::UPDATED_AT,
            ]);

        return $productOfferQuery;
    }

    /**
     * @return string
     */
    protected function createProductOfferStoresSubquery(): string
    {
        $storesSubquery = $this->getFactory()->getProductOfferStorePropelQuery()
            ->joinSpyStore()
            ->addAsColumn('stores', sprintf('GROUP_CONCAT(DISTINCT %s)', SpyStoreTableMap::COL_NAME))
            ->where(sprintf(
                '%s = %s',
                SpyProductOfferTableMap::COL_ID_PRODUCT_OFFER,
                SpyProductOfferStoreTableMap::COL_FK_PRODUCT_OFFER
            ));
        $params = [];

        return $storesSubquery->createSelectSql($params);
    }

    /**
     * @param \Orm\Zed\ProductOffer\Persistence\SpyProductOfferQuery $productOfferQuery
     * @param int $idLocale
     *
     * @return \Orm\Zed\ProductOffer\Persistence\SpyProductOfferQuery
     */
    protected function joinProductLocalizedAttributesToProductOfferQuery(SpyProductOfferQuery $productOfferQuery, int $idLocale): SpyProductOfferQuery
    {
        $productLocalizedAttributesJoinName = 'SpyProductLocalizedAttributes';
        $productJoin = new Join(SpyProductOfferTableMap::COL_CONCRETE_SKU, SpyProductTableMap::COL_SKU, Criteria::INNER_JOIN);
        $productLocalizedAttributesJoin = new Join(SpyProductTableMap::COL_ID_PRODUCT, SpyProductLocalizedAttributesTableMap::COL_FK_PRODUCT, Criteria::INNER_JOIN);

        $productOfferQuery->addJoinObject($productJoin, 'SpyProduct')
            ->addJoinObject($productLocalizedAttributesJoin, $productLocalizedAttributesJoinName)
            ->addJoinCondition(
                $productLocalizedAttributesJoinName,
                sprintf('%s = %s', SpyProductLocalizedAttributesTableMap::COL_FK_LOCALE, $idLocale)
            );

        return $productOfferQuery;
    }

    /**
     * @param \Propel\Runtime\ActiveQuery\Criteria $criteria
     * @param string $searchTerm
     *
     * @return \Propel\Runtime\ActiveQuery\Criterion\LikeCriterion
     */
    protected function getProductNameSearchCriteria(Criteria $criteria, string $searchTerm): LikeCriterion
    {
        /** @var \Propel\Runtime\ActiveQuery\Criterion\LikeCriterion $likeCriterion */
        $likeCriterion = $criteria->getNewCriterion(
            SpyProductLocalizedAttributesTableMap::COL_NAME,
            '%' . $searchTerm . '%',
            Criteria::LIKE
        );

        return $likeCriterion->setIgnoreCase(true);
    }

    /**
     * @param \Propel\Runtime\ActiveQuery\Criteria $criteria
     * @param string $searchTerm
     *
     * @return \Propel\Runtime\ActiveQuery\Criterion\LikeCriterion
     */
    protected function getProductSkuSearchCriteria(Criteria $criteria, string $searchTerm): LikeCriterion
    {
        /** @var \Propel\Runtime\ActiveQuery\Criterion\LikeCriterion $likeCriterion */
        $likeCriterion = $criteria->getNewCriterion(
            SpyProductTableMap::COL_SKU,
            '%' . $searchTerm . '%',
            Criteria::LIKE
        );

        return $likeCriterion->setIgnoreCase(true);
    }

    /**
     * @param \Propel\Runtime\ActiveQuery\Criteria $criteria
     * @param string $searchTerm
     *
     * @return \Propel\Runtime\ActiveQuery\Criterion\LikeCriterion
     */
    protected function getProductOfferReferenceSearchCriteria(Criteria $criteria, string $searchTerm): LikeCriterion
    {
        /** @var \Propel\Runtime\ActiveQuery\Criterion\LikeCriterion $likeCriterion */
        $likeCriterion = $criteria->getNewCriterion(
            SpyProductOfferTableMap::COL_PRODUCT_OFFER_REFERENCE,
            '%' . $searchTerm . '%',
            Criteria::LIKE
        );

        return $likeCriterion->setIgnoreCase(true);
    }

    /**
     * @param \Propel\Runtime\ActiveQuery\Criteria $criteria
     * @param string $searchTerm
     *
     * @return \Propel\Runtime\ActiveQuery\Criterion\LikeCriterion
     */
    protected function getProductOfferMerchantSkuSearchCriteria(Criteria $criteria, string $searchTerm): LikeCriterion
    {
        /** @var \Propel\Runtime\ActiveQuery\Criterion\LikeCriterion $likeCriterion */
        $likeCriterion = $criteria->getNewCriterion(
            SpyProductOfferTableMap::COL_MERCHANT_SKU,
            '%' . $searchTerm . '%',
            Criteria::LIKE
        );

        return $likeCriterion->setIgnoreCase(true);
    }

    /**
     * @param \Orm\Zed\ProductOffer\Persistence\SpyProductOfferQuery $productOfferQuery
     * @param \Generated\Shared\Transfer\ProductOfferTableCriteriaTransfer $productOfferTableCriteriaTransfer
     *
     * @return \Orm\Zed\ProductOffer\Persistence\SpyProductOfferQuery
     */
    protected function addProductOfferFilters(
        SpyProductOfferQuery $productOfferQuery,
        ProductOfferTableCriteriaTransfer $productOfferTableCriteriaTransfer
    ): SpyProductOfferQuery {
        $productOfferQuery = $this->addIsActiveProductOfferFilter($productOfferQuery, $productOfferTableCriteriaTransfer);
        $productOfferQuery = $this->addStockProductOfferFilter($productOfferQuery, $productOfferTableCriteriaTransfer);
        $productOfferQuery = $this->addStoreProductOfferFilter($productOfferQuery, $productOfferTableCriteriaTransfer);
        $productOfferQuery = $this->addValidityProductOfferFilter($productOfferQuery, $productOfferTableCriteriaTransfer);
        $productOfferQuery = $this->addCreatedAtProductOfferFilter($productOfferQuery, $productOfferTableCriteriaTransfer);
        $productOfferQuery = $this->addUpdatedAtProductOfferFilter($productOfferQuery, $productOfferTableCriteriaTransfer);

        return $productOfferQuery;
    }

    /**
     * @param \Orm\Zed\ProductOffer\Persistence\SpyProductOfferQuery $productOfferQuery
     * @param \Generated\Shared\Transfer\ProductOfferTableCriteriaTransfer $productOfferTableCriteriaTransfer
     *
     * @return \Orm\Zed\ProductOffer\Persistence\SpyProductOfferQuery
     */
    protected function addIsActiveProductOfferFilter(
        SpyProductOfferQuery $productOfferQuery,
        ProductOfferTableCriteriaTransfer $productOfferTableCriteriaTransfer
    ): SpyProductOfferQuery {
        if ($productOfferTableCriteriaTransfer->getFilterIsActive() === null) {
            return $productOfferQuery;
        }

        $productOfferQuery->filterByIsActive(
            $productOfferTableCriteriaTransfer->getFilterIsActive()
        );

        return $productOfferQuery;
    }

    /**
     * @param \Orm\Zed\ProductOffer\Persistence\SpyProductOfferQuery $productOfferQuery
     * @param \Generated\Shared\Transfer\ProductOfferTableCriteriaTransfer $productOfferTableCriteriaTransfer
     *
     * @return \Orm\Zed\ProductOffer\Persistence\SpyProductOfferQuery
     */
    protected function addStockProductOfferFilter(
        SpyProductOfferQuery $productOfferQuery,
        ProductOfferTableCriteriaTransfer $productOfferTableCriteriaTransfer
    ): SpyProductOfferQuery {
        if ($productOfferTableCriteriaTransfer->getFilterHasStock() === null) {
            return $productOfferQuery;
        }

        $productOfferQuery->where(
            sprintf(
                '(%s) %s 0',
                SpyProductOfferStockTableMap::COL_QUANTITY,
                $productOfferTableCriteriaTransfer->getFilterHasStock() ? '>' : '='
            )
        );

        if (!$productOfferTableCriteriaTransfer->getFilterHasStock()) {
            $productOfferQuery->_or()
                ->useProductOfferStockQuery(null, Criteria::LEFT_JOIN)
                    ->filterByIdProductOfferStock(null, Criteria::ISNULL)
                ->endUse();
        }

        return $productOfferQuery;
    }

    /**
     * @param \Orm\Zed\ProductOffer\Persistence\SpyProductOfferQuery $productOfferQuery
     * @param \Generated\Shared\Transfer\ProductOfferTableCriteriaTransfer $productOfferTableCriteriaTransfer
     *
     * @return \Orm\Zed\ProductOffer\Persistence\SpyProductOfferQuery
     */
    protected function addStoreProductOfferFilter(
        SpyProductOfferQuery $productOfferQuery,
        ProductOfferTableCriteriaTransfer $productOfferTableCriteriaTransfer
    ): SpyProductOfferQuery {
        if (!$productOfferTableCriteriaTransfer->getFilterInStores()) {
            return $productOfferQuery;
        }

        $productOfferQuery->distinct()
            ->useSpyProductOfferStoreQuery()
                ->filterByFkStore_In($productOfferTableCriteriaTransfer->getFilterInStores())
            ->endUse();

        return $productOfferQuery;
    }

    /**
     * @param \Orm\Zed\ProductOffer\Persistence\SpyProductOfferQuery $productOfferQuery
     * @param \Generated\Shared\Transfer\ProductOfferTableCriteriaTransfer $productOfferTableCriteriaTransfer
     *
     * @return \Orm\Zed\ProductOffer\Persistence\SpyProductOfferQuery
     */
    protected function addValidityProductOfferFilter(
        SpyProductOfferQuery $productOfferQuery,
        ProductOfferTableCriteriaTransfer $productOfferTableCriteriaTransfer
    ): SpyProductOfferQuery {
        $criteriaRangeFilterTransfer = $productOfferTableCriteriaTransfer->getFilterValidity();

        if (!$criteriaRangeFilterTransfer) {
            return $productOfferQuery;
        }

        if ($criteriaRangeFilterTransfer->getFrom()) {
            $productOfferQuery->useSpyProductOfferValidityQuery()
                ->filterByValidFrom($criteriaRangeFilterTransfer->getFrom(), Criteria::GREATER_EQUAL)
                ->endUse();
        }

        if ($criteriaRangeFilterTransfer->getTo()) {
            $productOfferQuery->useSpyProductOfferValidityQuery()
                ->filterByValidTo($criteriaRangeFilterTransfer->getTo(), Criteria::LESS_THAN)
                ->endUse();
        }

        return $productOfferQuery;
    }

    /**
     * @param \Orm\Zed\ProductOffer\Persistence\SpyProductOfferQuery $productOfferQuery
     * @param \Generated\Shared\Transfer\ProductOfferTableCriteriaTransfer $productOfferTableCriteriaTransfer
     *
     * @return \Orm\Zed\ProductOffer\Persistence\SpyProductOfferQuery
     */
    protected function addCreatedAtProductOfferFilter(
        SpyProductOfferQuery $productOfferQuery,
        ProductOfferTableCriteriaTransfer $productOfferTableCriteriaTransfer
    ): SpyProductOfferQuery {
        $criteriaRangeFilterTransfer = $productOfferTableCriteriaTransfer->getFilterCreatedAt();

        if (!$criteriaRangeFilterTransfer) {
            return $productOfferQuery;
        }

        if ($criteriaRangeFilterTransfer->getFrom()) {
            $productOfferQuery->filterByCreatedAt($criteriaRangeFilterTransfer->getFrom(), Criteria::GREATER_EQUAL);
        }

        if ($criteriaRangeFilterTransfer->getTo()) {
            $productOfferQuery->filterByCreatedAt(
                $criteriaRangeFilterTransfer->getTo(),
                Criteria::LESS_THAN
            );
        }

        return $productOfferQuery;
    }

    /**
     * @param \Orm\Zed\ProductOffer\Persistence\SpyProductOfferQuery $productOfferQuery
     * @param \Generated\Shared\Transfer\ProductOfferTableCriteriaTransfer $productOfferTableCriteriaTransfer
     *
     * @return \Orm\Zed\ProductOffer\Persistence\SpyProductOfferQuery
     */
    protected function addUpdatedAtProductOfferFilter(
        SpyProductOfferQuery $productOfferQuery,
        ProductOfferTableCriteriaTransfer $productOfferTableCriteriaTransfer
    ): SpyProductOfferQuery {
        $criteriaRangeFilterTransfer = $productOfferTableCriteriaTransfer->getFilterUpdatedAt();

        if (!$criteriaRangeFilterTransfer) {
            return $productOfferQuery;
        }

        if ($criteriaRangeFilterTransfer->getFrom()) {
            $productOfferQuery->filterByUpdatedAt($criteriaRangeFilterTransfer->getFrom(), Criteria::GREATER_EQUAL);
        }

        if ($criteriaRangeFilterTransfer->getTo()) {
            $productOfferQuery->filterByUpdatedAt(
                $criteriaRangeFilterTransfer->getTo(),
                Criteria::LESS_THAN
            );
        }

        return $productOfferQuery;
    }

    /**
     * @param \Orm\Zed\ProductOffer\Persistence\SpyProductOfferQuery $productOfferQuery
     * @param \Generated\Shared\Transfer\ProductOfferTableCriteriaTransfer $productOfferTableCriteriaTransfer
     *
     * @return \Orm\Zed\ProductOffer\Persistence\SpyProductOfferQuery
     */
    protected function addProductOfferSorting(
        SpyProductOfferQuery $productOfferQuery,
        ProductOfferTableCriteriaTransfer $productOfferTableCriteriaTransfer
    ): SpyProductOfferQuery {
        $orderColumn = $productOfferTableCriteriaTransfer->getOrderBy() ?? static::COL_KEY_OFFER_REFERENCE;
        $orderDirection = $productOfferTableCriteriaTransfer->getOrderDirection() ?? Criteria::DESC;

        if (!$orderColumn || !$orderDirection) {
            return $productOfferQuery;
        }

        $orderColumn = ProductOfferTableDataMapper::PRODUCT_OFFER_DATA_COLUMN_MAP[$orderColumn] ?? $orderColumn;

        if (
            in_array($orderColumn, [
            SpyProductOfferTableMap::COL_PRODUCT_OFFER_REFERENCE,
            SpyProductOfferTableMap::COL_MERCHANT_SKU,
            SpyProductOfferTableMap::COL_CONCRETE_SKU,
            ], true)
        ) {
            $productOfferQuery = $this->addNaturalSorting($productOfferQuery, $orderColumn, $orderDirection);
        }
        $productOfferQuery->orderBy($orderColumn, $orderDirection);

        return $productOfferQuery;
    }

    /**
     * @param \Propel\Runtime\ActiveQuery\ModelCriteria $query
     * @param string $orderColumn
     * @param string $orderDirection
     *
     * @return \Propel\Runtime\ActiveQuery\ModelCriteria
     */
    protected function addNaturalSorting(
        ModelCriteria $query,
        string $orderColumn,
        string $orderDirection
    ): ModelCriteria {
        if ($orderDirection === Criteria::ASC) {
            $query->addAscendingOrderByColumn("LENGTH($orderColumn)");
        }
        if ($orderDirection === Criteria::DESC) {
            $query->addDescendingOrderByColumn("LENGTH($orderColumn)");
        }

        // DISTINCT query requires it to be in SELECT
        $query->withColumn("LENGTH($orderColumn)");

        return $query;
    }

    /**
     * @module ProductOffer
     * @module ProductOfferStock
     * @module ProductOfferValidity
     *
     * @param int $idMerchant
     *
     * @return \Generated\Shared\Transfer\MerchantProductOfferCountsTransfer
     */
    public function getOffersDashboardCardCounts(int $idMerchant): MerchantProductOfferCountsTransfer
    {
        $productOfferMerchantPortalGuiConfig = $this->getFactory()->getConfig();
        $dashboardExpiringOffersLimit = $productOfferMerchantPortalGuiConfig->getDashboardExpiringOffersDaysThreshold();
        $dashboardLowStockThreshold = $productOfferMerchantPortalGuiConfig->getDashboardLowStockThreshold();
        $currentDateTime = (new DateTime())->format('Y-m-d H:i:s');
        $expiringOffersDateTime = (new DateTime(sprintf('+%s Days', $dashboardExpiringOffersLimit)))->format('Y-m-d H:i:s');

        /** @var array $merchantProductOfferCounts */
        $merchantProductOfferCounts = $this->getFactory()->getProductOfferPropelQuery()
            ->leftJoinSpyProductOfferValidity()
            ->leftJoinProductOfferStock()
            ->filterByFkMerchant($idMerchant)
            ->addAsColumn(MerchantProductOfferCountsTransfer::TOTAL, 'COUNT(*)')
            ->addAsColumn(
                MerchantProductOfferCountsTransfer::ACTIVE,
                'COUNT(CASE WHEN ' . SpyProductOfferTableMap::COL_IS_ACTIVE . ' IS TRUE THEN 1 END)'
            )
            ->addAsColumn(
                MerchantProductOfferCountsTransfer::WITH_STOCK,
                'COUNT(CASE WHEN ' . SpyProductOfferStockTableMap::COL_QUANTITY . ' > 0 THEN 1 END)'
            )
            ->addAsColumn(
                MerchantProductOfferCountsTransfer::LOW_IN_STOCK,
                'COUNT(CASE WHEN ' . SpyProductOfferStockTableMap::COL_QUANTITY . ' < ' . $dashboardLowStockThreshold . ' THEN 1 END)'
            )
            ->addAsColumn(
                MerchantProductOfferCountsTransfer::WITH_VALID_DATES,
                'COUNT(CASE WHEN (' .
                    SpyProductOfferValidityTableMap::COL_VALID_FROM . " <= '" . $currentDateTime .
                        "' OR " . SpyProductOfferValidityTableMap::COL_VALID_FROM . ' IS NULL)' .
                    ' AND (' . SpyProductOfferValidityTableMap::COL_VALID_TO . " >= '" . $currentDateTime .
                        "' OR " . SpyProductOfferValidityTableMap::COL_VALID_TO . ' IS NULL)' .
                    ' OR ' . SpyProductOfferValidityTableMap::COL_ID_PRODUCT_OFFER_VALIDITY . ' IS NULL THEN 1 END)'
            )
            ->addAsColumn(
                MerchantProductOfferCountsTransfer::EXPIRING,
                "COUNT(CASE WHEN '" . $currentDateTime . "' < " . SpyProductOfferValidityTableMap::COL_VALID_TO
                . " AND '" . $expiringOffersDateTime . "' > " . SpyProductOfferValidityTableMap::COL_VALID_TO . ' THEN 1 END)'
            )
            ->addAsColumn(
                MerchantProductOfferCountsTransfer::VISIBLE,
                'COUNT(CASE WHEN ' . SpyProductOfferTableMap::COL_IS_ACTIVE . " IS TRUE
                    AND " . SpyProductOfferStockTableMap::COL_QUANTITY . ' > 0 AND ((' .
                        SpyProductOfferValidityTableMap::COL_VALID_FROM . " <= '" . $currentDateTime .
                            "' OR " . SpyProductOfferValidityTableMap::COL_VALID_FROM . ' IS NULL)' .
                        ' AND (' . SpyProductOfferValidityTableMap::COL_VALID_TO . " >= '" . $currentDateTime .
                            "' OR " . SpyProductOfferValidityTableMap::COL_VALID_TO . ' IS NULL)' .
                        ' OR ' . SpyProductOfferValidityTableMap::COL_ID_PRODUCT_OFFER_VALIDITY . " IS NULL
                    ) THEN 1 END)"
            )
            ->select([
                MerchantProductOfferCountsTransfer::TOTAL,
                MerchantProductOfferCountsTransfer::ACTIVE,
                MerchantProductOfferCountsTransfer::WITH_STOCK,
                MerchantProductOfferCountsTransfer::LOW_IN_STOCK,
                MerchantProductOfferCountsTransfer::WITH_VALID_DATES,
                MerchantProductOfferCountsTransfer::EXPIRING,
                MerchantProductOfferCountsTransfer::VISIBLE,
            ])
            ->findOne();

        $merchantProductOfferCountsTransfer = (new MerchantProductOfferCountsTransfer())->fromArray($merchantProductOfferCounts, true);
        $inactiveCount = $merchantProductOfferCountsTransfer->getTotal() - $merchantProductOfferCountsTransfer->getActive();
        $merchantProductOfferCountsTransfer->setInactive($inactiveCount > 0 ? $inactiveCount : 0);

        return $merchantProductOfferCountsTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductOfferTableCriteriaTransfer $productOfferPriceTableCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\PriceProductOfferTableViewCollectionTransfer
     */
    public function getProductOfferPriceTableData(
        PriceProductOfferTableCriteriaTransfer $productOfferPriceTableCriteriaTransfer
    ): PriceProductOfferTableViewCollectionTransfer {
        $productOfferPriceTableCriteriaTransfer->requireIdProductOffer();
        $productOfferPriceTableCriteriaTransfer->requireIdMerchant();

        $priceProductStoreQuery = $this->getFactory()
            ->getPriceProductStorePropelQuery()
            ->select([SpyStoreTableMap::COL_NAME, SpyCurrencyTableMap::COL_CODE])
            ->addAsColumn(PriceProductOfferTableViewTransfer::STORE, SpyStoreTableMap::COL_NAME)
            ->addAsColumn(PriceProductOfferTableViewTransfer::CURRENCY, SpyCurrencyTableMap::COL_CODE);

        $priceTypeValues = $this->getFactory()->getPriceProductFacade()->getPriceTypeValues();

        foreach ($priceTypeValues as $priceTypeTransfer) {
            if (!$priceTypeTransfer->getIdPriceType()) {
                continue;
            }

            $priceTypeName = mb_strtolower($priceTypeTransfer->getName());
            $grossColumnName = $priceTypeName . static::SUFFIX_PRICE_TYPE_GROSS;
            $grossClause = sprintf(
                'MAX(CASE WHEN %s = %s THEN %s END)',
                SpyPriceProductTableMap::COL_FK_PRICE_TYPE,
                $priceTypeTransfer->getIdPriceType(),
                SpyPriceProductStoreTableMap::COL_GROSS_PRICE
            );

            $netColumnName = $priceTypeName . static::SUFFIX_PRICE_TYPE_NET;
            $netClause = sprintf(
                'MAX(CASE WHEN %s = %s THEN %s END)',
                SpyPriceProductTableMap::COL_FK_PRICE_TYPE,
                $priceTypeTransfer->getIdPriceType(),
                SpyPriceProductStoreTableMap::COL_NET_PRICE
            );

            $priceProductStoreQuery->addAsColumn($grossColumnName, $grossClause)
                ->addAsColumn($netColumnName, $netClause);
        }

        $priceProductStoreQuery->addAsColumn(
            static::COL_PRICE_PRODUCT_OFFER_IDS,
            sprintf('GROUP_CONCAT(%s)', SpyPriceProductOfferTableMap::COL_ID_PRICE_PRODUCT_OFFER)
        )->addAsColumn(
            static::COL_TYPE_PRICE_PRODUCT_OFFER_IDS,
            sprintf(
                'GROUP_CONCAT(CONCAT(%s,\':\',%s))',
                SpyPriceTypeTableMap::COL_NAME,
                SpyPriceProductOfferTableMap::COL_ID_PRICE_PRODUCT_OFFER
            )
        );

        $priceProductStoreQuery = $this->applyPriceProductOfferFilters(
            $priceProductStoreQuery,
            $productOfferPriceTableCriteriaTransfer
        );

        $priceProductStoreQuery->usePriceProductQuery()
                ->innerJoinPriceType()
            ->endUse()
            ->groupBy(SpyStoreTableMap::COL_NAME)
            ->groupBy(SpyCurrencyTableMap::COL_CODE);

        if ($productOfferPriceTableCriteriaTransfer->getOrderBy()) {
            $priceProductStoreQuery->orderBy($productOfferPriceTableCriteriaTransfer->getOrderBy(), $productOfferPriceTableCriteriaTransfer->getOrderDirection());
        }

        $paginate = $priceProductStoreQuery->paginate(
            $productOfferPriceTableCriteriaTransfer->requirePage()->getPage(),
            $productOfferPriceTableCriteriaTransfer->requirePageSize()->getPageSize()
        );

        $paginationTransfer = $this->hydratePaginationTransfer($paginate);

        $priceProductOfferTableViewCollectionTransfer = $this->getFactory()
            ->createPriceProductOfferTableDataMapper()
            ->mapPriceProductOfferTableDataArrayToPriceProductOfferTableViewCollectionTransfer(
                $paginate->getResults()->getData(),
                new PriceProductOfferTableViewCollectionTransfer()
            );

        $priceProductOfferTableViewCollectionTransfer->setPagination($paginationTransfer);

        return $priceProductOfferTableViewCollectionTransfer;
    }

    /**
     * @param \Orm\Zed\PriceProduct\Persistence\SpyPriceProductStoreQuery $priceProductStoreQuery
     * @param \Generated\Shared\Transfer\PriceProductOfferTableCriteriaTransfer $productOfferPriceTableCriteriaTransfer
     *
     * @return \Orm\Zed\PriceProduct\Persistence\SpyPriceProductStoreQuery
     */
    protected function applyPriceProductOfferFilters(
        SpyPriceProductStoreQuery $priceProductStoreQuery,
        PriceProductOfferTableCriteriaTransfer $productOfferPriceTableCriteriaTransfer
    ): SpyPriceProductStoreQuery {
        $priceProductStoreQuery
            ->useSpyPriceProductOfferQuery()
                ->useSpyProductOfferQuery()
                    ->filterByFkMerchant($productOfferPriceTableCriteriaTransfer->getIdMerchant())
                    ->filterByIdProductOffer($productOfferPriceTableCriteriaTransfer->getIdProductOffer())
                ->endUse()
            ->endUse();

        if (count($productOfferPriceTableCriteriaTransfer->getFilterInStores()) > 0) {
            $priceProductStoreQuery
                ->useStoreQuery(SpyStoreTableMap::TABLE_NAME, Criteria::INNER_JOIN)
                    ->filterByIdStore_In($productOfferPriceTableCriteriaTransfer->getFilterInStores())
                ->endUse();
        } else {
            $priceProductStoreQuery->innerJoinStore();
        }

        if (count($productOfferPriceTableCriteriaTransfer->getFilterInCurrencies()) > 0) {
            $priceProductStoreQuery
                ->useCurrencyQuery()
                    ->filterByIdCurrency_In($productOfferPriceTableCriteriaTransfer->getFilterInCurrencies())
                ->endUse();
        } else {
            $priceProductStoreQuery->innerJoinCurrency();
        }

        return $priceProductStoreQuery;
    }
}
