const SelectActions = {
  Close: 0,
  CloseSelect: 1,
  First: 2,
  Last: 3,
  Next: 4,
  Open: 5,
  PageDown: 6,
  PageUp: 7,
  Previous: 8,
  Select: 9,
  Type: 10,
};

const defaultIdPrefix = "select--";

let id = 0;
function getId() {
  id++;
  return id;
}

class MapItemSelect {
  constructor(el, idPrefix = defaultIdPrefix, onChangeCallback = null) {
    this.el = el;
    this.toggle = el.querySelector("[data-toggle]");
    this.label = el.querySelector("[data-label]");
    this.listbox = el.querySelector("[data-listbox]");

    this.idPrefix = el.id
      ? `${el.id}--`
      : idPrefix != null && idPrefix.length
      ? idPrefix
      : `${defaultIdPrefix}${getId()}--`;

    this.id = el.id && el.id.length ? el.id : this.idPrefix.slice(0, -2);

    this.open = "open" === el?.dataset?.state;

    this.options = [...el.querySelectorAll("[data-option]")];
    this.maxIndex = this.options.length - 1;

    this.value = null;

    this.selectedOption = this.options[0];
    this.selectedOptionIndex = 0;
    this.focusedOptionIndex = -1;

    this.onChangeCallback = onChangeCallback;

    if (!(el && this.toggle && this.listbox)) return;
    this.init();
  }
  init = () => {
    this.el.id = this.id;

    // set ids for aria references
    [this.toggle, this.label, this.listbox, ...this.options].forEach(
      (el) => null == el.id && (el.id = this.getId())
    );

    // set aria props and roles
    this.setupToggle();
    this.setupListbox();
    this.setupOptions();

    this.el.dataset.state = this.open ? "open" : "closed";

    this.addEventListeners();
  };
  setupToggle = () => {
    const ariaProps = {
      expanded: false,
      haspopup: "listbox",
      autocomplete: "none",
      controls: this.listbox.id,
      labelledby: this.label.id,
      activedescendant: null,
    };
    this.setAriaProps(this.toggle, ariaProps);
    this.toggle.setAttribute("role", "combobox");
    this.toggle.tabIndex = 0;
    (!this.toggle.id || !this.toggle.id.length) &&
      (this.toggle.id = this.idPrefix + "toggle");
  };
  setupListbox = () => {
    const ariaProps = {
      labelledby: this.label.id,
    };

    this.setAriaProps(this.listbox, ariaProps);
    this.listbox.setAttribute("role", "listbox");
    this.listbox.tabIndex = -1;
    (!this.listbox.id || !this.listbox.id.length) &&
      (this.listbox.id = this.idPrefix + "listbox");
  };
  setupOptions = () => {
    this.options.forEach((option, i) => {
      option.role = "option";
      option.dataset.index = i;
      this.setAriaProps(option, { selected: i < 1 });
      option.addEventListener("click", (event) => {
        event.stopPropagation();
        this.onOptionClick(option);
      });
      option.addEventListener("mousedown", this.onOptionMouseDown.bind(this));
      i < 1 && this.selectOption(option);
    });
  };
  addEventListeners = () => {
    this.toggle.addEventListener("blur", this.onToggleBlur.bind(this));
    this.toggle.addEventListener("click", this.onToggleClick.bind(this));
    this.toggle.addEventListener("keydown", this.onToggleKeyDown.bind(this));
    this.listbox.addEventListener("focusout", this.onToggleBlur.bind(this));
  };
  selectOption = (option, callback = true) => {
    if (!option) return;
    this.selectedOption = option;
    this.selectedOptionIndex = parseInt(option.dataset.index);
    // this.focusedOptionIndex = this.selectedOptionIndex;
    // this.value = this.getOptionValue(option);
    // this.setLabel(option.textContent);
    this.options.forEach((o) => {
      // this.setAriaProps(o, {selected: o?.id.toString() === option?.id.toString()})
      this.setAriaProps(o, { selected: false });
    });
    callback &&
      this?.onChangeCallback &&
      this.onChangeCallback(this.getOptionItemId(option));
  };
  getOptionItemId = (option) => {
    return option?.dataset?.itemId;
  };
  getOptionValue = (option) => {
    return option?.dataset?.value ?? option?.value ?? option?.textContent;
  };
  setAriaProps = (el, props) => {
    Object.entries(props).forEach(([key, value]) =>
      el.setAttribute(`aria-${key}`, value)
    );
  };
  getId = () => {
    return this.idPrefix + getId();
  };
  toggleOpen = (open = !this.open) => {
    this.open = open;
    document.body.classList[open ? 'add' : 'remove']('no-overflow');

    this.update();
  };
  setLabel = (content = "") => {
    if (this.label.children) {
      this.label.children[0].textContent = content;
      return;
    }
    this.label.textContent = content;
  };
  update = () => {
    this.el.dataset.state = this.open ? "open" : "closed";

    !this.open &&
      (this.options.forEach((o) => (o.dataset.focus = false)),
      (this.focusedOptionIndex = -1));
    // this.setLabel(this.selectedOption.textContent);

    this.setAriaProps(this.toggle, {
      expanded: this.open,
      activedescendant: this.open ? this.selectedOption?.id ?? null : null,
    });
  };
  getActionFromKey = (event) => {
    const { key, altKey, ctrlKey, metaKey } = event;
    const openKeys = ["ArrowDown", "ArrowUp", "Enter", " "]; // all keys that will do the default open action
    // handle opening when closed
    if (!this.open && openKeys.includes(key)) {
      return SelectActions.Open;
    }

    // home and end move the selected option when open or closed
    if (key === "Home") {
      return SelectActions.First;
    }
    if (key === "End") {
      return SelectActions.Last;
    }

    // handle typing characters when open or closed
    if (
      key === "Backspace" ||
      key === "Clear" ||
      (key.length === 1 && key !== " " && !altKey && !ctrlKey && !metaKey)
    ) {
      return SelectActions.Type;
    }

    // handle keys when open
    if (this.open) {
      if (key === "ArrowUp" && altKey) {
        return SelectActions.CloseSelect;
      } else if (key === "ArrowDown" && !altKey) {
        return SelectActions.Next;
      } else if (key === "ArrowUp") {
        return SelectActions.Previous;
      } else if (key === "PageUp") {
        return SelectActions.PageUp;
      } else if (key === "PageDown") {
        return SelectActions.PageDown;
      } else if (key === "Escape") {
        return SelectActions.Close;
      } else if (key === "Enter" || key === " ") {
        return SelectActions.CloseSelect;
      }
    }
  };
  getUpdatedIndex = (action) => {
    const currentIndex = this.focusedOptionIndex;
    const pageSize = 10; // used for pageup/pagedown

    switch (action) {
      case SelectActions.First:
        return 0;
      case SelectActions.Last:
        return this.maxIndex;
      case SelectActions.Previous:
        return Math.max(0, currentIndex - 1);
      case SelectActions.Next:
        return Math.min(this.maxIndex, currentIndex + 1);
      case SelectActions.PageUp:
        return Math.max(0, currentIndex - pageSize);
      case SelectActions.PageDown:
        return Math.min(this.maxIndex, currentIndex + pageSize);
      default:
        return currentIndex;
    }
  };
  isElementInView = (element) => {
    var bounding = element.getBoundingClientRect();

    return (
      bounding.top >= 0 &&
      bounding.left >= 0 &&
      bounding.bottom <=
        (window.innerHeight || document.documentElement.clientHeight) &&
      bounding.right <=
        (window.innerWidth || document.documentElement.clientWidth)
    );
  };
  isScrollable = (element) => {
    return element && element.clientHeight < element.scrollHeight;
  };
  maintainScrollVisibility = (activeElement, scrollParent) => {
    const { offsetHeight, offsetTop } = activeElement;
    const { offsetHeight: parentOffsetHeight, scrollTop } = scrollParent;

    const isAbove = offsetTop < scrollTop;
    const isBelow = offsetTop + offsetHeight > scrollTop + parentOffsetHeight;

    if (isAbove) {
      scrollParent.scrollTo(0, offsetTop);
    } else if (isBelow) {
      scrollParent.scrollTo(0, offsetTop - parentOffsetHeight + offsetHeight);
    }
  };
  onToggleBlur = (event) => {
    if (this.listbox.contains(event.relatedTarget)) {
      return;
    }

    // select current option and close
    if (this.open) {
      this.selectOption(this.options[this.focusedOptionIndex]);
      this.toggleOpen();
    }
  };
  onToggleClick = () => {
    this.toggleOpen();
  };
  onToggleKeyDown = (event) => {
    const action = this.getActionFromKey(event);

    const focusedOption = this.options[this.focusedOptionIndex];

    switch (action) {
      case SelectActions.Last:
      case SelectActions.First:
        this.update();
        this.toggle.focus();
      // intentional fallthrough
      case SelectActions.Next:
      case SelectActions.Previous:
      case SelectActions.PageUp:
      case SelectActions.PageDown:
        event.preventDefault();
        return this.onOptionFocus(this.getUpdatedIndex(action));
      case SelectActions.CloseSelect:
        event.preventDefault();
        this.selectOption(focusedOption);
        this.toggleOpen(false);
      // intentional fallthrough
      case SelectActions.Close:
        event.preventDefault();
        this.toggleOpen(false);
        return this.toggle.focus();
      case SelectActions.Open:
        event.preventDefault();
        this.toggleOpen(true);
        return this.toggle.focus();
    }
  };
  onOptionFocus = (index) => {
    const option = this.options[index];

    // update state
    this.focusedOptionIndex = parseInt(option?.dataset?.index);

    // update active option styles
    this.options.forEach((o, i) => {
      o.dataset.focus = i === index ? true : false;
    });

    // ensure the new option is in view
    if (this.isScrollable(this.listbox)) {
      this.maintainScrollVisibility(option, this.listbox);
    }

    // ensure the new option is visible on screen
    // ensure the new option is in view
    if (!this.isElementInView(option)) {
      option.scrollIntoView({
        behavior: "smooth",
        block: "nearest",
      });
    }
  };
  onOptionClick = (option) => {
    // const index = parseInt(option.dataset.index);
    // this.onOptionFocus(index);
    this.selectOption(option);
    this.toggleOpen(false);
    this.toggle.focus();
  };
  onOptionMouseDown = () => {
    // Clicking an option will cause a blur event,
    // but we don't want to perform the default keyboard blur action
    this.ignoreBlur = true;
  };
  setValue = (newValue) => {
    const option = this.options.find(
      (option) => option.dataset.value.toString() === newValue.toString()
    );

    if (!option) return;

    this.selectOption(option, false);
  };
}

export default MapItemSelect;
