<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferMerchantPortalGui\Communication;

use Generated\Shared\Transfer\ProductOfferTransfer;
use Spryker\Shared\GuiTable\DataProvider\GuiTableDataProviderInterface;
use Spryker\Shared\GuiTable\GuiTableFactoryInterface;
use Spryker\Shared\GuiTable\Http\GuiTableDataRequestExecutorInterface;
use Spryker\Shared\ZedUi\ZedUiFactoryInterface;
use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Builder\ProductNameBuilder;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Builder\ProductNameBuilderInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\ConfigurationProvider\GuiTableConfigurationProviderInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\ConfigurationProvider\ProductGuiTableConfigurationProvider;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\ConfigurationProvider\ProductOfferGuiTableConfigurationProvider;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\DataProvider\OffersDashboardCardProvider;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\DataProvider\OffersDashboardCardProviderInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\DataProvider\PriceProductDataProvider;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\DataProvider\PriceProductDataProviderInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\DataProvider\PriceProductOfferDataProvider;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\DataProvider\PriceProductOfferDataProviderInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\DataProvider\ProductGuiTableDataProvider;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\DataProvider\ProductOfferGuiTableDataProvider;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Deleter\PriceDeleter;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Deleter\PriceDeleterInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Expander\MerchantOrderItemTableExpander;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Expander\MerchantOrderItemTableExpanderInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Expander\PriceProductsVolumeDataExpander;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Expander\PriceProductsVolumeDataExpanderInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Filter\PriceProductFilter;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Filter\PriceProductFilterInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Form\Constraint\ValidProductOfferPriceIdsOwnByMerchantConstraint;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Form\DataProvider\ProductOfferCreateFormDataProvider;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Form\DataProvider\ProductOfferCreateFormDataProviderInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Form\DataProvider\ProductOfferUpdateFormDataProvider;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Form\DataProvider\ProductOfferUpdateFormDataProviderInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Form\ProductOfferForm;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Form\Transformer\Merger\MergeStrategy\PriceProductMatchingExistingVolumePriceMergeStrategy;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Form\Transformer\Merger\MergeStrategy\PriceProductMergeStrategyInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Form\Transformer\Merger\MergeStrategy\VolumePriceMatchingExistingPriceProductMergeStrategy;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Form\Transformer\Merger\MergeStrategy\VolumePriceNotMatchingExistingPriceProductMergeStrategy;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Form\Transformer\Merger\PriceProductsMerger;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Form\Transformer\Merger\PriceProductsMergerInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Form\Transformer\PriceProductOfferTransformer;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Form\Transformer\ProductOfferStockTransformer;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Form\Transformer\QuantityTransformer;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Form\Transformer\StoresTransformer;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\GuiTable\Column\ColumnIdCreator;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\GuiTable\Column\ColumnIdCreatorInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\GuiTable\ConfigurationProvider\PriceProductOfferCreateGuiTableConfigurationProvider;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\GuiTable\ConfigurationProvider\PriceProductOfferCreateGuiTableConfigurationProviderInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\GuiTable\ConfigurationProvider\PriceProductOfferUpdateGuiTableConfigurationProvider;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\GuiTable\ConfigurationProvider\PriceProductOfferUpdateGuiTableConfigurationProviderInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\GuiTable\DataProvider\ProductOfferPriceGuiTableDataProvider;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Mapper\PriceProductOfferMapper;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Mapper\PriceProductOfferTableDataMapper;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Mapper\PriceProductOfferTableDataMapperInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Reader\PriceProductReader;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Reader\PriceProductReaderInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Sorter\ComparisonStrategy\PriceProductOfferTableView\PriceProductOfferTableViewComparisonStrategyInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Sorter\ComparisonStrategy\PriceProductOfferTableView\PriceProductOfferTableViewPriceComparisonStrategy;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Sorter\ComparisonStrategy\PriceProductOfferTableView\PriceProductOfferTableViewSimpleGetterComparisonStrategy;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Sorter\PriceProductOfferTableViewSorter;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Sorter\PriceProductOfferTableViewSorterInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Translator\ValidationResponseTranslator;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Translator\ValidationResponseTranslatorInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Validator\PriceProductOffer\Constraint\VolumePriceHasBasePriceProductConstraint;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Validator\PriceProductOffer\PriceProductOfferCollectionConstraintProvider;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Validator\PriceProductOffer\PriceProductOfferConstraintProviderInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Validator\PriceProductOffer\PriceProductOfferValidator;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Validator\PriceProductOffer\PriceProductOfferValidatorInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Validator\PriceProductOffer\PropertyPath\PriceProductOfferPropertyPathAnalyzer;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Validator\PriceProductOffer\PropertyPath\PriceProductOfferPropertyPathAnalyzerInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\External\ProductOfferMerchantPortalGuiToValidationAdapterInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade\ProductOfferMerchantPortalGuiToCurrencyFacadeInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade\ProductOfferMerchantPortalGuiToLocaleFacadeInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade\ProductOfferMerchantPortalGuiToMerchantStockFacadeInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade\ProductOfferMerchantPortalGuiToMerchantUserFacadeInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade\ProductOfferMerchantPortalGuiToMoneyFacadeInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade\ProductOfferMerchantPortalGuiToPriceProductFacadeInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade\ProductOfferMerchantPortalGuiToPriceProductOfferFacadeInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade\ProductOfferMerchantPortalGuiToPriceProductOfferVolumeFacadeInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade\ProductOfferMerchantPortalGuiToProductFacadeInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade\ProductOfferMerchantPortalGuiToProductOfferFacadeInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade\ProductOfferMerchantPortalGuiToRouterFacadeInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade\ProductOfferMerchantPortalGuiToStoreFacadeInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade\ProductOfferMerchantPortalGuiToTranslatorFacadeInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Service\ProductOfferMerchantPortalGuiToPriceProductVolumeServiceInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Service\ProductOfferMerchantPortalGuiToUtilEncodingServiceInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\ProductOfferMerchantPortalGuiDependencyProvider;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;
use Twig\Environment;

/**
 * @method \Spryker\Zed\ProductOfferMerchantPortalGui\Persistence\ProductOfferMerchantPortalGuiRepositoryInterface getRepository()
 * @method \Spryker\Zed\ProductOfferMerchantPortalGui\ProductOfferMerchantPortalGuiConfig getConfig()
 */
class ProductOfferMerchantPortalGuiCommunicationFactory extends AbstractCommunicationFactory
{
    public function createProductGuiTableConfigurationProvider(): GuiTableConfigurationProviderInterface
    {
        return new ProductGuiTableConfigurationProvider(
            $this->getTranslatorFacade(),
            $this->getGuiTableFactory(),
            $this->getProductTableExpanderPlugins(),
        );
    }

    public function createProductOfferGuiTableConfigurationProvider(): GuiTableConfigurationProviderInterface
    {
        return new ProductOfferGuiTableConfigurationProvider(
            $this->getStoreFacade(),
            $this->getTranslatorFacade(),
            $this->getGuiTableFactory(),
        );
    }

    public function createPriceProductOfferUpdateGuiTableConfigurationProvider(): PriceProductOfferUpdateGuiTableConfigurationProviderInterface
    {
        return new PriceProductOfferUpdateGuiTableConfigurationProvider(
            $this->getGuiTableFactory(),
            $this->getPriceProductFacade(),
            $this->getStoreFacade(),
            $this->getCurrencyFacade(),
            $this->createColumnIdCreator(),
        );
    }

    public function createPriceProductOfferCreateGuiTableConfigurationProvider(): PriceProductOfferCreateGuiTableConfigurationProviderInterface
    {
        return new PriceProductOfferCreateGuiTableConfigurationProvider(
            $this->getGuiTableFactory(),
            $this->getPriceProductFacade(),
            $this->getStoreFacade(),
            $this->getCurrencyFacade(),
            $this->createColumnIdCreator(),
        );
    }

    public function createProductTableDataProvider(): GuiTableDataProviderInterface
    {
        return new ProductGuiTableDataProvider(
            $this->getRepository(),
            $this->getTranslatorFacade(),
            $this->createProductNameBuilder(),
            $this->getMerchantUserFacade(),
            $this->getLocaleFacade(),
            $this->getProductTableExpanderPlugins(),
        );
    }

    public function createProductOfferTableDataProvider(): GuiTableDataProviderInterface
    {
        return new ProductOfferGuiTableDataProvider(
            $this->getRepository(),
            $this->getTranslatorFacade(),
            $this->createProductNameBuilder(),
            $this->getMerchantUserFacade(),
            $this->getLocaleFacade(),
        );
    }

    public function createProductOfferPriceTableDataProvider(?int $idProductOffer = null): GuiTableDataProviderInterface
    {
        return new ProductOfferPriceGuiTableDataProvider(
            $this->getMerchantUserFacade(),
            $this->getMoneyFacade(),
            $this->createPriceProductOfferTableDataMapper(),
            $this->createPriceProductReader(),
            $this->createPriceProductOfferTableViewSorter(),
            $idProductOffer,
        );
    }

    public function createProductNameBuilder(): ProductNameBuilderInterface
    {
        return new ProductNameBuilder();
    }

    /**
     * @param \Generated\Shared\Transfer\ProductOfferTransfer|null $data
     * @param array<mixed> $options
     *
     * @return \Symfony\Component\Form\FormInterface<mixed>
     */
    public function createProductOfferForm(?ProductOfferTransfer $data = null, array $options = []): FormInterface
    {
        return $this->getFormFactory()->create(ProductOfferForm::class, $data, $options);
    }

    public function createProductOfferCreateFormDataProvider(): ProductOfferCreateFormDataProviderInterface
    {
        return new ProductOfferCreateFormDataProvider(
            $this->getProductFacade(),
            $this->getMerchantUserFacade(),
            $this->getMerchantStockFacade(),
        );
    }

    public function createProductOfferUpdateFormDataProvider(): ProductOfferUpdateFormDataProviderInterface
    {
        return new ProductOfferUpdateFormDataProvider(
            $this->getProductFacade(),
            $this->getProductOfferFacade(),
            $this->getMerchantStockFacade(),
            $this->getMerchantUserFacade(),
        );
    }

    /**
     * @return \Symfony\Component\Form\DataTransformerInterface<\ArrayObject<int, \Generated\Shared\Transfer\StoreTransfer>|null, array<int>|null>
     */
    public function createStoresTransformer(): DataTransformerInterface
    {
        return new StoresTransformer();
    }

    /**
     * @return \Symfony\Component\Form\DataTransformerInterface<\Spryker\DecimalObject\Decimal|null, float|null>
     */
    public function createQuantityTransformer(): DataTransformerInterface
    {
        return new QuantityTransformer();
    }

    /**
     * @return \Symfony\Component\Form\DataTransformerInterface<\ArrayObject<int, \Generated\Shared\Transfer\ProductOfferStockTransfer>, \Generated\Shared\Transfer\ProductOfferStockTransfer>
     */
    public function createProductOfferStockTransformer(): DataTransformerInterface
    {
        return new ProductOfferStockTransformer();
    }

    /**
     * @param int|null $idProductOffer
     *
     * @return \Symfony\Component\Form\DataTransformerInterface<\ArrayObject<int, \Generated\Shared\Transfer\PriceProductTransfer>, string>
     */
    public function createPriceProductOfferTransformer(?int $idProductOffer = null): DataTransformerInterface
    {
        return new PriceProductOfferTransformer(
            $this->getUtilEncodingService(),
            $this->getPriceProductFacade(),
            $this->getCurrencyFacade(),
            $this->getMoneyFacade(),
            $this->createPriceProductToPriceProductOfferMerger(),
            $this->createColumnIdCreator(),
            $this->createPriceProductOfferDataProvider(),
            $idProductOffer,
        );
    }

    public function createOffersDashboardCardProvider(): OffersDashboardCardProviderInterface
    {
        return new OffersDashboardCardProvider(
            $this->getRepository(),
            $this->getMerchantUserFacade(),
            $this->getRouterFacade(),
            $this->getConfig(),
            $this->getTwigEnvironment(),
        );
    }

    public function createPriceProductOfferMapper(): PriceProductOfferMapper
    {
        return new PriceProductOfferMapper(
            $this->getPriceProductFacade(),
            $this->getMoneyFacade(),
            $this->getPriceProductOfferVolumeFacade(),
            $this->getPriceProductVolumeService(),
            $this->createPriceProductOfferPropertyPathAnalyzer(),
            $this->createColumnIdCreator(),
        );
    }

    public function createMerchantOrderItemTableExpander(): MerchantOrderItemTableExpanderInterface
    {
        return new MerchantOrderItemTableExpander($this->getProductOfferFacade());
    }

    public function createPriceProductOfferTableDataMapper(): PriceProductOfferTableDataMapperInterface
    {
        return new PriceProductOfferTableDataMapper(
            $this->getPriceProductFacade(),
            $this->getStoreFacade(),
            $this->createColumnIdCreator(),
        );
    }

    public function createPriceProductReader(): PriceProductReaderInterface
    {
        return new PriceProductReader(
            $this->getPriceProductOfferFacade(),
            $this->createPriceProductFilter(),
        );
    }

    public function createPriceProductFilter(): PriceProductFilterInterface
    {
        return new PriceProductFilter();
    }

    public function createPriceProductOfferTableViewSorter(): PriceProductOfferTableViewSorterInterface
    {
        return new PriceProductOfferTableViewSorter(
            $this->createPriceProductOfferTableViewSimpleGetterComparisonStrategy(),
            $this->createPriceProductOfferTableViewComparisonStrategies(),
        );
    }

    /**
     * @return array<\Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Sorter\ComparisonStrategy\PriceProductOfferTableView\PriceProductOfferTableViewComparisonStrategyInterface>
     */
    public function createPriceProductOfferTableViewComparisonStrategies(): array
    {
        return [
            $this->createPriceProductOfferTableViewPriceComparisonStrategy(),
        ];
    }

    public function createPriceProductOfferTableViewSimpleGetterComparisonStrategy(): PriceProductOfferTableViewComparisonStrategyInterface
    {
        return new PriceProductOfferTableViewSimpleGetterComparisonStrategy();
    }

    public function createPriceProductOfferTableViewPriceComparisonStrategy(): PriceProductOfferTableViewComparisonStrategyInterface
    {
        return new PriceProductOfferTableViewPriceComparisonStrategy(
            $this->createColumnIdCreator(),
        );
    }

    public function createPriceProductOfferValidator(): PriceProductOfferValidatorInterface
    {
        return new PriceProductOfferValidator(
            $this->getValidationAdapter(),
            $this->createPriceProductOfferCollectionConstraintProvider(),
            $this->getPriceProductOfferFacade(),
        );
    }

    public function createPriceProductOfferCollectionConstraintProvider(): PriceProductOfferConstraintProviderInterface
    {
        return new PriceProductOfferCollectionConstraintProvider(
            $this->createPriceProductOfferCollectionConstraints(),
        );
    }

    /**
     * @return array<\Symfony\Component\Validator\Constraint>
     */
    public function createPriceProductOfferCollectionConstraints(): array
    {
        return [
            $this->createVolumePriceHasBasePriceProductConstraint(),
        ];
    }

    public function createVolumePriceHasBasePriceProductConstraint(): SymfonyConstraint
    {
        return new VolumePriceHasBasePriceProductConstraint();
    }

    public function createPriceProductToPriceProductOfferMerger(): PriceProductsMergerInterface
    {
        return new PriceProductsMerger(
            $this->createPriceProductMergeStrategies(),
        );
    }

    /**
     * @return array<\Spryker\Zed\ProductOfferMerchantPortalGui\Communication\Form\Transformer\Merger\MergeStrategy\PriceProductMergeStrategyInterface>
     */
    public function createPriceProductMergeStrategies(): array
    {
        return [
            $this->createPriceProductMatchingExistingVolumePriceMergeStrategy(),
            $this->createVolumePriceMatchingExistingPriceProductMergeStrategy(),
            $this->createVolumePriceNotMatchingExistingPriceProductMergeStrategy(),
        ];
    }

    public function createVolumePriceNotMatchingExistingPriceProductMergeStrategy(): PriceProductMergeStrategyInterface
    {
        return new VolumePriceNotMatchingExistingPriceProductMergeStrategy(
            $this->getPriceProductVolumeService(),
        );
    }

    public function createPriceProductMatchingExistingVolumePriceMergeStrategy(): PriceProductMergeStrategyInterface
    {
        return new PriceProductMatchingExistingVolumePriceMergeStrategy(
            $this->getPriceProductVolumeService(),
        );
    }

    public function createVolumePriceMatchingExistingPriceProductMergeStrategy(): PriceProductMergeStrategyInterface
    {
        return new VolumePriceMatchingExistingPriceProductMergeStrategy(
            $this->getPriceProductVolumeService(),
        );
    }

    public function createColumnIdCreator(): ColumnIdCreatorInterface
    {
        return new ColumnIdCreator();
    }

    public function createPriceProductOfferPropertyPathAnalyzer(): PriceProductOfferPropertyPathAnalyzerInterface
    {
        return new PriceProductOfferPropertyPathAnalyzer(
            $this->createColumnIdCreator(),
        );
    }

    public function createPriceProductsVolumeDataExpander(): PriceProductsVolumeDataExpanderInterface
    {
        return new PriceProductsVolumeDataExpander(
            $this->getPriceProductVolumeService(),
            $this->createPriceProductOfferMapper(),
            $this->getPriceProductOfferVolumeFacade(),
            $this->createPriceProductFilter(),
            $this->createPriceProductOfferDataProvider(),
        );
    }

    public function createPriceProductDataProvider(): PriceProductDataProviderInterface
    {
        return new PriceProductDataProvider(
            $this->getPriceProductFacade(),
            $this->getPriceProductOfferFacade(),
            $this->createPriceProductOfferMapper(),
            $this->createPriceProductsVolumeDataExpander(),
        );
    }

    public function createPriceDeleter(): PriceDeleterInterface
    {
        return new PriceDeleter(
            $this->getPriceProductOfferFacade(),
            $this->getPriceProductVolumeService(),
            $this->createPriceProductOfferValidator(),
        );
    }

    public function createPriceProductOfferDataProvider(): PriceProductOfferDataProviderInterface
    {
        return new PriceProductOfferDataProvider(
            $this->getMerchantUserFacade(),
            $this->getProductOfferFacade(),
            $this->createPriceProductFilter(),
        );
    }

    public function createValidationResponseTranslator(): ValidationResponseTranslatorInterface
    {
        return new ValidationResponseTranslator($this->getTranslatorFacade());
    }

    public function getLocaleFacade(): ProductOfferMerchantPortalGuiToLocaleFacadeInterface
    {
        return $this->getProvidedDependency(ProductOfferMerchantPortalGuiDependencyProvider::FACADE_LOCALE);
    }

    public function getUtilEncodingService(): ProductOfferMerchantPortalGuiToUtilEncodingServiceInterface
    {
        return $this->getProvidedDependency(ProductOfferMerchantPortalGuiDependencyProvider::SERVICE_UTIL_ENCODING);
    }

    public function getMerchantUserFacade(): ProductOfferMerchantPortalGuiToMerchantUserFacadeInterface
    {
        return $this->getProvidedDependency(ProductOfferMerchantPortalGuiDependencyProvider::FACADE_MERCHANT_USER);
    }

    public function getTranslatorFacade(): ProductOfferMerchantPortalGuiToTranslatorFacadeInterface
    {
        return $this->getProvidedDependency(ProductOfferMerchantPortalGuiDependencyProvider::FACADE_TRANSLATOR);
    }

    public function getStoreFacade(): ProductOfferMerchantPortalGuiToStoreFacadeInterface
    {
        return $this->getProvidedDependency(ProductOfferMerchantPortalGuiDependencyProvider::FACADE_STORE);
    }

    public function getRouterFacade(): ProductOfferMerchantPortalGuiToRouterFacadeInterface
    {
        return $this->getProvidedDependency(ProductOfferMerchantPortalGuiDependencyProvider::FACADE_ROUTER);
    }

    protected function getTwigEnvironment(): Environment
    {
        return $this->getProvidedDependency(ProductOfferMerchantPortalGuiDependencyProvider::SERVICE_TWIG);
    }

    public function getGuiTableHttpDataRequestExecutor(): GuiTableDataRequestExecutorInterface
    {
        return $this->getProvidedDependency(ProductOfferMerchantPortalGuiDependencyProvider::SERVICE_GUI_TABLE_HTTP_DATA_REQUEST_EXECUTOR);
    }

    public function getGuiTableFactory(): GuiTableFactoryInterface
    {
        return $this->getProvidedDependency(ProductOfferMerchantPortalGuiDependencyProvider::SERVICE_GUI_TABLE_FACTORY);
    }

    public function getZedUiFactory(): ZedUiFactoryInterface
    {
        return $this->getProvidedDependency(ProductOfferMerchantPortalGuiDependencyProvider::SERVICE_ZED_UI_FACTORY);
    }

    public function getProductFacade(): ProductOfferMerchantPortalGuiToProductFacadeInterface
    {
        return $this->getProvidedDependency(ProductOfferMerchantPortalGuiDependencyProvider::FACADE_PRODUCT);
    }

    public function getProductOfferFacade(): ProductOfferMerchantPortalGuiToProductOfferFacadeInterface
    {
        return $this->getProvidedDependency(ProductOfferMerchantPortalGuiDependencyProvider::FACADE_PRODUCT_OFFER);
    }

    public function getMerchantStockFacade(): ProductOfferMerchantPortalGuiToMerchantStockFacadeInterface
    {
        return $this->getProvidedDependency(ProductOfferMerchantPortalGuiDependencyProvider::FACADE_MERCHANT_STOCK);
    }

    public function getCurrencyFacade(): ProductOfferMerchantPortalGuiToCurrencyFacadeInterface
    {
        return $this->getProvidedDependency(ProductOfferMerchantPortalGuiDependencyProvider::FACADE_CURRENCY);
    }

    public function getPriceProductFacade(): ProductOfferMerchantPortalGuiToPriceProductFacadeInterface
    {
        return $this->getProvidedDependency(ProductOfferMerchantPortalGuiDependencyProvider::FACADE_PRICE_PRODUCT);
    }

    public function getValidationAdapter(): ProductOfferMerchantPortalGuiToValidationAdapterInterface
    {
        return $this->getProvidedDependency(ProductOfferMerchantPortalGuiDependencyProvider::EXTERNAL_ADAPTER_VALIDATION);
    }

    public function getPriceProductOfferFacade(): ProductOfferMerchantPortalGuiToPriceProductOfferFacadeInterface
    {
        return $this->getProvidedDependency(ProductOfferMerchantPortalGuiDependencyProvider::FACADE_PRICE_PRODUCT_OFFER);
    }

    public function getPriceProductOfferVolumeFacade(): ProductOfferMerchantPortalGuiToPriceProductOfferVolumeFacadeInterface
    {
        return $this->getProvidedDependency(ProductOfferMerchantPortalGuiDependencyProvider::FACADE_PRICE_PRODUCT_OFFER_VOLUME);
    }

    public function getPriceProductVolumeService(): ProductOfferMerchantPortalGuiToPriceProductVolumeServiceInterface
    {
        return $this->getProvidedDependency(ProductOfferMerchantPortalGuiDependencyProvider::SERVICE_PRICE_PRODUCT_VOLUME);
    }

    public function createValidProductOfferPriceIdsOwnByMerchantConstraint(): SymfonyConstraint
    {
        return new ValidProductOfferPriceIdsOwnByMerchantConstraint();
    }

    public function getMoneyFacade(): ProductOfferMerchantPortalGuiToMoneyFacadeInterface
    {
        return $this->getProvidedDependency(ProductOfferMerchantPortalGuiDependencyProvider::FACADE_MONEY);
    }

    /**
     * @return array<\Spryker\Zed\ProductOfferMerchantPortalGuiExtension\Dependency\Plugin\ProductTableExpanderPluginInterface>
     */
    public function getProductTableExpanderPlugins(): array
    {
        return $this->getProvidedDependency(ProductOfferMerchantPortalGuiDependencyProvider::PLUGINS_PRODUCT_TABLE_EXPANDER);
    }

    /**
     * @return list<\Spryker\Zed\ProductOfferMerchantPortalGuiExtension\Dependency\Plugin\ProductOfferFormExpanderPluginInterface>
     */
    public function getProductOfferFormExpanderPlugins(): array
    {
        return $this->getProvidedDependency(ProductOfferMerchantPortalGuiDependencyProvider::PLUGINS_PRODUCT_OFFER_FORM_EXPANDER);
    }

    /**
     * @return list<\Spryker\Zed\ProductOfferMerchantPortalGuiExtension\Dependency\Plugin\ProductOfferFormViewExpanderPluginInterface>
     */
    public function getProductOfferFormViewExpanderPlugins(): array
    {
        return $this->getProvidedDependency(ProductOfferMerchantPortalGuiDependencyProvider::PLUGINS_PRODUCT_OFFER_FORM_VIEW_EXPANDER);
    }
}
