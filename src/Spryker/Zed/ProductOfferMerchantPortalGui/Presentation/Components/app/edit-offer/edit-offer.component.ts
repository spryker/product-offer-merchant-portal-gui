import { ChangeDetectionStrategy, Component, Input, ViewEncapsulation } from '@angular/core';
import { jsonAttribute } from '@spryker/utils';
import { Image } from '../image-slider/image-slider.component';

export interface ProductDetails {
    name: string;
    sku: string;
    validFrom: string | Date;
    validTo: string | Date;
    validDateFormat: string;
    validFromTitle: string;
    validToTitle: string;
}

@Component({
    standalone: false,
    selector: 'mp-edit-offer',
    templateUrl: './edit-offer.component.html',
    styleUrls: ['./edit-offer.component.less'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    encapsulation: ViewEncapsulation.None,
    host: {
        class: 'mp-edit-offer',
    },
})
export class EditOfferComponent {
    @Input({ transform: jsonAttribute }) product?: ProductDetails;
    @Input({ transform: jsonAttribute }) images?: Image[];
    @Input() productDetailsTitle?: string;
    @Input() productCardTitle?: string;
}
