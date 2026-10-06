import './bootstrap';
import * as bootstrap from 'bootstrap';

import Alpine from 'alpinejs';
import listingForm from './listing-form';
import './form-validation';

window.bootstrap = bootstrap;
window.Alpine = Alpine;

Alpine.data('listingForm', listingForm);

Alpine.start();
