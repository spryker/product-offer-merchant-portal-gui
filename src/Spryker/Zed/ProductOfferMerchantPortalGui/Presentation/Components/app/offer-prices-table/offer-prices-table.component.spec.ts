import { NO_ERRORS_SCHEMA } from '@angular/core';
import { OfferPricesTableComponent } from './offer-prices-table.component';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { By } from '@angular/platform-browser';

describe('OfferPricesTableComponent', () => {
    let fixture: ComponentFixture<OfferPricesTableComponent>;

    beforeEach(() => {
        TestBed.configureTestingModule({
            declarations: [OfferPricesTableComponent],
            schemas: [NO_ERRORS_SCHEMA],
        });

        fixture = TestBed.createComponent(OfferPricesTableComponent);
    });

    it('should render <spy-table> component', () => {
        const tableComponent = fixture.debugElement.query(By.css('spy-table'));

        expect(tableComponent).toBeTruthy();
    });

    it('should bound `@Input(config)` to the `config` input of <spy-table> component', () => {
        const mockTableConfig = {
            config: 'config',
            data: 'data',
            columns: 'columns',
        };

        fixture.componentRef.setInput('config', mockTableConfig);
        fixture.detectChanges();
        const tableComponent = fixture.debugElement.query(By.css('spy-table'));

        expect(tableComponent.properties.config).toEqual(mockTableConfig);
    });

    it('should bound `@Input(tableId)` to the `tableId` input of <spy-table> component', () => {
        const mockTableId = 'tableId';
        fixture.componentRef.setInput('tableId', mockTableId);
        fixture.detectChanges();
        const tableComponent = fixture.debugElement.query(By.css('spy-table'));

        expect(tableComponent.properties.tableId).toBe(mockTableId);
    });
});
