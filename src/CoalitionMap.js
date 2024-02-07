import mapboxgl from "mapbox-gl";
import "mapbox-gl/dist/mapbox-gl.css";

import mapStyle from "./mapStyle.json";

import Marker from "./Marker";
import CountryCluster from "./CountryCluster";
import Cluster from "./ClusterMarker";
import Polygon from "./Polygon";

// import countryBoundaries from "./geodata/boundaries/countries";
// import regionBoundaries from "./geodata/boundaries/regions";
// import countryPoints from "./geodata/points/countries";
// import regionPoints from "./geodata/points/regions";
import replaceState from "./replaceState";
import getURL from "./getURL";
import breakPoints from "./breakPoints";
import isEmpty from "./isEmpty";
// import countryBoundingBoxes from './geodata/countryBoundingBoxes'
import countryPolys from './geodata/country.geometries.js'

mapboxgl.accessToken =
  "pk.eyJ1IjoibWJha2VyLXJhdGhhbmEiLCJhIjoiY2xsdWk5MTQ4MHp2dTNlcWY1YXAxdXB6YSJ9.O2odz3WBo9hsNIBXHc8kiQ";

const debug = false;

const filters = {
  hideAntarctica: [
    "match",
    ["get", "iso_3166_1_alpha_3"],
    "ATA",
    false,
    true,
  ],
  partners: ["all", ["==", ["get", "type"], "member"]],
  projects: ["all", ["==", ["get", "type"], "project"]],
  fellows: ["all", ["==", ["get", "type"], "fellow"]],
};

class CoalitionMap {
  constructor(el, data) {
    this.el = el;

    this.rootTransitionDur = 300;

    this.srcData = JSON.parse(JSON.stringify(data));
    this.data = JSON.parse(JSON.stringify(data));
    this.pointsRegions = this.data.features.filter(
      (f) => f.properties.meta_type !== "country"
    )

    this.fgCountryIds = this.getUniqueCountryIds(this.getFgCountries(this.data.features));
    this.bgCountryIds = this.getUniqueCountryIds(this.getBgCountries(this.data.features));

    this.fgCountriesData = this.getFgCountriesData(this.fgCountryIds);

    this.countryCentroids = this.getCountryCentroids(this.srcData);

    this.padding = {
      top: 0,
      bottom: 0,
      left: 0,
      right: 0,
    };

    this.bounds = this.getBoundsFromFeatures(data.features);

    this.map = new mapboxgl.Map({
      container: el, // container ID
      style: mapStyle,
      minZoom: 0,
      maxZoom: window.innerWidth >= breakPoints.lg ? 5.5 : 8,
      renderWorldCopies: false,
      doubleClickZoom: false,
      // projection: projectionTypes.behrmann,
      attributionControl: false,
    });
    debug && (this.map.showPadding = true);

    this.paddingOffset = 32;

    // this.constants = {
    //   boundaries: {
    //     // regions: regionBoundaries,
    //     // countries: countryBoundaries,
    //   },
    //   points: {
    //     regions: regionPoints,
    //     countries: countryPoints,
    //   },
    // };

    this.hashKey = window.location.hash.replace("#", "");
    isEmpty(this.hashKey) && (this.hashKey = "directory");

    this.srcIds = {
      points: 'map_points',
      regions: 'map_regions',
    }

    this.pointsSrc = {
      source: this.srcIds.points
    }
    this.regionsSrc = {
      source: this.srcIds.regions
    }
    this.countriesSrc = {
      source: "composite",
      "source-layer": "country_boundaries",
      sourceLayer: "country_boundaries",
    };
    this.sources = {
      countries: this.countriesSrc,
      points: this.pointsSrc,
      regions: this.regionsSrc,
    };

    this.layerIds = {
      points: "points",
      clusters: "clusters",
      regions: "regions",
      countries_bg: "countries_bg",
      countries_fg: "countries_fg",
      countries_outlines: "countries_outlines",
    };

    this.features = {};
    this.featuresOnScreen = {};

    this.loaded = false;

    this.clusterRadius = window.innerWidth < breakPoints.lg ? 50 : 35;

    this.hoveredPolygonId = null;
    this.hoveredPolygonMapId = null;
    this.hoveredPolygonFeature = null;

    this.hoverMapId = null;
    this.hoverFeatureId = null;
    this.hoverType = null; // country || region
    this.clickedMapId = null;
    this.clickedFeatureId = null;
    this.clickedType = null; // country || region

    this.resizeObserver = new ResizeObserver(this.onResize);

    this.hashListener = null;

    this.mapIds = this.data.features.map((f) => f.properties.mapId);
    this.itemIds = [
      ...new Set(this.data.features.map((f) => f.properties.itemId)),
    ];

    this.scrollListener = new ScrollListener(
      document.querySelector("#map__top-trigger"),
      document.querySelector("#map__bottom-trigger"),
      el
    );

    this.map.on("load", this.initMap);
  }
  initMap = () => {
    this.loaded = true;
    this.setupMap();
    this.map.doubleClickZoom.disable();
    this.resizeObserver.observe(this.el.parentElement);
  };
  setupMap = () => {
    this.addSources(this.data);

    this.setMapLayers();
    this.setMapListeners();

    this.updateCountryFilters();

    this.bounds = this.getBoundsFromFeatures(this.data.features);
    this.map.fitBounds(this.bounds);

    this.map.on("render", this.onRender);
  };
  onHashKeyChange = (hashKey) => {
    this.hashKey = hashKey;
    Object.values(this.features).forEach((feature) =>
      feature.onHashKeyChange(hashKey)
    );
  };
  setClusterRadius = () => {
    this.clusterRadius = window.innerWidth < breakPoints.lg ? 50 : 35;
  };
  getCountryCentroids = (data) => {
    const centroids = {};

    const filtered = data.features.filter(
      (f) => f.properties.meta_type === "country"
    );

    for (const feature of filtered) {
      const id = feature.properties.iso_a3;
      if (centroids[id]) {
        continue;
      }
      centroids[id] = feature.geometry.coordinates;
    }

    return centroids;
  };

  addSource = (sourceId, data, props = {}) => {
    return this.map.addSource(sourceId, {
      type: "geojson",
      data,
      generateId: true,
      ...props,
    });
  };
  addSources = (data) => {
    const [points, regions] = this.sortFeatures(data.features);

    this.addSource(
      this.srcIds.points,
      {
        type: "FeatureCollection",
        features: points,
      },
      {
        cluster: true,
        clusterRadius: this.clusterRadius,
        clusterProperties: {
          members: ["+", ["case", filters.partners, 1, 0]],
          projects: ["+", ["case", filters.projects, 1, 0]],
          fellows: ["+", ["case", filters.fellows, 1, 0]],
        },
      }
    );
    this.addSource(this.srcIds.regions, {
      type: "FeatureCollection",
      features: regions,
    });
    this.sourcesLoaded = false;
  };
  getFgCountries = (features = []) => {
    const filtered = features
      .filter((f) => f.properties.meta_type === "country")
      .map((f) => f.properties.countries ?? [])
      .flat();
    return filtered;
  };
  getBgCountries = (features = []) => {
    const allCountries = features
      .map((f) => f.properties.countries ?? [])
      .flat();

    return allCountries;
  };
  getFgCountriesData = (countryIds = []) => {
    const dataObj = {};

    for (const countryId of countryIds) {
      const items = this.srcData.features.filter(
        (f) =>
          f.properties.meta_type === "country" &&
          f.properties.iso_a3 === countryId
      );

      dataObj[countryId] = {
        items,
        counts: this.getCounts(items),
      };
    }

    return dataObj;
  };
  getUniqueCountryIds = (countries) => {
    return [...new Set(countries)];
  };
  onResize = () => {
    this.setClusterRadius();

    this.updatePadding();

    this.map?.resize();

    this.bounds && this.fitToBounds();
  };
  updatePadding = () => {
    if (window.innerWidth < breakPoints.lg) {
      this.padding = {
        top: 84 + 32,
        bottom: 32,
        left: 32,
        right: 32,
      };
    } else {
      const parentRect = document
        .querySelector("#mapping-platform")
        ?.getBoundingClientRect();
      const parentTop = parentRect?.top;
      const parentBottom = parentTop + parentRect.height;
      const rect = document
        .querySelector("#map_window")
        ?.getBoundingClientRect();
      this.paddingOffset = window.innerWidth > 1440 ? 32 : 20;
      const offset = this.paddingOffset;

      this.padding = {
        left: Math.round(rect.left) + offset,
        right: Math.round(window.innerWidth - rect.right) + offset,
        top: Math.round(rect.top - parentTop) + offset,
        // bottom: Math.round(rect.top - parentTop),
        bottom: Math.round(parentBottom - rect.bottom) + offset,
      };
    }

    this.map.setPadding(this.padding);
  };
  updateCountryFilters = () => {
    this.map?.setFilter(this.layerIds.countries_bg, [
      "in",
      ["get", "iso_3166_1_alpha_3"],
      ["literal", this.bgCountryIds],
    ]);
    this.map?.setFilter(this.layerIds.countries_fg, [
      "in",
      ["get", "iso_3166_1_alpha_3"],
      ["literal", this.fgCountryIds],
    ]);
    this.map?.setFilter(this.layerIds.countries_outlines, [
      "in",
      ["get", "iso_3166_1_alpha_3"],
      ["literal", this.bgCountryIds],
    ]);
  };
  onRender = () => {
    const sourceLoaded =
      this.map.isSourceLoaded(this.srcIds.points) &&
      this.map.isSourceLoaded(this.srcIds.regions);
    if (!sourceLoaded) return;
    this.sourcesLoaded = true;
    this.updateMapFeatures();
  };
  removeMapLayers = () => {
    this.map.off("mousemove", "item_regions", this.onPolygonMouseMove);
    this.map.off("mouseleave", "item_regions", this.onPolygonMouseLeave);
    this.map.off("click", "item_regions", this.onPolygonClick);

    [
      "item_markers",
      "item_regions",
      "item_clusters",
    ].forEach((layerId) => {
      this.map.getLayer(layerId) && this.map.removeLayer(layerId);
    });
  };
  setMapLayers = () => {
    this.addLayer(this.layerIds.countries_bg, {
      type: "fill",
      ...this.countriesSrc,
      paint: {
        "fill-color": "#D9DEE1",
      },
    });
    this.addLayer(this.layerIds.countries_fg, {
      type: "fill",
      ...this.countriesSrc,
      filter: filters.hideAntarctica,
      paint: {
        "fill-color": [
          "case",
          ["any",
            ["boolean", ["feature-state", "hover"], false],
            ["boolean", ["feature-state", "clicked"], false],
          ],
          "#92C790", // hover
          "#B6D9B5",
        ],
        // "fill-color": "#92C790",
        // "fill-opacity": [
        //   "case",
        //   ["boolean", ["feature-state", "hover"], false],
        //   1, // hover
        //   0.6,
        // ],
      },
    });
    this.addLayer(this.layerIds.countries_outlines, {
      type: "line",
      ...this.countriesSrc,
      paint: {
        "line-color": "#ffffff",
        "line-opacity": 1,
        "line-width": 0.5,
      },
    });
    this.addLayer(this.layerIds.regions, {
      type: "fill",
      ...this.regionsSrc,
      layout: {},
      paint: {
        "fill-color": [
          "case",
          [
            "any",
            ["boolean", ["feature-state", "hover"], false],
            ["boolean", ["feature-state", "clicked"], false],
          ],
          "#49A146", // hover
          "#6DB46B",
        ],
        'fill-opacity': 0.8
      },
    });
    this.addLayer(this.layerIds.clusters, {
      type: "symbol",
      ...this.pointsSrc,
      filter: ["==", "cluster", true],
      layout: {},
    });
    this.addLayer(this.layerIds.points, {
      type: "symbol",
      ...this.pointsSrc,
      filter: ["==", "cluster", false],
      layout: {},
    });
  };
  setMapListeners = () => {
    this.map.on(
      "mousemove",
      [this.layerIds.countries_fg, this.layerIds.regions],
      this.onRegionMouseMove
    );
    this.map.on(
      "mouseleave",
      [this.layerIds.countries_fg, this.layerIds.regions],
      this.onRegionMouseLeave
    );
    this.map.on("click",
    [this.layerIds.countries_fg, this.layerIds.regions],
    this.onRegionClick);
  };
  addLayer = (id, props, before = null) => {
    return this.map.addLayer(
      {
        id,
        ...(props && props),
      },
      before ?? ""
    );
  };

  onRegionMouseMove = (e) => {
    if (!e.features.length) return;

    const feature = e.features[0];
    const isCountry = null != feature.properties.iso_3166_1_alpha_3;
    const featureId = feature.id;
    const mapId = !isCountry
      ? feature.properties.mapId
      : feature.properties.iso_3166_1_alpha_3;
    const featureHovered = null != this.hoverFeatureId;
    const hoveredIsCurrent = featureId === (this.hoverFeatureId ?? null);
    const hideOtherFeature = featureHovered && !hoveredIsCurrent;
    
    if (hideOtherFeature) {
      this.setRegionHover(this.hoverFeatureId, this.hoverMapId, this.hoverType, false);
    }
    
    this.setCursor("pointer");
    
    if (hoveredIsCurrent) return;

    this.setRegionHover( featureId, mapId, isCountry ? 'country' : 'region', true );
  };
  onRegionMouseLeave = (e) => {
    const relatedTarget = e.originalEvent.relatedTarget;
    const hoverFeature = null != this.hoverMapId && this.features[this.hoverMapId];
    const stillInRegion = relatedTarget != null && hoverFeature.contains(relatedTarget);

    if (null == this.hoverFeatureId || stillInRegion )
      return;

    this.setRegionHover(
      this.hoverFeatureId,
      this.hoverMapId,
      this.hoverType,
      false,
      e?.originalEvent ?? null
    );

    this.setCursor('')
  };
  onRegionClick = (e) => {
    e.originalEvent.stopPropagation();

    if (!e.features.length) return;
    
    const feature = e.features[0];
    const isCountry = null != feature.properties.iso_3166_1_alpha_3;
    const featureId = feature.id;
    const mapId = !isCountry
      ? feature.properties.mapId
      : feature.properties.iso_3166_1_alpha_3;
    const type = isCountry ? 'country' : 'region';

    const hasClicked = null != this.clickedMapId;
    const clickedIsCurrent = (mapId === this.clickedMapId)
    const closeCurrent = hasClicked;
    const openNew = !hasClicked || !clickedIsCurrent;
    
    if (closeCurrent) this.removeClicked()
    
    if (!openNew) return;
    this.setClicked(featureId, mapId, type, e)
  };
  onRegionClickOut = () => {
    this.toggleRegionState(this.clickedFeatureId, {clicked: false})
    this.clickedFeatureId = null;
    this.clickedMapId = null;
    this.clickedType = null;
  };
  setRegionHover = (featureId, mapId, type, hover = false) => {
    if (hover || this.hoverFeatureId != null) {
      type === 'region'
        ? this.toggleRegionState(featureId, {hover})
        : this.toggleCountryState(mapId, {hover})
    }

    this.hoverFeatureId = hover ? featureId : null;
    this.hoverMapId = hover ? mapId : null;
    this.hoverType = hover ? type : null;
  };
  setClicked = (featureId, mapId, type, e = {}) => {
    const feature = this.features[mapId];
    if (!feature) {
      console.error('No feature found for mapId: '+mapId);
    }

    this.clickedFeatureId = featureId;
    this.clickedMapId = mapId;
    this.clickedType = type;

    type !== 'country'
      ? this.toggleRegionState(featureId, {clicked: true})
      : this.toggleCountryState(mapId, {clicked: true});

    feature && feature.open(e);
  }
  removeClicked = () => {
    if (null == this.clickedMapId) return;

    const feature = this.features[this.clickedMapId];

    this.clickedType !== 'country'
      ? this.toggleRegionState(this.clickedFeatureId, {clicked: false})
      : this.toggleCountryState(this.clickedMapId, {clicked: false})

    feature.close();
    this.clickedFeatureId = null;
    this.clickedMapId = null;
    this.clickedType = null;
  }
  onCountryClickOut = () => {
    this.removeClicked()
  };
  onRegionLeave = (e) => {
    const target = e?.relatedTarget ?? null;
    const hoverFeature = null != this.regionHoverId && this.features[this.regionHoverId];

    if (target && (!this.regionHoverId || hoverFeature.contains(target)))
      return;

    this.setRegionHover(
      this.regionHoverId,
      false,
      e?.originalEvent ?? null
    );

    this.setCursor('')
  };
  onEnter = (hoverMarker) => {
    this.hoverMarker = hoverMarker;
  };
  onLeave = (e) => {
    const target = e.relatedTarget;
    if (!this.hoverMarker || this.hoverMarker.contains(target)) return;
    this.hoverMarker.onLeave();
    this.hoverMarker = null;
  };
  toggleCountryState = (iso_a3, state = {}) => {
    const related = this.map?.querySourceFeatures(this.countriesSrc.source, {
      sourceLayer: this.countriesSrc.sourceLayer,
      filter: ["match", ["get", "iso_3166_1_alpha_3"], iso_a3, true, false],
    });

    for (const feature of related) {
      this.map?.setFeatureState(
        {
          ...this.countriesSrc,
          id: feature.id,
        },
        state
      );
    }
  };
  toggleRegionState = (featureId, state = {}) => {
    this.map.setFeatureState(
      { ...this.regionsSrc, id: featureId },
      state
    );
    return;
  };

  updateSourceData = (data) => {
    const [points, polygons] = this.sortFeatures(data.features);

    this.map.getSource(this.pointsSourceId).setData({
      ...data,
      features: points,
    });
    this.map.getSource(this.polygonsSourceId).setData({
      ...data,
      features: polygons,
    });
  };
  sortFeatures = (features) => {
    const points = features.filter((f) => f.geometry.type !== "Polygon");
    const polygons = features.filter((f) => f.geometry.type === "Polygon");

    return [points, polygons];
  };
  updateMapFeatures = async () => {
    const newFeatures = {};

    for (const feature of this.pointsRegions) {
      const props = feature.properties;
      const isCluster = true === props.cluster;
      const id = isCluster ? feature.id : props.map_id;
      const coordinates = feature.geometry?.coordinates ?? [];
      const itemType = isCluster
      ? "cluster"
      : feature.geometry.type === "Point"
      ? "point"
      : "polygon";
      
      isCluster && console.log({ props });

      let mapFeature = this.features[id];

      if (!mapFeature) {
        const items = isCluster ? await this.getClusterItems(id) : [];

        switch (itemType) {
          case "cluster":
            mapFeature = new Cluster(
              id,
              coordinates,
              items,
              this.getCounts(items),
              this.hashKey,
              this.map,
              this.onEnter,
              this.onLeave
            );
            break;
          case "point":
            mapFeature = new Marker(
              id,
              coordinates,
              props,
              this.hashKey,
              this.map,
              this.onEnter,
              this.onLeave
            );
            break;
          case "polygon":
            mapFeature = new Polygon(
              id,
              feature.id,
              coordinates,
              props,
              this.hashKey,
              this.map,
              this.onRegionClickOut
            );
            break;
        }

        this.features[id] = mapFeature;
      }

      newFeatures[id] = mapFeature;

      !this.featuresOnScreen[id] && mapFeature.addTo(this.map);
    }

    for (const country of this.fgCountryIds) {
      const coordinates = this.countryCentroids[country];
      let mapFeature = this.features[country];

      if (!mapFeature) {
        const { counts } = this.fgCountriesData[country];
        const items = this.fgCountriesData[country].items ?? [];

        mapFeature = new CountryCluster(
          country,
          coordinates,
          this.hashKey,
          items,
          counts,
          this.map,
          this.onCountryClickOut
        );

        this.features[country] = mapFeature;
      }

      newFeatures[country] = mapFeature;
      !this.featuresOnScreen[country] && mapFeature.addTo(this.map);
    }

    for (const id in this.featuresOnScreen) {
      if (newFeatures[id]) continue;
      this.featuresOnScreen[id].remove();
    }

    this.featuresOnScreen = newFeatures;
  };
  getCountries = (features) => {
    const countries = features.reduce(
      (carry, current) => [
        ...carry,
        ...(current?.properties?.countries ? current.properties.countries : []),
      ],
      []
    );
    return [...new Set(countries)].sort();
  };
  getBoundsFromFeatures = (features = []) => {
    if (!this.map) return;

    const bounds = new mapboxgl.LngLatBounds();

    if (!features) return bounds;

    for (const feature of features) {
      const geometry = 'country' !== feature.properties.meta_type
        ? feature.geometry
        : countryPolys[feature.properties.iso_a3] ?? null;
        // : countryBoundingBoxes[feature.properties.iso_a3] ?? null;

      geometry && this.addGeometryToBounds(geometry, bounds);
    }

    return bounds;
  };
  addGeometryToBounds = (geometry, bounds = new mapboxgl.LngLatBounds()) => {
    if (geometry.type === "Point") {
      bounds.extend(geometry.coordinates);
    } else if (geometry.type === "MultiPoint") {
      geometry.coordinates.forEach((coord) => bounds.extend(coord));
    } else if (geometry.type === "LineString") {
      geometry.coordinates.forEach((coord) => bounds.extend(coord));
    } else if (geometry.type === "MultiLineString") {
      geometry.coordinates.forEach((line) => {
        line.forEach((coord) => bounds.extend(coord));
      });
    } else if (geometry.type === "Polygon") {
      geometry.coordinates.forEach((coord) => bounds.extend(coord[0]));
    } else if (geometry.type === "MultiPolygon") {
      geometry.coordinates.forEach((polygon) => {
        polygon[0].forEach((coord) => bounds.extend(coord));
      });
    }
    return bounds;
  };
  getClusterItems = (id, count) => {
    return new Promise((res, rej) => {
      this.map
        .getSource(this.pointsSourceId)
        .getClusterLeaves(id, count, 0, (err, features) => {
          if (err) {
            console.error(err.message + ` (${id})`);
            return [];
          }

          const unique = [];

          return res(
            features
              .map((f) => f.properties)
              .filter((f) => {
                const isUnique = !unique.includes(f.item_id);
                isUnique && unique.push(f.item_id);
                return isUnique;
              })
          );
        });
    });
  };
  onMarkerEnter = (e) => {
    const mapId = e.features[0].mapId;
    const marker = this.features[mapId];
    if (!marker) return;

    this.hoverMarker = marker;
    this.hoverMarker.onEnter(e);
  };
  onPolygonEnter = (hoverPolygon) => {
    this.hoveredPolygonFeature = hoverPolygon;
  };
  onPolygonLeave = (e) => {
    const target = e?.relatedTarget ?? null;
    if (
      target &&
      (!this.hoveredPolygonFeature ||
        this.hoveredPolygonFeature.contains(target))
    )
      return;

    this.setPolygonHover(
      this.hoveredPolygonMapId,
      this.hoveredPolygonId,
      false,
      e?.originalEvent ?? null
    );
    this.map.getCanvas().style.cursor = "";
  };
  fitToBounds = (bounds = this.bounds) => {
    const isPoint =
      bounds.getNorthWest().toString() === bounds.getSouthEast().toString();

    // need to include center when using non mercator projections
    // otherwise it won't work properly
    // this is a known bug with mapbox, check for updates
    // https://github.com/mapbox/mapbox-gl-js/issues/12885
    const center = bounds.getCenter();

    !isPoint
      ? this.map.fitBounds(bounds, {
          center,
        })
      : this.map.flyTo({
          center,
          zoom: window.innerWidth < breakPoints.lg ? 2 : 4,
        });
  };
  fitToFeatures = (features) => {
    const bounds = this.getBoundsFromFeatures(features)
    this.fitToBounds(bounds)
  };
  getCounts = (items) => {
    return {
      members: items.filter((f) => (f.properties?.type ?? f.type) === "member")
        .length,
      projects: items.filter(
        (f) => (f.properties?.type ?? f.type) === "project"
      ).length,
      resources: items.filter(
        (f) => (f.properties?.type ?? f.type) === "resource"
      ).length,
      fellows: items.filter((f) => (f.properties?.type ?? f.type) === "fellow")
        .length,
    };
  };
  onResultsUpdate = (results) => {
    this.closePopups();
    
    this.data.features = results;
    
    this.fgCountryIds = this.getUniqueCountryIds(this.getFgCountries(results));
    this.bgCountryIds = this.getUniqueCountryIds(this.getBgCountries(results));
    
    this.fgCountriesData = this.getFgCountriesData(this.fgCountryIds);

    this.pointsRegions = [
      ...results.filter(
        (f) => f.geometry.type === "Point" && f.properties.meta_type !== 'country'
      ),
      ...results.filter(
        (f) => f.geometry.type !== "Point" && f.properties.meta_type !== 'country'
      ),
    ]

    this.bounds = this.getBoundsFromFeatures(results);

    if (!this.sourcesLoaded || !this.map) {
      return;
    }
    
    this.map?.getSource(this.srcIds.points).setData({
      type: "FeatureCollection",
      features: results.filter(
        (f) => f.geometry.type === "Point" && f.properties.meta_type !== 'country'
      ),
    });
    this.map?.getSource(this.srcIds.regions).setData({
      type: "FeatureCollection",
      features: results.filter(
        (f) => f.geometry.type !== "Point" && f.properties.meta_type !== 'country'
      ),
    });


    for (const feature of Object.values(this.features)) {
      feature.remove();
    }
    this.features = {};
    this.featuresOnScreen = {};

    this.updateCountryFilters();

    this.fitToBounds()
  };
  closePopups = () => {
    Object.values(this.features).forEach(
      (f) => ((f.close ?? f.onLeave)(), f?.popup?.remove())
    );
    this.clickFeature = null;
    this.hoverFeature = null;
    this.hoverFeatureId = null;
    this.hoverMapId = null;
    this.hoverSource = null;
  };
  setCursor = (cursor = "default") => {
    if (isEmpty(cursor)) {
      return this.map?.getCanvas().style.removeProperty('cursor');
    }
    this.map.getCanvas().style.cursor = cursor;
  };
}

class ScrollListener {
  constructor(topTriggerEl, bottomTriggerEl, mapEl) {
    this.topTriggerEl = topTriggerEl;
    this.bottomTriggerEl = bottomTriggerEl;
    this.mapEl = mapEl;

    this.bottomTriggerTop = 0;
    this.bottomTriggerBottom = 0;

    this.prevY = null;
    this.dir = 1; // -1 up, 1 down

    this.scrollListener = null;

    this.resizeObserver = new ResizeObserver(this.onResize);
    this.intersectionObserver = new IntersectionObserver(this.onIntersect, {
      threshold: [0, 0.0001, 0.1, 0.9, 0.9999, 1],
    });
    if (!topTriggerEl || !bottomTriggerEl || !mapEl) return;
    this.init();
  }
  init = () => {
    this.topTriggerEl.dataset.trigger = -1;
    this.bottomTriggerEl.dataset.trigger = 1;
    this.topTriggerEl.setAttribute("aria-hidden", true);
    this.bottomTriggerEl.setAttribute("aria-hidden", true);
    this.resizeObserver.observe(this.topTriggerEl);
  };
  onResize = () => {
    const { innerWidth } = window;
    const aspectRatio = getWindowAspectRatio();

    if (innerWidth < breakPoints.lg || aspectRatio === "portrait") {
      this.bottomTriggerTop = this.getItemTop(this.bottomTriggerEl);
      this.bottomTriggerBottom = this.getItemBottom(this.bottomTriggerEl);
      this.intersectionObserver.observe(this.topTriggerEl);
      this.intersectionObserver.observe(this.bottomTriggerEl);
    } else {
      this.intersectionObserver.disconnect();
      this.mapEl.classList.remove("stuck");
      this.mapEl.style.removeProperty("transform");
      this.scrollListener &&
        (window.removeEventListener("scroll", this.onScroll),
        (this.scrollListener = null));
    }
  };
  getItemTop = (el) => {
    return (
      Math.round(el.getBoundingClientRect().top + window.scrollY) -
      window.innerHeight
    );
  };
  getItemBottom = (el) => {
    return (
      Math.round(el.getBoundingClientRect().bottom + window.scrollY) -
      window.innerHeight
    );
  };
  getMapOffset = () => {
    const { scrollY } = window;
    return this.clamp(
      0,
      Math.round(scrollY - this.bottomTriggerTop),
      this.bottomTriggerBottom
    );
  };
  clamp = (min, value, max) => {
    return Math.min(max, Math.max(min, value));
  };
  onScroll = () => {
    const offsetY = this.getMapOffset();
    this.mapEl.style.transform = `translateY(${-offsetY}px)`;
  };
  onIntersect = (entries) => {
    const entry = entries.pop();
    const {
      target,
      intersectionRatio,
      boundingClientRect: { top, bottom },
    } = entry;
    const vH = window.innerHeight;
    const trigger = target.dataset.trigger;

    switch (trigger) {
      case "-1": // top
        if (top >= 0 && top < vH && bottom > 0) {
          this.mapEl.classList.add("stuck");
          break;
        } else if (top >= vH) {
          this.mapEl.classList.remove("stuck");
          break;
        }
      case "1": // bottom
        if (intersectionRatio > 0) {
          this.mapEl.classList.add("stuck");
          if (top <= vH && bottom > vH) {
            !this.scrollListener &&
              (this.scrollListener = window.addEventListener(
                "scroll",
                this.onScroll
              ));
            break;
          }
        } else {
          this.scrollListener &&
            (window.removeEventListener("scroll", this.onScroll),
            (this.scrollListener = null));
          break;
        }
    }
  };
  reset = () => {
    this.intersectionObserver.disconnect();
  };
  areConditionsMet = (conditions, values) => {
    const compareFunctions = {
      ">": this.isGreaterThan,
      "<": this.isLesserThan,
      "=": this.isEqualTo,
      "><": this.isBetween,
    };

    const action = compareFunctions[conditions[0]] ?? null;
    const value = values[conditions[1]] ?? null;
    const comparisonValues = conditions[2] ?? null;

    if (!action || !value || !comparisonValues) return false;

    return action(...comparisonValues, value);
  };
  isGreaterThan = (a, value) => {
    return value > a;
  };
  isLesserThan = (a, value) => {
    return value < a;
  };
  isEqualTo = (a, value) => {
    return a.toString() === value.toString();
  };
  isBetween = (a, b, value) => {
    return a < value && b > value;
  };
}

function getWindowAspectRatio() {
  const { innerWidth, innerHeight } = window;
  const aspectRatio = innerWidth / innerHeight > 1 ? "landscape" : "portrait";
  return aspectRatio;
}

export default CoalitionMap;
