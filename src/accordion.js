class Accordion {
  constructor(domNode) {
    this.rootEl = domNode.parentElement;
    this.buttonEl = this.rootEl.querySelector('button[aria-expanded]');
    this.iconEl = this.rootEl.querySelector('svg');
    this.parentSectionEl = domNode.closest('.coalition-lens')
    this.parentDescription = this.parentSectionEl.querySelector('.coalition-lens__description');

    const controlsId = this.buttonEl.getAttribute('aria-controls');
    this.contentEl = document.getElementById(controlsId);

    this.open = this.buttonEl.getAttribute('aria-expanded') === 'true';

    // add event listeners
    this.buttonEl.addEventListener('click', this.onButtonClick.bind(this));
  }

  onButtonClick() {
    this.toggle(!this.open);
  }

  toggle(open) {
    // don't do anything if the open state doesn't change
    if (open === this.open) {
      return;
    }

    if (open) {
      accordions.forEach(accordion => (accordion !== this && accordion.parentSectionEl === this.parentSectionEl && accordion.toggle(false)))
    }

    // update the internal state
    this.open = open;

    // handle DOM updates
    this.buttonEl.setAttribute('aria-expanded', `${open}`);
    if (open) {
      this.contentEl.removeAttribute('hidden');
    } else {
      this.contentEl.setAttribute('hidden', '');
    }

    this.rootEl.dataset.expanded = open;

    // toggle icon
    this.iconEl.style.transform = `rotateZ(${open ? 180 : 0}deg)`;

    if (open) {
      this.parentDescription.setAttribute('hidden','')
    } else {
      this.parentDescription.removeAttribute('hidden')
    }
  }
}

// init accordions
const accordions = [];

document.querySelectorAll('.accordion h4').forEach((accordionEl) => {
  accordions.push(new Accordion(accordionEl))
});
