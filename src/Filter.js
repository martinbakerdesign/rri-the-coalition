class Filter {
    constructor (el, onChangeCallback) {
        this.el = el;

        this.taxonomy = this.el.dataset.taxonomy;

        this.countEl = this.el.querySelector('.filter__toggle__count');
        this.previewEl = this.el.querySelector('.filter__toggle__preview');

        this.inputs = [...el.querySelectorAll('input')];
        this.allInput = el.querySelector('input[value$="-all"]');

        this.onChangeCallback = onChangeCallback;

        this.options = {}
        for (const input of this.inputs.slice(1)) {
            this.options[input.value] = input.closest('label').textContent;
        }

        this.value = this.getValue();

        this.inputs.forEach(input => input.addEventListener('change',this.onChange))

        this.updateDom();
    }
    getValue = (mapAll = false) => {
        const value = this.inputs.map((input) =>
            (input.checked ? input.value : false)
        )
            .filter((val) => false !== val);
        if (!mapAll || !value.includes(this.allInput.value)) return value.map(v => +v);
        return false;
    }
    onChange = (e) => {
        const target = e.target;
        const checked = target.checked;
        const isAll = target === this.allInput;

        if (isAll) {
            // uncheck all other inputs
            this.inputs.slice(1).forEach(
                input => (input.checked = !checked)
            );
        } else if (this.allInput.checked) {
            this.allInput.checked = false;
        }

        this.value = this.getValue();

        this.updateDom()

        this?.onChangeCallback();
    }
    updateDom = () => {
        if (this.allInput.checked && this.value.length > 1) {
            this.allInput.checked = false;
        }

        const isAll = this.allInput.checked;

        const count = isAll
            ? this.inputs.length - 1
            : this.value.length;
        this.countEl.textContent = count;

        const preview = isAll
            ? 'All'
            : this.value.map(value => this.options[value]).join(', ');
        this.previewEl.textContent = preview;
        
    }
    reset = () => {
        for (const input of this.inputs) {
            const isAll = input.value.slice(-4) === '-all';
            input.checked = isAll;
        }
        this.value = this.getValue();
        this.updateDom();
    }
}

export default Filter;