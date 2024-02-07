import mapboxgl from "mapbox-gl";
import getIconHTML from "./getIconHTML";
import appendMemberPopupContent from "./appendMemberPopupContent";
import appendProjectPopupContent from "./appendProjectPopupContent";
import appendFellowPopupContent from "./appendFellowPopupContent";
import isEmpty from "./isEmpty";
import breakPoints from "./breakPoints";

const popupOffset = 18;

class Marker {
  constructor (id, coordinates, itemData, hashKey, map, onEnterCallback, onLeaveCallback) {
    this.id = id;
    this.coordinates = coordinates;
    this.itemType = itemData?.type;
    this.itemData = itemData;

    this.marker = null;
    this.markerEl = null;
    this.popup = null;
    this.popupEl = null;

    this.hashKey = isEmpty(hashKey) ? hashKey : 'directory';

    this.onEnterCallback = onEnterCallback;
    this.onLeaveCallback = onLeaveCallback;

    this.map = map;

    this.init()
  }
  init = () => {
    this.markerEl = this.createDomContainer();

    this.createMarker();

    this.createPopup();
  }
  onHashKeyChange = (hashKey) => {
    this.hashKey = hashKey;
    this.updatePopupContent();
  }
  addMarkerListeners = () => {
    this.markerEl.addEventListener('mouseenter',this.onEnter);
    this.markerEl.addEventListener("mouseleave", this.onLeaveCallback);
  }
  addPopupListeners = () => {
    this.popupEl.addEventListener('mouseleave',this.onLeaveCallback)
  }
  removeMarkerListeners = () => {
    this.markerEl?.removeEventListener('mouseleave',this.onLeaveCallback)
  }
  removePopupListeners = () => {
    this.popupEl && (
      this.popupEl.removeEventListener('mouseleave',this.onLeaveCallback)
    )
  }
  createDomContainer = () => {
    const {id, type, name} = this.itemData;
    const container = document.createElement("a");
    const hashKey = isEmpty(this.hashKey) ? this.hashKey : 'directory';
    container.setAttribute(
      "href",
      `?focus=${type}&id=${id}#${hashKey}`
    );
    container.className =
      "flex gap-x-1 items-center map_marker relative pointer-events-auto cursor-pointer overflow-visible";
  
    container.dataset.id = id;
    container.dataset.type = type;
  
    container.innerHTML = getIconHTML(type, 1, name);

    return container;
  }
  createMarker = () => {
    this.marker = new mapboxgl.Marker({
      element: this.markerEl,
    }).setLngLat(this.coordinates);

    return this.marker;
  }
  createPopup = () => {
    this.popup = new mapboxgl.Popup({
      offset: popupOffset,
      closeOnClick: window.innerWidth < breakPoints.lg,
      closeButton: false,
      maxWidth: "100%",
      focusAfterOpen: false,
    });
    this.popup.setDOMContent(this.createPopupContent());

    this.marker.setPopup(this.popup);
  }
  createPopupContent = () => {
    const {name, id} = this.itemData;
    const itemType = this.itemType;
    if (!["member", "project", "fellow"].includes(itemType)) return null;
  
    const popupContainer = document.createElement("div");
    // popupContainer.hidden = true;
    // popupContainer.setAttribute('aria-hidden', 'true');
    popupContainer.className =
      "map_popup w-map-popup max-w-popup rounded-m bg-green-50 w-full relative flex flex-col drop-shadow-xl font-sans overflow-hidden min-h-map-thumbnail h-full";
    popupContainer.dataset.type = itemType;
  
    const anchor = document.createElement("a");
    anchor.className = "map_popup_anchor";
    anchor.textContent = name ?? "";
    anchor.tabIndex = 0;
    const hashKey = isEmpty(this.hashKey) ? this.hashKey : 'directory';
    anchor.setAttribute(
      "href",
      `?focus=${itemType}&id=${id}#${hashKey}`
    );
    popupContainer.appendChild(anchor);
  
    const innerContainer = document.createElement("div");
    innerContainer.className =
      "grid gap-x-10 items-center justify-items-start grid-cols-[auto_1fr] min-h-map-popup w-full";
    popupContainer.appendChild(innerContainer);
  
    const appendContentFn = itemType === 'member'
      ? appendMemberPopupContent
      : itemType === 'project'
        ? appendProjectPopupContent
        : appendFellowPopupContent;

    appendContentFn( innerContainer, this.itemData );
  
    return popupContainer;
  }
  updatePopupContent = () => {
    this.popup?.setDOMContent(this.createPopupContent())
  }
  onEnter = (e) => {
    if (this.popupEl) return;
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

    this.popup.setLngLat(this.coordinates)
      .addTo(this.map);

    this.popupEl = this.popup?.getElement();

    this.addPopupListeners();

    this.onEnterCallback
      && this.onEnterCallback(this);
  }
  onLeave = () => {
    this.removePopupListeners();
    this.popup.remove();
    this.popupEl = null;
  }
  addTo = (map) => {
    this.addMarkerListeners()
    return this.marker?.addTo(map);
  }
  remove = () => {
    this.removeMarkerListeners();

    this.removePopup()
    
    this.marker?.remove();
  };
  removePopup = () => {
    this.removePopupListeners();
  
    this.popup?.remove();
    this.popupEl = null;
  }
  contains = (target) => {
    return this.markerEl?.contains(target) || this.popupEl?.contains(target)
  }
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

export default Marker

