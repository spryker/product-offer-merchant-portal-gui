import { Component, Input, NO_ERRORS_SCHEMA } from '@angular/core';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { By } from '@angular/platform-browser';
import { ProductOfferComponent } from './product-offer.component';

@Component({
    standalone: false,
    template: `
        <mp-product-offer [tableConfig]="tableConfig" [tableId]="tableId">
            <span title></span>
            <span description></span>
        </mp-product-offer>
    `,
})
class TestHostComponent {
    @Input() tableConfig: unknown;
    @Input() tableId: unknown;
}

describe('ProductOfferComponent', () => {
    let hostFixture: ComponentFixture<TestHostComponent>;

    beforeEach(() => {
        TestBed.configureTestingModule({
            declarations: [ProductOfferComponent, TestHostComponent],
            schemas: [NO_ERRORS_SCHEMA],
        });

        hostFixture = TestBed.createComponent(TestHostComponent);
        hostFixture.detectChanges();
    });

    it('should render <mp-product-offer-table> component', () => {
        const productOfferTableComponent = hostFixture.debugElement.query(By.css('mp-product-offer-table'));

        expect(productOfferTableComponent).toBeTruthy();
    });

    it('should render <spy-headline> component', () => {
        const headlineComponent = hostFixture.debugElement.query(By.css('spy-headline'));

        expect(headlineComponent).toBeTruthy();
    });

    it('should render `.mp-product-offer__description` element', () => {
        const descriptionElem = hostFixture.debugElement.query(By.css('.mp-product-offer__description'));

        expect(descriptionElem).toBeTruthy();
    });

    it('should render `title` slot to the <spy-headline> component', () => {
        const titleSlot = hostFixture.debugElement.query(By.css('spy-headline [title]'));

        expect(titleSlot).toBeTruthy();
    });

    it('should render `description` slot to the `.mp-product-offer__description` element', () => {
        const descriptionSlot = hostFixture.debugElement.query(By.css('.mp-product-offer__description [description]'));

        expect(descriptionSlot).toBeTruthy();
    });

    it('should bound `@Input(tableConfig)` to the `config` input of <mp-product-offer-table> component', () => {
        const mockTableConfig = {
            config: 'config',
            data: 'data',
            columns: 'columns',
        };
        const localHostFixture = TestBed.createComponent(TestHostComponent);
        localHostFixture.componentRef.setInput('tableConfig', mockTableConfig);
        localHostFixture.detectChanges();

        const productOfferTableComponent = localHostFixture.debugElement.query(By.css('mp-product-offer-table'));

        expect(productOfferTableComponent.properties.config).toEqual(mockTableConfig);
    });

    it('should bound `@Input(tableId)` to the `tableId` input of <mp-product-offer-table> component', () => {
        const mockTableId = 'mockTableId';
        const localHostFixture = TestBed.createComponent(TestHostComponent);
        localHostFixture.componentRef.setInput('tableId', mockTableId);
        localHostFixture.detectChanges();

        const productOfferTableComponent = localHostFixture.debugElement.query(By.css('mp-product-offer-table'));

        expect(productOfferTableComponent.properties.tableId).toEqual(mockTableId);
    });
});
