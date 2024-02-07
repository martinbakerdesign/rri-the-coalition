import mapboxgl from "mapbox-gl";
import getIconHTML from "./getIconHTML";
import getSVGIconMarkup from "./getSVGIconMarkup";
import appendMemberPopupContent from "./appendMemberPopupContent";
import appendProjectPopupContent from "./appendProjectPopupContent";
import appendFellowPopupContent from "./appendFellowPopupContent";
import isEmpty from "./isEmpty";
import createIconMarker from "./createIconMarker";
import createToggleMarker from "./createToggleMarker";
import createPopupContent from "./createPopupContent";

const popupOffset = 18;

class ClusterMarker {
  constructor(id, coordinates, items, counts, hashKey, map, onEnterCallback, onLeaveCallback) {
    this.id = id;
    this.coordinates = coordinates;
    this.items = items;
    this.counts = counts;

    this.marker = null;
    this.markerEl = null;
    this.prevMarkerEl = null;
    this.popup = null;
    this.popupEl = null;

    this.focus = 0;
    this.count = this.items.length;

    this.map = map;

    this.onEnterCallback = onEnterCallback;
    this.onLeaveCallback = onLeaveCallback;

    this.hashKey = isEmpty(hashKey) ? hashKey : 'directory';

    this.hover = false;

    this.init();
  }
  init = () => {
    this.createMarker();

    this.createPopup();
  };
  onHashKeyChange = (hashKey) => {
    this.hashKey = hashKey;
    this.updatePopupContent();
  }
  addMarkerListeners = () => {
    this.markerEl.addEventListener("click", this.onMarkerClick);
    this.markerEl.addEventListener('mouseenter',this.onEnter);
    this.markerEl.addEventListener("mouseleave", this.onLeaveCallback);
  }
  addClickOutListeners = () => {
    window.addEventListener('click', this.onClickOut)
  }
  removeClickOutListeners = () => {
    window.removeEventListener('click', this.onClickOut)
  }
  addPopupListeners = () => {
    this.popupEl.addEventListener("mouseleave", this.onLeaveCallback);
  };
  addToggleListeners = () => {
    const buttons = [...this.markerEl?.querySelectorAll("button")];

    for (const button of buttons) {
      button.addEventListener("click", this.toggleFocus);
    }
  };
  removeMarkerListeners = () => {
    this.markerEl.removeEventListener('mouseenter',this.onEnter);
    this.markerEl.removeEventListener("mouseleave", this.onLeaveCallback);
    this.markerEl.removeEventListener("click", this.onMarkerClick);
  }
  removeToggleListeners = () => {
    const buttons = [...this.markerEl?.querySelectorAll("button")];

    for (const button of buttons) {
      button.removeEventListener("click", this.toggleFocus);
    }
  };
  removePopupListeners = () => {
    this.popupEl && this.popupEl.removeEventListener("mouseleave", this.onLeaveCallback);
  };
  toggleFocus = (e) => {
    const button = e.target.closest("button");
    const delta = parseInt(button.dataset.delta);
    
    this.focus = (this.focus + delta) % this.count;
    if (this.focus < 0) {
      this.focus = this.count - 1;
    }

    this.updateMarkerContent();
    this.updatePopupContent();
  };
  createStaticMarkerContainer = () => {
    // create container
    const container = document.createElement("div");
    container.className =
      "flex gap-x-1 items-center map_cluster pointer-events-auto relative";

    // go through each grouping and create an icon
    let count;
    for (let type in this.counts) {
      count = this.counts[type];
      if (!this.counts[type]) continue;

      const iconHTML = getIconHTML(type, count);

      container.innerHTML += iconHTML;
    }

    return container;
  };
  createHoverMarkerContainer = () => {
    return createToggleMarker(this.count)
  };
  createMarker = (hover = false) => {
    this.markerEl = (
      !hover
        ? this.createStaticMarkerContainer
        : this.createHoverMarkerContainer
    )();

    this.marker = new mapboxgl.Marker({
      element: this.markerEl,
    }).setLngLat(this.coordinates);

    return this.marker;
  };
  createPopup = () => {
    const yOffset = 24;
    const xOffset = 73;
    this.popup = new mapboxgl.Popup({
      offset: {
        top: [0, yOffset],
        "top-left": [-xOffset, yOffset],
        "top-right": [xOffset, yOffset],
        bottom: [0, -yOffset],
        "bottom-left": [-xOffset, -yOffset],
        "bottom-right": [xOffset, -yOffset],
        left: [-xOffset, 0],
        right: [xOffset, 0],
      },
      closeOnClick: false,
      closeButton: false,
      maxWidth: "100%",
      focusAfterOpen: false,
    });

    this.marker.setPopup(this.popup);
  };
  updateMarkerContent = () => {
    this.markerEl.querySelector(".status").textContent = `${
      this.focus + 1
    } of ${this.count}`;
  };
  updatePopupContent = () => {
    const itemData = this.items[this.focus]
    const popupContent = createPopupContent(itemData, this.hashKey)
    this.popup.setDOMContent(popupContent);
  };
  replaceMarker = (isHover = false) => {
    this.prevMarkerEl = this.markerEl;
    this.marker?.remove();

    this.createMarker(isHover);

    this.addMarkerListeners()
    this.marker.addTo(this.map);
  };
  onClickOut = (e) => {
    if (this.contains(e.target)) return;
    this.onLeave();
  }
  onMarkerClick = (e) => {
    if (this.hover) return;
    this.hover = true;

    this.replaceMarker(true);

    this.showPopup()

    this.addClickOutListeners();
  }
  showPopup = () => {
    const itemData = this.items[this.focus]
    const popupContent = createPopupContent(itemData, this.hashKey)
    this.popup
      .setLngLat(this.coordinates)
      .setDOMContent(popupContent)
      .addTo(this.map);

    this.popupEl = this.popup?.getElement();

    this.addPopupListeners();
    this.addToggleListeners();
  }
  onEnter = (e) => {
    if (this.hover) return;
    
    this.hover = true;

    this.replaceMarker(true);

    // const feature = e.features[0];
    // const { geometry } = feature;

    // // Copy coordinates array.
    // const coordinates = geometry.coordinates.slice();

    // //   Ensure that if the map is zoomed out such that multiple
    // //   copies of the feature are visible, the popup appears
    // //   over the copy being pointed to.
    // while (Math.abs(e.lngLat.lng - coordinates[0]) > 180) {
    //   coordinates[0] += e.lngLat.lng > coordinates[0] ? 360 : -360;
    // }

    this.showPopup()

    this.onEnterCallback
      && this.onEnterCallback(this);
  };
  onLeave = () => {
    if (!this.hover) return;

    this.removeToggleListeners();
    this.removePopupListeners();
    this.removeClickOutListeners();

    this.focus = 0;
    this.hover = false;

    this.popup.remove();
    this.popupEl = null;

    this.replaceMarker();
  };
  addTo = (map) => {
    this.addMarkerListeners();
    return this.marker?.addTo(map);
  };
  remove = () => {
    this.hover = false;

    this.removeMarkerListeners();
    this.removePopupListeners();

    this.popup?.remove();
    this.popupEl = null;

    this.marker?.remove();
  };
  contains = (target) => {
    const markerEl = this.marker.getElement();
    return markerEl?.contains(target)
      || this.prevMarkerEl?.contains(target)
      || this.popupEl?.contains(target);
  };
  fadeIn = () => {
    this.markerEl && (this.markerEl.style.transition = 'opacity 300ms ease-out');
    this.popupEl && (this.popupEl.style.transition = 'opacity 300ms ease-out');
    this.markerEl && (this.markerEl.style.opacity = '1');
    this.popupEl && (this.popupEl.style.opacity = '1');
  }
  fadeOut = () => {
    this.markerEl && (this.markerEl.style.transition = 'opacity 300ms ease-out');
    this.popupEl && (this.popupEl.style.transition = 'opacity 300ms ease-out');
    this.markerEl && (this.markerEl.style.opacity = '0');
    this.popupEl && (this.popupEl.style.opacity = '0');
  }
}

export default ClusterMarker
