class SearchQuery {
    constructor (el, onChangeCallback) {
        this.el = el;

        this.value = el.value;

        this.onChangeCallback = onChangeCallback;

        this.el.addEventListener('input',this.onChange)
    }
    getValue = () => {
        return this.el.value;
    }
    onChange = ( ) => {
        this.value = this.getValue();

        this?.onChangeCallback();
    }
    reset = () => {
        this.el.value = '';

        this.value = '';
    }
}

export default SearchQuery;