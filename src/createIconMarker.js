import isEmpty from 'is-empty'
import getIconHTML from './getIconHTML';

export default function createIconMarker (type, name, href) {
    const container = document.createElement(!isEmpty(href) ? "a" : 'div');

    !isEmpty(href) && container.setAttribute("href",href)

    container.className =
      "flex gap-x-1 items-center map_marker relative pointer-events-auto cursor-pointer overflow-visible";
  
    container.innerHTML = getIconHTML(type, 1, name);

    return container;
}