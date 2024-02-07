import mapboxgl from "mapbox-gl";
import {polygon} from '@turf/helpers'
import centroid from '@turf/centroid'
import isEmpty from "./isEmpty";
import createPopupContent from "./createPopupContent";

const popupOffset = 0;

function getCentroid(coordinates) {
  return centroid(polygon(coordinates)).geometry.coordinates;
}

class Polygon {
  constructor (id, featureId, coordinates, itemData, hashKey, map, onCloseCallback) {
    this.id = id;
    this.featureId = featureId;
    this.coordinates = coordinates;
    this.center = getCentroid(coordinates);
    this.itemType = itemData?.type;
    this.itemData = itemData;

    this.popup = null;
    this.popupEl = null;
    
    this.hashKey = isEmpty(hashKey) ? hashKey : 'directory';

    this.onCloseCallback = onCloseCallback

    this.map = map;

    this.init()
  }
  init = () => {
    this.createPopup();
  }
  onHashKeyChange = (hashKey) => {
    this.hashKey = hashKey ?? 'directory';
    this.updatePopupContent();
  }
  addClickOutListeners = () => {
    this.map.on('click', this.onClickOut)
  }
  removeClickOutListeners = () => {
    window.removeEventListener('click', this.onClickOut)
  }
  onClickOut = (e) => {
    const clickWithinPopup = this.contains(e.originalEvent.target);
    const featureAtPoint = !isEmpty(e.point) && this.map.queryRenderedFeatures(e.point)[0];
    const featureMapId = featureAtPoint && featureAtPoint.properties.mapId;
    const clickWithinPolygon = featureMapId === this.id;
    if (clickWithinPopup || clickWithinPolygon) return;
    this.close();
  }
  createPopup = () => {
    this.popup = new mapboxgl.Popup({
      offset: popupOffset,
      closeOnClick: false,
      closeButton: false,
      maxWidth: "100%",
      focusAfterOpen: false,
    });
    const content = createPopupContent(this.itemData, this.hashKey ?? 'directory')
    this.popup.setDOMContent(content);
  }
  updatePopupContent = () => {
    const updatedContent = createPopupContent(this.itemData, this.hashKey ?? 'directory')
    if (!updatedContent) return;
    this.popup?.setDOMContent(updatedContent)
  }
  open = (e) => {
    if (e.target && this.contains(e.target) || this.isOpen) return;

    this.isOpen = true;
    this.createPopup();
    this.addTo(this.map)
    this.showPopup()
    this.addClickOutListeners()
  };
  close = (e) => {
    if (!this.isOpen) return;

    this.map.off('click', this.onClickOut)
    this.removeClickOutListeners();

    this.isOpen = false;

    this.hidePopup();
    this.onCloseCallback && this.onCloseCallback(this);
  }
  showPopup = () => {
    if (this.popup && this.popupEl) return;
    const popupContent = createPopupContent(this.itemData, this.hashKey);
    this.popup
      .setLngLat(this.center)
      .setDOMContent(popupContent)
      .addTo(this.map);

    this.popupEl = this.popup?.getElement();
  }
  hidePopup = () => {
    this.popup && this.popup.remove();
    this.popup = null;
    this.popupEl = null;
  }
  onEnter = (e) => {
    if (this.popupEl) return;

    this.popup
      .setLngLat(this.center)
      .addTo(this.map);

    this.popupEl = this.popup?.getElement();
  }
  onLeave = (e) => {
    if (e != null && this.popupEl.contains(e.relatedTarget)) return;
    this.removePopupListeners();
    this.popup.remove();
    this.popupEl = null;
  }
  addTo = (map) => {
    return this;
  }
  remove = () => {
    this.hidePopup()
  };
  contains = (target) => {
    return this.popupEl?.contains(target)
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

export default Polygon

