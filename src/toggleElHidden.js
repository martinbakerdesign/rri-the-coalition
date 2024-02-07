export default function toggleElHidden (el, hidden = true) {
    if (!el) return;
    el.hidden = hidden;
    el.setAttribute("aria-hidden", hidden);
    el.tabIndex = hidden ? -1 : 0;
}