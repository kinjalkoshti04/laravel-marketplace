/**
 * Listing form: dependent dropdowns (category -> subcategory, country -> state -> city -> area)
 * loaded from the /ajax endpoints, plus previews for newly picked photos.
 *
 * `selected` holds the ids to pre-select (old input after a validation error, or the listing being edited).
 */
export default ({ urls, selected }) => ({
    category: selected.category ?? '',
    subcategory: selected.subcategory ?? '',
    country: selected.country ?? '',
    state: selected.state ?? '',
    city: selected.city ?? '',
    area: selected.area ?? '',

    subcategories: [],
    states: [],
    cities: [],
    areas: [],
    loading: {},
    previews: [],

    async init() {
        // Restore the whole chain without clearing the pre-selected children.
        await Promise.all([
            this.category && this.load('subcategories', { category_id: this.category }),
            this.country && this.load('states', { country_id: this.country }),
            this.state && this.load('cities', { state_id: this.state }),
            this.city && this.load('areas', { city_id: this.city }),
        ]);
    },

    async load(list, params) {
        this.loading[list] = true;
        try {
            const response = await fetch(`${urls[list]}?${new URLSearchParams(params)}`, {
                headers: { Accept: 'application/json' },
            });
            this[list] = response.ok ? await response.json() : [];
        } finally {
            this.loading[list] = false;
        }
    },

    categoryChanged() {
        this.subcategory = '';
        this.subcategories = [];
        if (this.category) this.load('subcategories', { category_id: this.category });
    },

    countryChanged() {
        this.state = this.city = this.area = '';
        this.states = this.cities = this.areas = [];
        if (this.country) this.load('states', { country_id: this.country });
    },

    stateChanged() {
        this.city = this.area = '';
        this.cities = this.areas = [];
        if (this.state) this.load('cities', { state_id: this.state });
    },

    cityChanged() {
        this.area = '';
        this.areas = [];
        if (this.city) this.load('areas', { city_id: this.city });
    },

    previewImages(event) {
        this.previews.forEach((url) => URL.revokeObjectURL(url));
        this.previews = [...event.target.files].map((file) => URL.createObjectURL(file));
    },
});
