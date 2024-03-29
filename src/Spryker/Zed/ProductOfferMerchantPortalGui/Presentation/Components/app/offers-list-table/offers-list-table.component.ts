import { ChangeDetectionStrategy, Component, Input, ViewEncapsulation } from '@angular/core';
import { TableConfig } from '@spryker/table';

@Component({
    selector: 'mp-offers-list-table',
    templateUrl: './offers-list-table.component.html',
    styleUrls: ['./offers-list-table.component.less'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    encapsulation: ViewEncapsulation.None,
})
export class OffersListTableComponent {
    @Input() config: TableConfig;
    @Input() tableId?: string;
}
