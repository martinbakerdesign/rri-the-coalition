import isEmpty from "is-empty";
import Filter from "./Filter";
import SearchQuery from "./SearchQuery";

export class DirectorySearch {
  constructor(el, data = [], onChangeCallback) {
    this.el = el;

    this.data = data.features;

    this.query = new SearchQuery(
      el.querySelector("input"),
      this.onChange.bind(this)
    );
    this.filters = [...el.querySelectorAll(".filter")].map(
      (filter) => new Filter(filter, this.onChange.bind(this))
    );

    this.resultsEl = el.querySelector("#directory__results");

    this.results = data;

    this.onChangeCallback =
      onChangeCallback &&
      debounce((results) => {
        onChangeCallback(results);
      }, 200);

    el.addEventListener("submit", this.onSubmit);

    // this.onChange()
  }
  onChange = () => {
    const query = this.query.getValue().toLowerCase();
    const filterValues = this.filters
      .map((filter) => [filter.taxonomy, filter.getValue(true)])
      .filter((filter) => filter[1] !== false);

    this.results =
      !query.length && !filterValues.length
        ? this.data.sort((a, b) =>
            a.properties.name < b.properties.name ? -1 : 1
          )
        : this.data
            .filter(this.applyQuery(query))
            .filter((item) => this.applyFilters(filterValues, item))
            .sort((a, b) =>
              a?.properties?.name < b?.properties?.name ? -1 : 1
            );

    const listResults = this.filterListResults(this.results);

    this.onChangeCallback &&
      this.onChangeCallback({ listResults, mapResults: [...this.results] });
  };
  filterListResults = (results) => {
    const uniqueResults = {};

    for (const result of results) {
      if (Object.keys(uniqueResults).includes(result.properties.item_id))
        continue;
      uniqueResults[result.properties.item_id] = result;
    }
    const listResults = Object.values(uniqueResults).map(
      (f) => f.properties.item_id
    );

    return listResults;
  };
  applyQuery = (query) => {
    return (item) => {
      return (
        isEmpty(query) ||
        (item.properties?.name ?? "")
          .toLowerCase()
          .includes(query.toLowerCase())
      );
    };
  };
  applyFilters = (filters, item) => {
    return filters.reduce(
      (carry, [key, term_ids]) =>
        carry && this.applyFilter(key, term_ids, item),
      true
    );
  };
  applyFilter = (key, term_ids, item) => {
    const value = item.properties[key];

    if (isEmpty(term_ids)) return isEmpty(value);

    if (isEmpty(value)) return false;

    if (Array.isArray(value)) {
      const match = term_ids.reduce(
        (carry, current) => carry && value.includes(current),
        true
      );

      return match;
    }

    // only applicable to types
    return term_ids.includes(value);
  };
  reduceFilterMatches = (filterTermIds) => {
    return (carry, term) => {
      return carry && filterTermIds.includes(+term.id);
    };
  };
  onSubmit = (e) => {
    e.preventDefault();
    e.stopPropagation();

    this.onChange();
  };
  reset = () => {
    this.query.reset();
    this.filters.forEach((f) => f.reset());
    this.results = this.data;
  };
}

export class DirectoryResults {
  constructor(el) {
    this.el = el;
    this.noResults = el.querySelector("#directory__no-results");

    this.results = [...el.querySelectorAll(".directory__result")];
    this.groups = [...el.querySelectorAll(".directory__result-group")];

    this.directorySearch = null;
  }
  onResultsChange = (results = []) => {
    this.updateDom(results);
  };
  updateDom = (results = []) => {
    const showAll = results == null;
    const hasResults = showAll || results.length > 0;

    for (const result of this.results) {
      const isInResults = showAll || this.findInResults(result, results);

      this.toggleResult(result, isInResults);
    }
    this.groups.forEach(this.toggleGroup);

    this.noResults.hidden = hasResults;
    this.noResults.setAttribute("aria-hidden", hasResults);
  };
  findInResults = (result, results) => {
    const itemId = result.dataset.itemId;

    return results.includes(itemId);
  };
  toggleResult = (result, visible) => {
    result.hidden = !visible;
    result.setAttribute("aria-hidden", !visible);
    result.querySelector("a").tabIndex = visible ? 0 : -1;
  };
  toggleGroup = (groupEl) => {
    const visibleResults = [
      ...groupEl.querySelectorAll(".directory__result:not([hidden])"),
    ].length;
    const groupVisible = visibleResults > 0;

    groupEl.hidden = !groupVisible;
    groupEl.setAttribute("aria-hidden", !groupVisible);
    groupEl.tabIndex = groupVisible ? 0 : -1;
  };
}

export default DirectorySearch;

function debounce(func, timeout = 300) {
  let timer;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => {
      func.apply(this, args);
    }, timeout);
  };
}
