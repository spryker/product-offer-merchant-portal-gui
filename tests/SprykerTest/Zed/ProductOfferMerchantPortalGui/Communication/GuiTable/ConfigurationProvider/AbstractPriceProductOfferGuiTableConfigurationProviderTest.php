<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\ProductOfferMerchantPortalGui\Communication\GuiTable\ConfigurationProvider;

use Codeception\Stub;
use Codeception\Test\Unit;
use Generated\Shared\DataBuilder\MerchantUserBuilder;
use Generated\Shared\DataBuilder\StoreRelationBuilder;
use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\MerchantTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Generated\Shared\Transfer\StoreWithCurrencyTransfer;
use Spryker\Shared\GuiTable\GuiTableFactoryInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\GuiTable\Column\ColumnIdCreatorInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Communication\GuiTable\ConfigurationProvider\AbstractPriceProductOfferGuiTableConfigurationProvider;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade\ProductOfferMerchantPortalGuiToCurrencyFacadeInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade\ProductOfferMerchantPortalGuiToMerchantUserFacadeInterface;
use Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade\ProductOfferMerchantPortalGuiToPriceProductFacadeInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group ProductOfferMerchantPortalGui
 * @group Communication
 * @group GuiTable
 * @group ConfigurationProvider
 * @group AbstractPriceProductOfferGuiTableConfigurationProviderTest
 * Add your own group annotations below this line
 */
class AbstractPriceProductOfferGuiTableConfigurationProviderTest extends Unit
{
    /**
     * @var string
     */
    protected const STORE_NAME_ASSIGNED = 'DE';

    /**
     * @var string
     */
    protected const STORE_NAME_NOT_ASSIGNED = 'AT';

    public function testGetStoreOptionsShouldReturnOnlyStoresAssignedToCurrentMerchant(): void
    {
        // Arrange
        $assignedStoreTransfer = (new StoreTransfer())->setIdStore(1)->setName(static::STORE_NAME_ASSIGNED);

        $storeRelationTransfer = (new StoreRelationBuilder())
            ->withStores($assignedStoreTransfer->toArray())
            ->build();

        $merchantUserTransfer = (new MerchantUserBuilder())
            ->withMerchant([
                MerchantTransfer::ID_MERCHANT => 1,
                MerchantTransfer::STORE_RELATION => $storeRelationTransfer,
            ])
            ->build();

        $merchantUserFacadeMock = Stub::makeEmpty(
            ProductOfferMerchantPortalGuiToMerchantUserFacadeInterface::class,
            [
                'getCurrentMerchantUser' => function () use ($merchantUserTransfer) {
                    return $merchantUserTransfer;
                },
            ],
        );

        $priceProductOfferGuiTableConfigurationProvider = $this->createPriceProductOfferGuiTableConfigurationProvider($merchantUserFacadeMock);

        // Act
        $storeOptions = $priceProductOfferGuiTableConfigurationProvider->getStoreOptionsForTest();

        // Assert
        $this->assertCount(1, $storeOptions);
        $this->assertArrayHasKey((string)$assignedStoreTransfer->getIdStoreOrFail(), $storeOptions);
        $this->assertSame(static::STORE_NAME_ASSIGNED, $storeOptions[(string)$assignedStoreTransfer->getIdStoreOrFail()]);
    }

    public function testGetCurrencyOptionsShouldReturnOnlyCurrenciesOfStoresAssignedToCurrentMerchant(): void
    {
        // Arrange
        $assignedStoreTransfer = (new StoreTransfer())->setIdStore(1)->setName(static::STORE_NAME_ASSIGNED);
        $notAssignedStoreTransfer = (new StoreTransfer())->setIdStore(2)->setName(static::STORE_NAME_NOT_ASSIGNED);

        $merchantCurrencyTransfer = (new CurrencyTransfer())->setIdCurrency(1)->setCode('EUR');
        $otherCurrencyTransfer = (new CurrencyTransfer())->setIdCurrency(2)->setCode('TRY');

        $storeWithCurrencyTransfers = [
            (new StoreWithCurrencyTransfer())->setStore($assignedStoreTransfer)->addCurrency($merchantCurrencyTransfer),
            (new StoreWithCurrencyTransfer())->setStore($notAssignedStoreTransfer)->addCurrency($otherCurrencyTransfer),
        ];

        $currencyFacadeMock = Stub::makeEmpty(
            ProductOfferMerchantPortalGuiToCurrencyFacadeInterface::class,
            [
                'getAllStoresWithCurrencies' => function () use ($storeWithCurrencyTransfers) {
                    return $storeWithCurrencyTransfers;
                },
            ],
        );

        $storeRelationTransfer = (new StoreRelationBuilder())
            ->withStores($assignedStoreTransfer->toArray())
            ->build();

        $merchantUserTransfer = (new MerchantUserBuilder())
            ->withMerchant([
                MerchantTransfer::ID_MERCHANT => 1,
                MerchantTransfer::STORE_RELATION => $storeRelationTransfer,
            ])
            ->build();

        $merchantUserFacadeMock = Stub::makeEmpty(
            ProductOfferMerchantPortalGuiToMerchantUserFacadeInterface::class,
            [
                'getCurrentMerchantUser' => function () use ($merchantUserTransfer) {
                    return $merchantUserTransfer;
                },
            ],
        );

        $priceProductOfferGuiTableConfigurationProvider = $this->createPriceProductOfferGuiTableConfigurationProvider(
            $merchantUserFacadeMock,
            $currencyFacadeMock,
        );

        // Act
        $currencyOptions = $priceProductOfferGuiTableConfigurationProvider->getCurrencyOptionsForTest();

        // Assert
        $this->assertCount(1, $currencyOptions);
        $this->assertArrayHasKey((string)$merchantCurrencyTransfer->getIdCurrencyOrFail(), $currencyOptions);
    }

    protected function createPriceProductOfferGuiTableConfigurationProvider(
        ProductOfferMerchantPortalGuiToMerchantUserFacadeInterface $merchantUserFacade,
        ?ProductOfferMerchantPortalGuiToCurrencyFacadeInterface $currencyFacade = null
    ) {
        return new class (
            Stub::makeEmpty(GuiTableFactoryInterface::class),
            Stub::makeEmpty(ProductOfferMerchantPortalGuiToPriceProductFacadeInterface::class),
            $merchantUserFacade,
            $currencyFacade ?? Stub::makeEmpty(ProductOfferMerchantPortalGuiToCurrencyFacadeInterface::class),
            Stub::makeEmpty(ColumnIdCreatorInterface::class),
        ) extends AbstractPriceProductOfferGuiTableConfigurationProvider {
            /**
             * @return array<string>
             */
            public function getStoreOptionsForTest(): array
            {
                return $this->getStoreOptions();
            }

            /**
             * @return array<string>
             */
            public function getCurrencyOptionsForTest(): array
            {
                return $this->getCurrencyOptions();
            }
        };
    }
}
