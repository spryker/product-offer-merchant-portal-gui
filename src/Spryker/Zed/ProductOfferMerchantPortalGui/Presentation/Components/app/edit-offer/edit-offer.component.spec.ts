import { Component, Input, NO_ERRORS_SCHEMA } from '@angular/core';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { By } from '@angular/platform-browser';
import { EditOfferComponent } from './edit-offer.component';

@Component({
    standalone: false,
    template: `
        <mp-edit-offer [productDetailsTitle]="productDetailsTitle" [images]="images" [product]="product">
            <span title></span>
            <span sub-title></span>
            <span approval-status></span>
            <span action></span>
            <span product-status></span>
            <span product-details></span>
            <div class="default-slot"></div>
        </mp-edit-offer>
    `,
})
class TestHostComponent {
    @Input() productDetailsTitle: any;
    @Input() images: any;
    @Input() product: any;
}

describe('EditOfferComponent', () => {
    let hostFixture: ComponentFixture<TestHostComponent>;

    beforeEach(() => {
        TestBed.configureTestingModule({
            declarations: [EditOfferComponent, TestHostComponent],
            schemas: [NO_ERRORS_SCHEMA],
        });

        hostFixture = TestBed.createComponent(TestHostComponent);
        hostFixture.detectChanges();
    });

    it('should render <mp-edit-offer> component', () => {
        const editOfferComponent = hostFixture.debugElement.query(By.css('mp-edit-offer'));

        expect(editOfferComponent).toBeTruthy();
    });

    it('should render <spy-headline> component', () => {
        const headlineComponent = hostFixture.debugElement.query(By.css('spy-headline'));

        expect(headlineComponent).toBeTruthy();
    });

    it('should render <spy-card> component', () => {
        const cardComponent = hostFixture.debugElement.query(By.css('spy-card'));

        expect(cardComponent).toBeTruthy();
    });

    it('should render <mp-image-slider> component to the <spy-card> component', () => {
        const mockImages = [
            {
                src: 'mockImages',
                alt: 'mockImages',
            },
        ];
        const localHostFixture = TestBed.createComponent(TestHostComponent);
        localHostFixture.componentRef.setInput('images', mockImages);
        localHostFixture.detectChanges();

        const imageSliderComponent = localHostFixture.debugElement.query(By.css('spy-card mp-image-slider'));

        expect(imageSliderComponent).toBeTruthy();
    });

    it('should render <spy-collapsible> component to the <spy-card> component', () => {
        const collapsibleComponent = hostFixture.debugElement.query(By.css('spy-card spy-collapsible'));

        expect(collapsibleComponent).toBeTruthy();
    });

    it('should render `title` slot to the <spy-headline> component', () => {
        const titleSlot = hostFixture.debugElement.query(By.css('spy-headline [title]'));

        expect(titleSlot).toBeTruthy();
    });

    it('should render `sub-title` slot to the <spy-headline> component', () => {
        const subTitleSlot = hostFixture.debugElement.query(By.css('spy-headline [sub-title]'));

        expect(subTitleSlot).toBeTruthy();
    });

    it('should render `approval-status` slot to the <spy-headline> component', () => {
        const approvalStatusSlot = hostFixture.debugElement.query(By.css('spy-headline [approval-status]'));

        expect(approvalStatusSlot).toBeTruthy();
    });

    it('should render `action` slot to the <spy-headline> component', () => {
        const actionSlot = hostFixture.debugElement.query(By.css('spy-headline [action]'));

        expect(actionSlot).toBeTruthy();
    });

    it('should render `product-status` slot to the <spy-card> component', () => {
        const productStatusSlot = hostFixture.debugElement.query(By.css('spy-card [product-status]'));

        expect(productStatusSlot).toBeTruthy();
    });

    it('should render `product-details` slot to the <spy-collapsible> component', () => {
        const productDetailsSlot = hostFixture.debugElement.query(By.css('spy-collapsible [product-details]'));

        expect(productDetailsSlot).toBeTruthy();
    });

    it('should render default slot to the <mp-edit-offer> component', () => {
        const defaultSlot = hostFixture.debugElement.query(By.css('mp-edit-offer .default-slot'));

        expect(defaultSlot).toBeTruthy();
    });

    it('should bound `@Input(productDetailsTitle)` to the `title` input of <spy-collapsible> component', () => {
        const mockProductDetailsTitle = 'productDetailsTitle';
        const localHostFixture = TestBed.createComponent(TestHostComponent);
        localHostFixture.componentRef.setInput('productDetailsTitle', mockProductDetailsTitle);
        localHostFixture.detectChanges();

        const collapsibleComponent = localHostFixture.debugElement.query(By.css('spy-collapsible'));

        expect(collapsibleComponent.properties.spyTitle).toBe(mockProductDetailsTitle);
    });

    it('should bound `@Input(images)` to the `images` input of <mp-image-slider> component', () => {
        const mockImages = [
            {
                src: 'mockImages',
                alt: 'mockImages',
            },
        ];
        const localHostFixture = TestBed.createComponent(TestHostComponent);
        localHostFixture.componentRef.setInput('images', mockImages);
        localHostFixture.detectChanges();

        const imageSliderComponent = localHostFixture.debugElement.query(By.css('mp-image-slider'));

        expect(imageSliderComponent.properties.images).toEqual(mockImages);
    });

    it('should render `@Input(product)` data to the appropriate places', () => {
        const mockProduct = {
            name: 'name',
            sku: 'sku',
            validFrom: '2023-03-15T00:00:00',
            validTo: '2025-05-25T00:00:00',
            validDateFormat: 'dd.MM.yyyy',
            validFromTitle: 'validFromTitle',
            validToTitle: 'validToTitle',
        };
        const localHostFixture = TestBed.createComponent(TestHostComponent);
        localHostFixture.componentRef.setInput('product', mockProduct);
        localHostFixture.detectChanges();

        const productTitleElem = localHostFixture.debugElement.query(By.css('.mp-edit-offer__product-title'));
        const productSkuElem = localHostFixture.debugElement.query(By.css('.mp-edit-offer__product-sku'));
        const validFromValueElem = localHostFixture.debugElement.query(
            By.css('.mp-edit-offer__product-dates-col:first-child .mp-edit-offer__product-dates-value'),
        );
        const validToValueElem = localHostFixture.debugElement.query(
            By.css('.mp-edit-offer__product-dates-col:last-child .mp-edit-offer__product-dates-value'),
        );
        const validFromTitleElem = localHostFixture.debugElement.query(
            By.css('.mp-edit-offer__product-dates-col:first-child .mp-edit-offer__product-dates-title'),
        );
        const validToTitleElem = localHostFixture.debugElement.query(
            By.css('.mp-edit-offer__product-dates-col:last-child .mp-edit-offer__product-dates-title'),
        );

        expect(productTitleElem.nativeElement.textContent).toContain(mockProduct.name);
        expect(productSkuElem.nativeElement.textContent).toContain(mockProduct.sku);
        expect(validFromValueElem.nativeElement.textContent.trim()).toBe('15.03.2023');
        expect(validToValueElem.nativeElement.textContent.trim()).toBe('25.05.2025');
        expect(validFromTitleElem.nativeElement.textContent).toContain(mockProduct.validFromTitle);
        expect(validToTitleElem.nativeElement.textContent).toContain(mockProduct.validToTitle);
    });
});
