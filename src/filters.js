class FilterGroup {
    constructor (containerEl) {
        this.containerEl = containerEl;
        this.inputs = [...containerEl.querySelectorAll('input')]
        this.allInput = containerEl.querySelector('[value$="-all"]')

        this.onChange = this.onChange.bind(this)
        this.inputs.forEach(input => input.addEventListener('change',this.onChange))
    }

    onChange = function (event) {
        const {target} = event;
        const checked = target.checked;
        const isAll = target === this.allInput;

        if (!isAll) {
            checked && (this.allInput.checked = false);
        } else {
            this.inputs.forEach(input => input !== this.allInput && (input.checked = !checked))
        }
    }

}

[...document.querySelectorAll('.search-filter-inputs')].forEach(container => new FilterGroup(container));