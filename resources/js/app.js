import './bootstrap';

import Alpine from 'alpinejs';
import listingForm from './listing-form';

window.Alpine = Alpine;

Alpine.data('listingForm', listingForm);

Alpine.start();
