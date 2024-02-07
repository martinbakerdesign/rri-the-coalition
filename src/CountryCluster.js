import mapboxgl from "mapbox-gl";
import createPopupContent from "./createPopupContent";
import createIconMarker from "./createIconMarker";
import createToggleMarker from "./createToggleMarker";
import isEmpty from "is-empty";

class CountryCluster {
  constructor(id, coordinates, hashKey, items, counts, map, onCloseCallback) {
    this.id = id;
    this.hashKey = hashKey;
    this.coordinates = coordinates;
    this.items = items;
    this.counts = counts;
    this.properties = {
      meta_type: 'country'
    }

    this.containerEl = null;
    this.marker = null;
    this.markerEl = null;
    this.prevMarkerEl = null;
    this.popup = null;
    this.popupEl = null;

    this.focus = 0;
    this.count = this.items.length;

    this.map = map;

    this.onCloseCallback = onCloseCallback;

    this.hover = false;
  }
  addToggleListeners = () => {
    const buttons = [...this.markerEl?.querySelectorAll("button")];

    for (const button of buttons) {
      button.addEventListener("click", this.toggleFocus);
    }
  };
  onHashKeyChange = (hashKey) => {
    this.hashKey = hashKey;
    this.popup && this.updatePopupContent();
  }
  addClickOutListeners = () => {
    this.map.on('click', this.onClickOut)
  }
  removeClickOutListeners = () => {
    window.removeEventListener('click', this.onClickOut)
  }
  removeToggleListeners = () => {
    const buttons = [...this.markerEl?.querySelectorAll("button")];

    for (const button of buttons) {
      button.removeEventListener("click", this.toggleFocus);
    }
  };
  udpateItems = (items = [], counts) => {
    this.items = items;
    this.count = items.length;
    this.counts = counts;
    this.marker && this.replaceMarker(this.hover)
    this.popup && this.updatePopupContent()
  }
  toggleFocus = (e) => {
    e.preventDefault()
    e.stopPropagation()
    const button = e.target.closest("button");
    const delta = parseInt(button.dataset.delta);
    
    this.focus = (this.focus + delta) % this.count;
    if (this.focus < 0) {
      this.focus = this.count - 1;
    }

    this.updateMarkerContent();
    this.updatePopupContent();
  };
  createMarker = () => {
    const {type,name} = this.items[0]?.properties;
    this.markerEl = this.items.length > 1
      ? createToggleMarker(this.items.length)
      : createIconMarker(type, name, null);

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
      anchor: 'bottom',
      closeOnMove: false,
      closeOnClick: true,
      closeButton: false,
      maxWidth: "100%",
      focusAfterOpen: false,
    });

    this.marker && this.marker.setPopup(this.popup);
  };
  updateMarkerContent = () => {
    this.markerEl.querySelector(".status").textContent = `${
      this.focus + 1
    } of ${this.count}`;
  };
  updatePopupContent = () => {
    const itemData = this.items[this.focus].properties;
    const newContent = createPopupContent(itemData, this.hashKey);
    console.trace()
    this.popup.setDOMContent(newContent);
    this.populEl = this.popup?.getElement();
  };
  removeMarker = () => {
    this.prevMarkerEl = this.markerEl;
    this.marker && this.marker?.remove();
    this.marker = null;
    this.markerEl = null;
  }
  replaceMarker = () => {
    this.removeMarker()

    this.createMarker();

    // this.addMarkerListeners()
    this.marker.addTo(this.map);
  };
  onClickOut = (e) => {
    const clickWithinPopup = this.contains(e.originalEvent.target);
    const featureAtPoint = !isEmpty(e.point) && this.map.queryRenderedFeatures(e.point)[0];
  const featureId = featureAtPoint && featureAtPoint.properties.iso_a3;
    const clickWithinCountry = featureId === this.id;

    if (clickWithinPopup || clickWithinCountry) return;
    
    this.close();
  }
  onMarkerClick = (e) => {
    console.log('onMarkerClick')
    if (this.hover) return;
    this.hover = true;

    this.replaceMarker(true);

    this.showPopup()

    this.addClickOutListeners();
  }
  showPopup = () => {
    const itemData = this.items[this.focus].properties;
    const popupContent = createPopupContent(itemData, this.hashKey);
    this.popup
      .setLngLat(this.coordinates)
      .setDOMContent(popupContent)
      .addTo(this.map);

    this.popupEl = this.popup?.getElement();

    this.addToggleListeners();
  }
  hidePopup = () => {
    this.popup && this.popup.remove();
    this.popup = null;
    this.popupEl = null;
  }
  open = (e) => {
    if (e.target && this.contains(e.target) || this.isOpen) return;

    this.isOpen = true;
    this.createMarker();
    this.createPopup();

    this.addTo(this.map)

    this.showPopup()
  };
  close = (e) => {
    if (!this.isOpen) return;

    this.map.off('click', this.onClickOut)
    this.removeClickOutListeners();
    this.removeToggleListeners();

    this.focus = 0;
    this.isOpen = false;

    this.hidePopup();

    this.removeMarker();
    this.onCloseCallback && this.onCloseCallback();
  }
  onEnter = (e) => {
    return;
    if (this.hover) return;
    
    this.hover = true;

    console.log('onEnter')

    this.createMarker();
    this.createPopup();

    this.addTo(this.map)

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
    return;
    if (!this.hover || !this.isOpen) return;

    this.removeToggleListeners();
    this.removePopupListeners();
    this.removeClickOutListeners();

    this.focus = 0;
    this.hover = false;
    this.isOpen = false;

    this.popup.remove();
    this.popupEl = null;

    this.removeMarker();
  };
  addTo = (map) => {
    this.marker && (this.addClickOutListeners(), this.marker?.addTo(map));
  };
  remove = () => {
    if (!this.hover && !this.isOpen) return;
    this.hover = false;
    this.isOpen = false;
    this.focus = 0;

    this.removeClickOutListeners();
    this.removeToggleListeners();

    this.removeMarker();
    this.hidePopup()
  };
  contains = (target) => {
    if (!this.marker || !target) return false;
    const markerEl = this.marker.getElement();
    return markerEl?.contains(target)
      || this.markerEl?.contains(target)
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

export default CountryCluster
