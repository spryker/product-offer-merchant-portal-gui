import { Component, Input, NO_ERRORS_SCHEMA } from '@angular/core';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { By } from '@angular/platform-browser';
import { OffersListComponent } from './offers-list.component';

@Component({
    standalone: false,
    template: `
        <mp-offers-list [tableConfig]="tableConfig" [tableId]="tableId">
            <span title></span>
            <span action></span>
        </mp-offers-list>
    `,
})
class TestHostComponent {
    @Input() tableConfig: unknown;
    @Input() tableId: unknown;
}

describe('OffersListComponent', () => {
    let hostFixture: ComponentFixture<TestHostComponent>;

    beforeEach(() => {
        TestBed.configureTestingModule({
            declarations: [OffersListComponent, TestHostComponent],
            schemas: [NO_ERRORS_SCHEMA],
        });

        hostFixture = TestBed.createComponent(TestHostComponent);
        hostFixture.detectChanges();
    });

    it('should render <mp-offers-list-table> component', () => {
        const offersListTableComponent = hostFixture.debugElement.query(By.css('mp-offers-list-table'));

        expect(offersListTableComponent).toBeTruthy();
    });

    it('should render <spy-headline> component', () => {
        const headlineComponent = hostFixture.debugElement.query(By.css('spy-headline'));

        expect(headlineComponent).toBeTruthy();
    });

    it('should render `title` slot to the <spy-headline> component', () => {
        const titleSlot = hostFixture.debugElement.query(By.css('spy-headline [title]'));

        expect(titleSlot).toBeTruthy();
    });

    it('should render `action` slot to the <spy-headline> component', () => {
        const actionSlot = hostFixture.debugElement.query(By.css('spy-headline [action]'));

        expect(actionSlot).toBeTruthy();
    });

    it('should bound `@Input(tableConfig)` to the `config` input of <mp-offers-list-table> component', () => {
        const mockTableConfig = {
            config: 'config',
            data: 'data',
            columns: 'columns',
        };
        const localHostFixture = TestBed.createComponent(TestHostComponent);
        localHostFixture.componentRef.setInput('tableConfig', mockTableConfig);
        localHostFixture.detectChanges();

        const offersListTableComponent = localHostFixture.debugElement.query(By.css('mp-offers-list-table'));

        expect(offersListTableComponent.properties.config).toEqual(mockTableConfig);
    });

    it('should bound `@Input(tableId)` to the `tableId` input of <mp-offers-list-table> component', () => {
        const mockTableId = 'mockTableId';
        const localHostFixture = TestBed.createComponent(TestHostComponent);
        localHostFixture.componentRef.setInput('tableId', mockTableId);
        localHostFixture.detectChanges();

        const offersListTableComponent = localHostFixture.debugElement.query(By.css('mp-offers-list-table'));

        expect(offersListTableComponent.properties.tableId).toEqual(mockTableId);
    });
});
