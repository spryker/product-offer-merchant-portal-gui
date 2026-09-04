import { Component, Input, NO_ERRORS_SCHEMA } from '@angular/core';
import { TestBed } from '@angular/core/testing';
import { By } from '@angular/platform-browser';
import { ImageSliderComponent } from './image-slider.component';

const mockedImages = [
    {
        src: 'mockSrc1',
        alt: 'mockAlt1',
    },
    {
        src: 'mockSrc2',
        alt: 'mockAlt2',
    },
    {
        src: 'mockSrc3',
        alt: 'mockAlt3',
    },
];

@Component({
    standalone: false,
    template: ` <mp-image-slider [images]="images"></mp-image-slider> `,
})
class TestHostComponent {
    @Input() images: unknown;
}

describe('ImageSliderComponent', () => {
    beforeEach(() => {
        TestBed.configureTestingModule({
            declarations: [ImageSliderComponent, TestHostComponent],
            schemas: [NO_ERRORS_SCHEMA],
        });
    });

    it('should render `.image-slider-preview` element if number of images > 1', () => {
        const hostFixture = TestBed.createComponent(TestHostComponent);
        hostFixture.componentRef.setInput('images', mockedImages);
        hostFixture.detectChanges();

        const sliderPreviewElem = hostFixture.debugElement.query(By.css('.image-slider-preview'));

        expect(sliderPreviewElem).toBeTruthy();
    });

    it('should NOT render image slider if number of images is 0', () => {
        const hostFixture = TestBed.createComponent(TestHostComponent);
        hostFixture.componentRef.setInput('images', []);
        hostFixture.detectChanges();

        const sliderElem = hostFixture.debugElement.query(By.css('.image-slider'));

        expect(sliderElem).toBeFalsy();
    });

    it('should render only showcard if number of images is 1', () => {
        const hostFixture = TestBed.createComponent(TestHostComponent);
        hostFixture.componentRef.setInput('images', [mockedImages[0]]);
        hostFixture.detectChanges();

        const sliderPreviewElem = hostFixture.debugElement.query(By.css('.image-slider-preview'));

        expect(sliderPreviewElem).toBeFalsy();
    });

    it('should render `.image-slider-preview` element with only 3 images if images array length > 3', () => {
        const hostFixture = TestBed.createComponent(TestHostComponent);
        hostFixture.componentRef.setInput('images', [...mockedImages, ...mockedImages]);
        hostFixture.detectChanges();

        const sliderPreviewElem = hostFixture.debugElement.query(By.css('.image-slider-preview'));
        const imagesArrayLength = sliderPreviewElem.nativeElement.children.length;

        expect(imagesArrayLength === 3).toBeTruthy();
    });

    it('should show first image from the list by default', () => {
        const hostFixture = TestBed.createComponent(TestHostComponent);
        hostFixture.componentRef.setInput('images', mockedImages);
        hostFixture.detectChanges();

        const sliderShowcardImgElem = hostFixture.debugElement.query(By.css('.image-slider-showcard-image'));

        expect(sliderShowcardImgElem.properties.src).toBe(mockedImages[0].src);
        expect(sliderShowcardImgElem.properties.alt).toBe(mockedImages[0].alt);
    });

    it('should change showcard image src and alt by mouseover event on image from preview', () => {
        const hostFixture = TestBed.createComponent(TestHostComponent);
        hostFixture.componentRef.setInput('images', mockedImages);
        hostFixture.detectChanges();

        const sliderPreviewElem = hostFixture.debugElement.query(By.css('.image-slider-preview-img:nth-child(2)'));

        sliderPreviewElem.triggerEventHandler('mouseover', null);
        hostFixture.detectChanges();

        const sliderShowcardImgElem = hostFixture.debugElement.query(By.css('.image-slider-showcard-image'));

        expect(sliderShowcardImgElem.properties.src).toBe(mockedImages[1].src);
        expect(sliderShowcardImgElem.properties.alt).toBe(mockedImages[1].alt);
    });
});
