class Search {
    constructor (containerEl) {
        this.dom = {
            container: containerEl,
            inputs: {
                search: containerEl.querySelector('input[name="search"]'),
            },
            accordion: {
                triggers: [...containerEl.querySelectorAll('.accordion__trigger')]
            }
        }

        this.toggleTrigger = this.toggleTrigger.bind(this);


        this.dom.accordion.triggers.forEach(
            trigger => trigger.addEventListener('click',this.toggleTrigger)
        )
    }

    toggleTrigger = function (event) {
        const trigger = event.target.closest('.accordion__trigger');

        const expanded = trigger.getAttribute('aria-expanded') !== 'true';
        const content = document.querySelector(`#${trigger.getAttribute('aria-controls')}`)
        const meta = trigger.querySelector('.accordion__trigger__meta');
        const icon = trigger.querySelector('.accordion__trigger__icon');

        trigger.setAttribute('aria-expanded', expanded);

        if (expanded) {
            content?.removeAttribute('hidden')
            meta?.setAttribute('hidden','')
        } else {
            content?.setAttribute('hidden','')
            meta?.removeAttribute('hidden')
        }

        icon.style.transform = `rotateZ(${expanded ? 180 : 0}deg)`;
    }
}

new Search(document.querySelector('[role="search"]'));