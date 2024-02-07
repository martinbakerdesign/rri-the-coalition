class Collapsible {
    constructor (el) {
        this.el = el;

        this.toggle = el.querySelector('.collapsible__toggle');
        this.collapsible = el.querySelector('.collapsible__collapsible');

        this.open = this.el.dataset.state === 'open';

        this.updateDom();

        this.toggle.addEventListener('click',this.toggleOpen)
    }
    toggleOpen = () => {
        this.open = !this.open;
        this.updateDom()
    }
    updateDom = () => {
        this.el.dataset.state = this.open ? 'open' : 'closed';

        const hidden = !this.open;
        this.collapsible.hidden = hidden;
        this.collapsible.setAttribute('aria-hidden', hidden);
    }
}

export default Collapsible;