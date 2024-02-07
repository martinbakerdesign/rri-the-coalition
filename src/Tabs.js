import MapItemSelect from "./MapItemSelect";
import getRelativeUrl from "./getRelativeUrl";
import getURL from "./getURL";
import replaceState from "./replaceState";
import MapboxMap from './CoalitionMap';
import toggleElHidden from "./toggleElHidden";
import breakPoints from "./breakPoints";
import isEmpty from "is-empty";

const collaboratorTypeId = jsonData?.memberTypes?.find(type => type.slug === 'collaborator')?.term_id ?? null;

export class Tabs {
  constructor(containerEl, map, search) {
    this.dom = {
      container: containerEl,
      tabList: containerEl.querySelector('[role="tablist"]'),
    };

    this.dom.tabs = [...this.dom.tabList.querySelectorAll('[role="tab"]')];
    this.dom.tabPanels = this.dom.tabs.map((tab) =>
      document.querySelector(`#${tab.getAttribute("aria-controls")}`)
    );

    this.tabPanels = this.dom.tabPanels.map(
      (tabPanelEl) => new TabPanel(tabPanelEl, this, map, search)
    );

    this.hashKeys = this.dom.tabs.map((tab) =>
      tab.getAttribute("href").replace("#", "")
    );

    this.panelIds = this.dom.tabPanels.map((panel) => panel.id);

    this.dom.tabs.forEach((tab, i) => (tab.dataset.index = i));
    this.dom.tabPanels.forEach((panel, i) => (panel.dataset.index = i));

    this.focusedTab = null;

    this.paddingX = 0;

    this.firstTab = 0;
    this.lastTab = this.dom.tabs.length - 1;

    this.hashKey = '';
    this.searchParams = {focus: null, id: null};
    this.searchParamsString = '';

    this.map = map;

    this.selectedIndex = parseInt(
      this.dom.tabs.find((tab) => tab.getAttribute("aria-selected") === "true")
        ?.dataset?.index ?? 0
    );
    this.prevSelected = this.selectedIndex;

    this.tab = this.dom.tabs[this.selectedIndex];
    this.tabPanel = this.tabPanels[this.selectedIndex];
    this.prevTabPanel = null;

    this.tabPanel.open = true;

    this.searchActive = false;

    this.resizeObserver = new ResizeObserver(this.onResize);
    
    window.addEventListener("load", () => {
      this.addEventListeners();
      this.updateDom(this.selectedIndex);
    });
  }
  onHashChange = (hashKey, searchParams) => {
    if (hashKey === this.hashKey && JSON.stringify(searchParams) === JSON.stringify(this.searchParams)) return;

    const index = this.hashKeys.indexOf(hashKey);

    if (index < 0) return;

    this.hashKey = hashKey;

    // clean up previous tab
    this.prevTabPanel &&
      this.tabPanel !== this.prevTabPanel &&
      this.prevTabPanel.reset();

    this.setSelectedIndex(index);
    this.onSearchParamsChange(searchParams, true);
  };
  onSearchParamsChange = (searchParams, hashChanged = false) => {
    if (JSON.stringify(searchParams) === JSON.stringify(this.searchParams) && !hashChanged) return;

    this.searchParams = searchParams;

    this.tabPanel.onParamsChange(searchParams);
  };
  updateSearchParams = (searchParams) => {
    this.searchParams = searchParams;
  }
  setSelectedIndex = (selectedIndex = this.selectedIndex) => {
    this.prevTabPanel = this.tabPanel;
    this.tabPanel && (this.tabPanel.open = false);
    this.selectedIndex = selectedIndex;

    this.tab = this.dom.tabs.find((tab) => {
      return parseInt(tab.dataset.index) === selectedIndex;
    });
    this.tabPanel = this.tabPanels[selectedIndex];
    this.tabPanel.open = true;

    this.updateDom(selectedIndex, this.prevSelected);

    this.prevSelected = this.selectedIndex;
  };
  addEventListeners = () => {
    this.resizeObserver.observe(this.dom.tabs[0]);
    this.dom.tabs.forEach(
      (tab) => (
        tab.addEventListener("click", this.onClick),
        tab.addEventListener("keydown", this.onKeyDown),
        tab.addEventListener("focus", this.onFocusChange)
      )
    );
  };
  removeEventListeners = () => {
    this.dom.tabs.forEach(
      (tab) => (
        tab.removeEventListener("keydown", this.onKeyDown),
        tab.removeEventListener("focus", this.onFocusChange)
      )
    );
  };
  onResize = () => {
    this.dom.tabList.style.removeProperty("width");
    if (window.innerWidth <= breakPoints.md) return;
    this.dom.tabList.style.width = `${this.dom.tabList.clientWidth}px`;
  };
  onClick = (e) => {
    const tab = e.target.closest("[role=tab]");
    if (!tab) return;
    this.setSelectedIndex(parseInt(tab.dataset.index));
  };
  onFocusChange = (e) => {
    const { target, type } = e;
    this.focusedTab = type === "focus" ? target.closest('[role="tab"]') : null;
  };
  onKeyDown = (e) => {
    const { key } = e;
    const delta =
      key === "ArrowLeft"
        ? -1
        : key === "ArrowRight"
        ? 1
        : key === "Home"
        ? -10
        : key === "End"
        ? 10
        : 0;
    this.setFocusedTab(delta);
    if (!["ArrowLeft", "ArrowRight", "Home", "End"].includes(key)) return;
    e.stopPropagation();
    e.preventDefault();
  };
  setFocusedTab(delta) {
    if (delta === 0) return;

    const currentIndex = +(this.focusedTab.dataset?.index ?? 0);
    // const lastTab = this.lastTab + (this.searchActive ? 0 : -1);
    const lastTab = this.lastTab;

    let nextIndex = 0;
    // Home or end
    if (delta === 10) {
      nextIndex = lastTab;
    }
    // +1 - 1
    else if (delta !== -10) {
      nextIndex =
        currentIndex + delta < 0
          ? lastTab
          : (currentIndex + delta) % (lastTab + 1);
    }

    this.focusedTab = this.dom.tabs.find(
      (tab) => +tab.dataset.index === nextIndex
    );
    this.focusedTab?.focus();
  }
  updateDom = (
    selectedIndex = this.selectedIndex,
    prevSelected = this.prevSelected
  ) => {
    let tab;
    const tabs = this.getTabs();
    for (let t = 0; t < tabs.length; t++) {
      tab = tabs.filter((tab) => tab.dataset.index === t.toString())[0];
      if (!tab) continue;

      t === selectedIndex
        ? tab.setAttribute("aria-selected", true)
        : tab.removeAttribute("aria-selected");

      tab.tabIndex = t === selectedIndex ? 0 : -1;

      this.dom.tabPanels[t].hidden = t !== selectedIndex;
    }
        
    return window.innerWidth < breakPoints.md
      ? this.scrollTabIntoView()
      : this.reorderTabs(selectedIndex, prevSelected);
  };
  scrollTabIntoView = () => {
    const tab = this.dom.tabs[this.selectedIndex];
    const left = tab.offsetLeft;
    const scrollWindow = tab.closest('[role=tablist]')
    scrollWindow.scrollLeft = left - 20;
  }
  reorderTabs = (
    selectedIndex = this.selectedIndex,
    prevSelected = this.prevSelected
  ) => {
    if (selectedIndex === prevSelected) return;

    this.removeEventListeners();
    const listEls = [...this.dom.tabList.querySelectorAll("li")];

    const currentTab = this.dom.tabList.querySelector(
      `[role="tab"][data-index="${selectedIndex}"]`
    );

    const currentTabIndex = listEls.indexOf(currentTab.parentElement);

    const prevTabs = listEls.slice(0, currentTabIndex);

    for (const toCopy of prevTabs) {
      this.dom.tabList.appendChild(toCopy.cloneNode(true));
    }

    const paddingLeft = window.innerWidth >= breakPoints.xl
      ? 0
      : (window.innerWidth - document.querySelector('.max-w-content').clientWidth) * 0.5;
    const distanceFromLeft = currentTab.offsetLeft - paddingLeft;

    const allTabs = this.getTabs();

    this.completeTransitions = 0;
    this.totalTabs = allTabs.length;
    this.transitionDelta = prevTabs.length;

    for (let tab of allTabs) {
      tab.parentElement.addEventListener(
        "transitionend",
        this.onTransitionComplete.bind(this)
      );
      tab.parentElement.style.transition = `transform 500ms cubic-bezier(0.8, 0, 0, 1)`;
      tab.parentElement.style.transform = `translateX(-${distanceFromLeft}px)`;
    }
    this.dom.tabList.style.pointerEvents = "none";
  };
  onTransitionComplete = (e) => {
    this.completeTransitions++;

    const liEls = [...this.dom.tabList.querySelectorAll("li")];

    e.target.style.removeProperty("transition");
    e.target.removeEventListener("transitionend", this.onTransitionComplete);

    if (this.completeTransitions === this.totalTabs) {
      this.completeTransitions = 0;
      this.totalTabs = 0;
      let index = 0;

      for (const li of liEls) {
        if (index < this.transitionDelta) {
          li.remove();
        } else {
          li.style.removeProperty("transform");
        }
        index++;
      }

      this.dom.tabList.style.pointerEvents = "auto";
    }

    this.dom.tabs = this.getTabs();

    this.addEventListeners();
  };
  getTabs() {
    return [...this.dom.tabList.querySelectorAll('[role="tab"]')];
  }
}

export class TabPanel {
  constructor(tabPanelEl, parent, map, search) {
    this.tabPanelEl = tabPanelEl;
    this.backButtonEl = tabPanelEl.querySelector(".tab__back");
    this.slideEl = tabPanelEl.querySelector(".tab__slide");
    this.contentEl = tabPanelEl.querySelector(".tab__content");
    this.contentEls = [...tabPanelEl.querySelectorAll(".tab__content > section")];
    this.contentSelectEls = [...tabPanelEl.querySelectorAll(".select")] ?? [];
    this.landingEl = tabPanelEl.querySelector(".tab__landing");

    this.memberFocusEls = [...tabPanelEl.querySelectorAll(".member-focus")];
    this.projectFocusEls = [...tabPanelEl.querySelectorAll(".project-focus")];
    this.fellowFocusEls = [...tabPanelEl.querySelectorAll(".fellow-focus")];

    this.parent = parent;

    this.defaultHref = this.backButtonEl.getAttribute("href");
    this.history = new TabHistory(this.defaultHref);

    this.hashKey = tabPanelEl.id.replace("tab--", "");
    this.isFellows = this.hashKey === "fellows";
    this.isDirectory = this.hashKey === "directory";

    this.open = false;

    this.state = tabPanelEl.dataset.state ?? "landing";

    this.focus = false;
    this.focusId = null;

    this.map = map;
    this.directorySearch = search;

    this.contentIds = this.contentEls.map((el) => el.dataset.id);

    this.contentSelects = this.contentSelectEls.map(select =>
      new MapItemSelect(
        select, null, this.onSelectChange.bind(this)
      )
    );

    this.memberTabs = [...tabPanelEl.querySelectorAll('.member-focus__content')].map(container => new MemberTabs(
      [...container.querySelectorAll('[role=tab]')],
      [...container.querySelectorAll('[role=tabpanel]')],
      container
    ));

    this.backButtonEl.addEventListener("click", this.onBackButtonClick);
  }
  onSelectChange = (itemId) => {
    if (!this.open || !this.focus) return;

    const id = !isNaN(parseInt(itemId.split('--')[1]))
      ? parseInt(itemId.split('--')[1])
      : null
    const focus = id != null ? itemId.split('--')[0] : false;

    
    const hash = new URL(window.location.href).hash;
    const params = new URLSearchParams(focus
      ? `?focus=${focus}&id=${id}`
      : '');
      
      replaceState(getURL(params.toString(), hash))
      this.updateState({focus,id});
      this.history.update()
    this.parent.updateSearchParams({focus,id});
    this.updateBackButtonHref();
    this.updateDom();
    this.updateMap();
  };
  updateBackButtonHref = () => {
    this.backButtonEl.setAttribute(
      "href",
      this.history.getPrevHref()
    );
  };
  onBackButtonClick = () => {
    console.log('onBackButtonClick', this.history.getPrevHref())
    this.history.goBack()
  };
  onParamsChange = (searchParams) => {
    this.history.update()
    this.updateState(searchParams);
    this.updateBackButtonHref();
    this.updateDom();
    this.updateMap();
  };
  onEnter = (searchParams) => {
    this.history.reset()
    this.open = true;

    this.onParamsChange(searchParams);
  };
  updateMap = () => {
    const isFellows = this.hashKey === "fellows";
    const isDirectory = this.hashKey === "directory";

    if (isDirectory) {
      if (['member','fellow','project'].includes(this.focus)) {
        if (this.focusId == null) return;
        const itemId = getItemId(this.focus, this.focusId);
        const items = getFeaturesByItemId(mapData.features, itemId);
        
        if (isEmpty(items)) return;

        this.map.onResultsUpdate(items);

        return;
      }

      this.directorySearch && this.directorySearch.onChange();
      return;
    }

    const results = this.map.srcData.features
      .filter(this.showItemForRoute(isFellows))

    this.map.onResultsUpdate(results);
  };
  showItemForRoute = (isFellows) => {
    return (f) => {
      // has a category value
      const value = f.properties[this.hashKey]
      const hasValue = !isEmpty(value.filter(f => null != f));
      if ("landing" === this.state) {
        return !isFellows
          ? hasValue
          : f.type === "fellow";
      }
  
      // category focus
      if ("category" === this.focus) {
        const hasCategory = value
          .some(term => term && term.toString() === this.focusId.toString());
  
        return ( hasValue && hasCategory );
      }
  
      return f.properties.id.toString() === this.focusId.toString();
    }
  }
  getHref = () => {
    const url = new URL(window.location.href);
    const params = url.searchParams.toString().length
      ? "?" + url.searchParams.toString()
      : "";

    return params + url.hash;
  };
  updateState = (searchParams) => {
    const newFocus = searchParams.focus ?? false;
    const newFocusId = searchParams.id ?? null;

    const itemId = getItemId(newFocus, newFocusId);
    const itemData = getItemsByItemId(directoryData, itemId);
    
    if (newFocus && 'category' !== newFocus && !itemData) {
      replaceState(getURL('',this.hashKey))
    }

    this.state = ["category", "member", "project", "fellow"].includes(newFocus)
      && (newFocus === 'category' || itemData != null)
      ? "focus"
      : "landing";
    this.focus = this.state !== 'landing'
      ? newFocus
      : false;
    this.focusId = this.state !== 'landing'
      ? newFocusId
      : null;
  };
  updateDom = () => {
    toggleElHidden(this.tabPanelEl, !this.open);
    
    // get data of item
    const itemId = getItemId(this.focus, this.focusId);
    const itemData = getItemsByItemId(directoryData, itemId);

    this.tabPanelEl.dataset.state = this.state;

    const isLanding = this.state === "landing";
    const isFocus = this.state === "focus";
    const isCategory = this.focus === "category";

    const backButtonHidden = this.state === "landing";
    toggleElHidden(this.backButtonEl, backButtonHidden);

    const landingHidden = !isLanding;
    toggleElHidden(this.landingEl, landingHidden);

    const contentHidden = !isFocus;
    toggleElHidden(this.contentEl, contentHidden);
    this.contentEls.forEach((el) =>
      toggleElHidden(
        el,
        contentHidden || !(+el.dataset.id === this.focusId && isCategory)
      )
    );

    [...this.memberFocusEls, ...this.projectFocusEls, ...this.fellowFocusEls].forEach(el => toggleElHidden(el, true));

    // update dom content
    switch (this.focus) {
      case "member":
        this.prepareMember(itemData);
        break;
      case "project":
        this.prepareProject(itemData);
        break;
      case "fellow":
        this.prepareFellow(itemData);
        break;
    }
  };
  prepareMember = (itemData) => {
    const classPrefix = 'member-focus'

    const el = this.memberFocusEls[0];

    toggleElHidden(el, true);
    el.style.opacity = 0;

    requestAnimationFrame(() => {
      el.dataset.id = itemData.id;

      const imgEl = el.querySelector(`.${classPrefix}__logo img`)
      imgEl.src = itemData.logo ?? '';
      imgEl.alt = itemData.name;
  
      const abbrevEl = el.querySelector(`.${classPrefix}__abbreviation`);
      abbrevEl.textContent = itemData.abbreviation ?? '';
  
      const establishedEl = el.querySelector(`.${classPrefix}__established`);
      establishedEl.textContent = itemData.established ?? '';
  
      const linkEl = el.querySelector(`.${classPrefix}__website-link`);
      itemData?.url && itemData?.url.length
        ? linkEl.setAttribute('href', itemData.url)
        : toggleElHidden(linkEl, true);

      el.querySelector(`.${classPrefix}__name`).textContent = itemData.name;
  
      const select = this.contentSelects
        .find(select => el.contains(select.el));
      select &&  select.setValue(itemData.id)
  
      const typeEl = el.querySelector(`.${classPrefix}__type`);
      typeEl.textContent = itemData.member_type.name ?? '';
  
      this.setCategoryChips(el.querySelector(`.${classPrefix}__categories`), itemData);

      const {projects, bio, vision, mission, achievements} = itemData;
      const projectsTab = el.querySelector('[id*=tab--projects]');
      projectsTab
        && this.prepareProjectsTab(projectsTab, projects);
      const projectsTabPanel = el.querySelector('[id*=member-focus__projects]');
      projectsTabPanel && this.prepareProjectsTabPanel(projectsTabPanel, projects);

      this.prepareAboutTab(
        el.querySelector('[id*=member-focus__about]'),
        bio && bio.length ? bio : null,
        vision && vision.length ? vision : null,
        mission && mission.length ? mission : null
      );

      this.prepareAchievementsTab(
        el.querySelector('[id*=member-focus__achievements]'),
        achievements
      )

      const memberTabs = this.memberTabs.find(tabs => (el.contains(tabs.containerEl)))
      memberTabs?.updateTabs()

      const tabs = [...el.querySelectorAll('[role=tab]')];
      // this.prepareTabs( el, tabs )
  
      setTimeout(() => {
        toggleElHidden(el, false);
        el.style.opacity = 1;
      }, 100)
    })
  };
  prepareAchievementsTab = (el, achievements) => {
    if (!el) return;
    const achievementsContainer = el.querySelector('.achievements')

    achievementsContainer.innerHTML = '';

    toggleElHidden(el, achievements.length === 0);

    const groupedAchievements = this.groupAchievementsByYear(achievements);

    Object.entries(groupedAchievements).forEach(([year, achievements]) => {
      achievementsContainer.appendChild(this.prepareAchievementsGroup(year, achievements))
    })
  }
  prepareAchievementsGroup = (year, achievements) => {
    const container = document.createElement('li')
    container.className = 'achievement-group'

    const yearEl = document.createElement('div')
    yearEl.className = 'achievement-group__year'
    yearEl.textContent = year;
    container.appendChild(yearEl)

    const itemsContainer = document.createElement('ul')
    itemsContainer.className = 'achievement-group__items'

    achievements.forEach((achievement) => {
      itemsContainer.appendChild(this.prepareAchievementItem(achievement))
    })

    container.appendChild(itemsContainer)

    return container;
  }
  prepareAchievementItem = (achievement) => {
    const item = document.createElement('li')
    item.className = 'achievement'

    const anchor = document.createElement('a');
    if (achievement.url) {
      anchor.setAttribute('href', achievement.url)
      anchor.setAttribute('target', '_blank')
      item.appendChild(anchor)
    }

    const heading = document.createElement('h5')
    heading.textContent = achievement.description;
    achievement.url
      ? anchor.appendChild(heading)
      : item.appendChild(heading);

    return item
  }
  groupAchievementsByYear = (achievements) => {
    const grouped = {}
    const sortedAchievements = achievements.sort((a,b) => a.date > b.date ? -1 : 1)

    for (const achievement of sortedAchievements) {
      const year = new Date(achievement.date).getFullYear();
      if (!grouped[year]) {
        grouped[year] = [];
      }
      grouped[year].push(achievement);
    }
    return grouped;
  }
  prepareProjectsTab = (tab, projects) => {
    if (!tab) return;    
    let chip = tab.querySelector('.count')
    if (!chip) {
      chip = document.querySelector('span');
      chip.className = 'count'
      tab.appendChild(chip)
    }
    chip.textContent = projects.length;

    toggleElHidden(tab, projects.length === 0);
  }
  prepareProjectsTabPanel = (tabPanel, projects) => {
    if (!tabPanel) return;

    toggleElHidden(tabPanel, projects.length === 0);
    
    console.log(tabPanel)

    const list = tabPanel.querySelector('.member__project-cards');

    list.innerHTML = '';

    projects.forEach((project) => {
      const card = this.getProjectCard(project)
      list.appendChild(card);
    })
  }
  getProjectCard = (projectData) => {

      const container = document.createElement('li');
      container.className = 'project-card';
      container.dataset.id = projectData.id;

      const anchor = document.createElement('a');
      anchor.className = 'anchor';
      anchor.setAttribute('href',`?focus=project&id=${projectData.id}#${this.hashKey}`);

      const thumbnailContainer = document.createElement('div')
      thumbnailContainer.className = 'project-card__thumbnail'

      const img = document.createElement('img')
      img.src = projectData.thumbnail;
      img.alt = projectData.name;

      const name = document.createElement('div')
      name.className = 'project-card__name'
      name.textContent = projectData.name;

      container.appendChild(anchor);
      container.appendChild(thumbnailContainer);
      thumbnailContainer.appendChild(img);
      container.appendChild(name);

      return container;
  }
  prepareAboutTab = (el, bio = null, vision = null, mission = null) => {
    if (!el) return;

    toggleElHidden(el,!bio && !vision && !mission);

    const bioEl = el.querySelector('.member-focus__about__content');
    bioEl.textContent = bio;
    toggleElHidden(bioEl,!bio);
    
    const visionEl = el.querySelector('.member-focus__vision__content');
    visionEl.textContent = vision;
    toggleElHidden(visionEl,!vision);
    
    const missionEl = el.querySelector('.member-focus__mission__content');
    missionEl.textContent = mission;
    toggleElHidden(missionEl,!mission);
  }
  prepareProject = (itemData) => {
    const classPrefix = 'project-focus'

    const el = this.projectFocusEls[0];

    toggleElHidden(el, true);
    el.style.opacity = 0;

    requestAnimationFrame(() => {
      el.dataset.id = itemData.id;
  
      el.querySelector(`.${classPrefix}__name`).textContent = itemData.name;

      const select = this.contentSelects
        .find(select => el.contains(select.el));
      select && select.setValue(itemData.id);
  
      const imgEl = el.querySelector(`.${classPrefix}__thumbnail img`)
      imgEl.src = itemData.thumbnail ?? '';
      imgEl.alt = itemData.name;

      const memberEl = el.querySelector(`.${classPrefix}__member`);
      const showMemberLink = itemData.memberId != null && itemData?.memberType?.term_id.toString() !== collaboratorTypeId.toString();
      toggleElHidden(memberEl,!showMemberLink)
      if (showMemberLink) {
        const memberLogoEl = el.querySelector(`.${classPrefix}__member__logo`)
        memberLogoEl.src = itemData.memberLogo ?? '';
        memberLogoEl.alt = itemData.memberName ?? '';
        const memberNameEl = el.querySelector(`.${classPrefix}__member__name`)
        memberNameEl.textContent = itemData.memberName ?? '';
        memberEl.setAttribute('href',`?focus=member&id=${itemData.memberId}#${this.hashKey}`);
      }

      const projectLinkEl = el.querySelector(`.${classPrefix}__website-link`)
      const showProjectLink = itemData.url && itemData.url.length;
      projectLinkEl.setAttribute('href',itemData?.url ?? '');
      toggleElHidden(projectLinkEl,showProjectLink)
  
      this.setCategoryChips(el.querySelector(`.${classPrefix}__categories`), itemData)
  
      const descriptionEl = el.querySelector(`.${classPrefix}__description`);
      descriptionEl.innerHTML = itemData.description;
  
      setTimeout(() => {
        toggleElHidden(el, false);
        el.style.opacity = 1;
      }, 100)
    });
  };
  prepareFellow = (itemData) => {
    const classPrefix = 'fellow-focus'

    const el = this.fellowFocusEls[0];

    toggleElHidden(el, true);
    el.style.opacity = 0;

    requestAnimationFrame(() => {
      el.dataset.id = itemData.id;
  
      el.querySelector(`.${classPrefix}__name`).textContent = itemData.name;
  
      const select = this.contentSelects
        .find(select => el.contains(select.el));
      select &&  select.setValue(itemData.id)
  
      const imgEl = el.querySelector(`.${classPrefix}__thumbnail img`)
      imgEl.src = itemData.thumbnail ?? '';
      imgEl.alt = itemData.name;
  
      this.setCategoryChips(el.querySelector(`.${classPrefix}__categories`), itemData)
  
      const bioEl = el.querySelector(`.${classPrefix}__bio`);
      bioEl.innerHTML = itemData.bio;
  
      setTimeout(() => {
        toggleElHidden(el, false);
        el.style.opacity = 1;
      }, 100)
    });
  };
  setCategoryChips = (el, itemData) => {
    el.innerHTML = '';

    const categories = this.getItemCategories(itemData);
    
    for (const category of categories) {
      el.appendChild(this.getCategoryChip(category));
    }
  }
  getItemCategories = (itemData) => {
    return [
      ...(itemData?.topics ?? []),
      ...(itemData?.expertise ?? [])
    ].sort((a,b) => a.name < b.name ? -1 : 1);
  }
  getCategoryChip = (category) => {
    if (!category || !category.name || !category.name.length) return null;

    const chip = document.createElement('li');

    chip.className = 'category-chip'
    
    const button = document.createElement('button')
    button.type = 'button'
    button.textContent = category.name;
    button.dataset.id = category.term_id ?? category.id ?? category.name;
    button.dataset.taxonomy = category.taxonomy ?? '';

    chip.appendChild(button)

    return chip;
  }
  reset = () => {
    this.state = "landing";
    this.focus = false;
    this.focusId = null;
    this.open = false;
    this.history.reset()

    this.isDirectory && this.directorySearch.reset();

    this.updateDom();
  };
}

class TabHistory {
  constructor (defaultHref) {
    this.defaultHref = defaultHref;
    this.history = [defaultHref];
  }
  update = () => {
    const newURL = getRelativeHref(window.location.href);
    if (this.history[this.history.length - 1] === newURL) return;
    this.history.push(newURL);
  };
  getPrevHref = () => {
    return this.history[this.history.length - 2] ?? this.defaultHref;
  }
  goBack = () => {
    this.history = this.history.slice(0, -2);
    return this.history;
  }
  reset = () => {
    this.history = [this.defaultHref];
  }
}

export class PartnersSection {
  constructor(sectionEl, mapData) {
    this.sectionEl = sectionEl;
    this.backButtonEl = sectionEl.querySelector(".tab__back");
    this.landingEl = sectionEl.querySelector(".tab__landing");
    this.slideEl = sectionEl.querySelector(".tab__slide");
    this.contentEl = sectionEl.querySelector(".tab__content");
    this.contentSelectEls = [...sectionEl.querySelectorAll(".select")] ?? [];
    this.cardsEl = sectionEl.querySelector('.partners__list');
    this.mapContainerEl = sectionEl.querySelector('#partners__map').parentElement;

    this.memberFocusEls = [...sectionEl.querySelectorAll(".member-focus")];
    this.projectFocusEls = [...sectionEl.querySelectorAll(".project-focus")];
    this.fellowFocusEls = [...sectionEl.querySelectorAll(".fellow-focus")];

    this.defaultHref = '#partners';
    this.hashKey = 'partners'
    this.history = new TabHistory(this.defaultHref);

    this.state = sectionEl.dataset.state ?? "landing";

    this.focus = false;
    this.focusId = null;

    this.map = new MapboxMap(sectionEl.querySelector('#partners__map'), mapData);

    this.contentSelects = this.contentSelectEls.map(select =>
      new MapItemSelect(
        select, null, this.onSelectChange.bind(this)
      )
    );

    this.memberTabs = [...sectionEl.querySelectorAll('.member-focus__content')].map(container => new MemberTabs(
      [...container.querySelectorAll('[role=tab]')],
      [...container.querySelectorAll('[role=tabpanel]')],
      container
    ));

    this.backButtonEl.addEventListener("click", this.onBackButtonClick);
  }
  onSelectChange = (itemId) => {
    if (!this.focus) return;

    const id = !isNaN(parseInt(itemId.split('--')[1]))
      ? parseInt(itemId.split('--')[1])
      : null
    const focus = id != null ? itemId.split('--')[0] : false;

    this.history.update()
    const hash = new URL(window.location.href).hash;
    const params = new URLSearchParams(focus
        ? `?focus=${focus}&id=${id}`
        : '');

    replaceState(getURL(params.toString(), hash))
    this.updateState({focus,id,});
    this.updateBackButtonHref();
    this.updateDom();
    this.updateMap();
  };
  updateBackButtonHref = () => {
    this.backButtonEl.setAttribute(
      "href",
      this.history.getPrevHref()
    );
  };
  onBackButtonClick = () => {
    this.history.goBack()
  };
  onParamsChange = (searchParams) => {
    this.history.update()
    this.updateState(searchParams);
    this.updateBackButtonHref();
    this.updateDom();
    this.updateMap();
  };
  onEnter = (searchParams) => {
    this.history.reset()
    this.open = true;

    this.onParamsChange(searchParams);
  };
  updateMap = () => {
    const mapIds = this.map.data.features
      .map((f) => f.properties)
      .filter((f) => {
        // has a category value
        if ("landing" === this.state) {
          return f[this.hashKey] && f[this.hashKey].length;
        }
        return f.id.toString() === this.focusId.toString();
      })
      .map((f) => f.mapId);

    this.map.updateFilters(mapIds);
  };
  getHref = () => {
    const url = new URL(window.location.href);
    const params = url.searchParams.toString().length
      ? "?" + url.searchParams.toString()
      : "";

    return params + url.hash;
  };
  updateState = (searchParams) => {
    const newFocus = searchParams.focus ?? false;
    const newFocusId = searchParams.id ?? null;

    const itemId = getItemId(newFocus, newFocusId);
    const itemData = getItemsByItemId(directoryData, itemId);
    
    if (newFocus && !itemData) {
      replaceState(getURL('',this.hashKey))
    }

    this.state = ["member", "project"].includes(newFocus) && itemData != null
      ? "focus"
      : "landing";
    this.focus = this.state !== 'landing'
      ? newFocus
      : false;
    this.focusId = this.state !== 'landing'
      ? newFocusId
      : null;
  };
  updateDom = () => {
    // get data of item
    const itemId = getItemId(this.focus, this.focusId);
    const itemData = getItemsByItemId(directoryData, itemId);

    this.sectionEl.dataset.state = this.state;

    const isLanding = this.state === "landing";
    const isFocus = this.state === "focus";

    const backButtonHidden = this.state === "landing";
    toggleElHidden(this.backButtonEl, backButtonHidden);

    const landingHidden = !isLanding;
    toggleElHidden(this.landingEl, landingHidden);
    toggleElHidden(this.cardsEl, landingHidden);
    toggleElHidden(this.contentEl, !landingHidden);
    toggleElHidden(this.mapContainerEl, !landingHidden);

    [...this.memberFocusEls, ...this.projectFocusEls, ...this.fellowFocusEls].forEach(el => toggleElHidden(el, true));

    // update dom content
    switch (this.focus) {
      case "member":
        this.prepareMember(itemData);
        break;
      case "project":
        this.prepareProject(itemData);
        break;
    }
  };
  prepareMember = (itemData) => {
    const classPrefix = 'member-focus'

    const el = this.memberFocusEls[0];

    toggleElHidden(el, true);
    el.style.opacity = 0;

    requestAnimationFrame(() => {
      el.dataset.id = itemData.id;

      const imgEl = el.querySelector(`.${classPrefix}__logo img`)
      imgEl.src = itemData.logo ?? '';
      imgEl.alt = itemData.name;
  
      const abbrevEl = el.querySelector(`.${classPrefix}__abbreviation`);
      abbrevEl.textContent = itemData.abbreviation ?? '';
  
      const establishedEl = el.querySelector(`.${classPrefix}__established`);
      establishedEl.textContent = itemData.established ?? '';
  
      const linkEl = el.querySelector(`.${classPrefix}__website-link`);
      itemData?.url && itemData?.url.length
        ? linkEl.setAttribute('href', itemData.url)
        : toggleElHidden(linkEl, true);

      el.querySelector(`.${classPrefix}__name`).textContent = itemData.name;
  
      const select = this.contentSelects
        .find(select => el.contains(select.el));
      select &&  select.setValue(itemData.id)
  
      const typeEl = el.querySelector(`.${classPrefix}__type`);
      typeEl.textContent = itemData.member_type.name ?? '';
  
      this.setCategoryChips(el.querySelector(`.${classPrefix}__categories`), itemData);

      const {projects, bio, vision, mission, achievements} = itemData;
      const projectsTab = el.querySelector('[id*=tab--projects]');
      projectsTab
        && this.prepareProjectsTab(projectsTab, projects);
      const projectsTabPanel = el.querySelector('[id*=member-focus__projects]');
      projectsTabPanel && this.prepareProjectsTabPanel(projectsTabPanel, projects);

      this.prepareAboutTab(
        el.querySelector('[id*=member-focus__about]'),
        bio && bio.length ? bio : null,
        vision && vision.length ? vision : null,
        mission && mission.length ? mission : null
      );

      this.prepareAchievementsTab(
        el.querySelector('[id*=member-focus__achievements]'),
        achievements
      )

      const memberTabs = this.memberTabs.find(tabs => (el.contains(tabs.containerEl)))
      memberTabs?.updateTabs()

      const tabs = [...el.querySelectorAll('[role=tab]')];
      // this.prepareTabs( el, tabs )
  
      setTimeout(() => {
        toggleElHidden(el, false);
        el.style.opacity = 1;
      }, 100)
    })
  };
  prepareAchievementsTab = (el, achievements) => {
    if (!el) return;
    const achievementsContainer = el.querySelector('.achievements')

    achievementsContainer.innerHTML = '';

    toggleElHidden(el, achievements.length === 0);

    const groupedAchievements = this.groupAchievementsByYear(achievements);

    Object.entries(groupedAchievements).forEach(([year, achievements]) => {
      achievementsContainer.appendChild(this.prepareAchievementsGroup(year, achievements))
    })
  }
  prepareAchievementsGroup = (year, achievements) => {
    const container = document.createElement('li')
    container.className = 'achievement-group'

    const yearEl = document.createElement('div')
    yearEl.className = 'achievement-group__year'
    yearEl.textContent = year;
    container.appendChild(yearEl)

    const itemsContainer = document.createElement('ul')
    itemsContainer.className = 'achievement-group__items'

    achievements.forEach((achievement) => {
      itemsContainer.appendChild(this.prepareAchievementItem(achievement))
    })

    container.appendChild(itemsContainer)

    return container;
  }
  prepareAchievementItem = (achievement) => {
    const item = document.createElement('li')
    item.className = 'achievement'

    const anchor = document.createElement('a');
    if (achievement.url) {
      anchor.setAttribute('href', achievement.url)
      anchor.setAttribute('target', '_blank')
      item.appendChild(anchor)
    }

    const heading = document.createElement('h5')
    heading.textContent = achievement.description;
    achievement.url
      ? anchor.appendChild(heading)
      : item.appendChild(heading);

    return item
  }
  groupAchievementsByYear = (achievements) => {
    const grouped = {}
    const sortedAchievements = achievements.sort((a,b) => a.date > b.date ? -1 : 1)

    for (const achievement of sortedAchievements) {
      const year = new Date(achievement.date).getFullYear();
      if (!grouped[year]) {
        grouped[year] = [];
      }
      grouped[year].push(achievement);
    }
    return grouped;
  }
  prepareProjectsTab = (tab, projects) => {
    if (!tab) return;    
    let chip = tab.querySelector('.count')
    if (!chip) {
      chip = document.querySelector('span');
      chip.className = 'count'
      tab.appendChild(chip)
    }
    chip.textContent = projects.length;

    toggleElHidden(tab, projects.length === 0);
  }
  prepareProjectsTabPanel = (tabPanel, projects) => {
    if (!tabPanel) return;

    toggleElHidden(tabPanel, projects.length === 0);

    tabPanel.innerHTML = '';

    projects.forEach((project) => {
      const card = this.getProjectCard(project)
      tabPanel.appendChild(card);
    })
  }
  getProjectCard = (projectData) => {

      const container = document.createElement('li');
      container.className = 'project-card';
      container.dataset.id = projectData.id;

      const anchor = document.createElement('a');
      anchor.className = 'anchor';
      anchor.setAttribute('href',`?focus=project&id=${projectData.id}#${this.hashKey}`);

      const thumbnailContainer = document.createElement('div')
      thumbnailContainer.className = 'project-card__thumbnail'

      const img = document.createElement('img')
      img.src = projectData.thumbnail;
      img.alt = projectData.name;

      const name = document.createElement('div')
      name.className = 'project-card__name'
      name.textContent = projectData.name;

      container.appendChild(anchor);
      container.appendChild(thumbnailContainer);
      thumbnailContainer.appendChild(img);
      container.appendChild(name);

      return container;
  }
  prepareAboutTab = (el, bio = null, vision = null, mission = null) => {
    if (!el) return;

    toggleElHidden(el,!bio && !vision && !mission);

    const bioEl = el.querySelector('.member-focus__about__content');
    bioEl.textContent = bio;
    toggleElHidden(bioEl,!bio);
    
    const visionEl = el.querySelector('.member-focus__vision__content');
    visionEl.textContent = vision;
    toggleElHidden(visionEl,!vision);
    
    const missionEl = el.querySelector('.member-focus__mission__content');
    missionEl.textContent = mission;
    toggleElHidden(missionEl,!mission);
  }
  prepareProject = (itemData) => {
    const classPrefix = 'project-focus'

    const el = this.projectFocusEls[0];

    toggleElHidden(el, true);
    el.style.opacity = 0;

    requestAnimationFrame(() => {
      el.dataset.id = itemData.id;
  
      el.querySelector(`.${classPrefix}__name`).textContent = itemData.name;

      const select = this.contentSelects
        .find(select => el.contains(select.el));
      select && select.setValue(itemData.id);
  
      const imgEl = el.querySelector(`.${classPrefix}__thumbnail img`)
      imgEl.src = itemData.thumbnail ?? '';
      imgEl.alt = itemData.name;

      const memberEl = el.querySelector(`.${classPrefix}__member`);
      const showMemberLink = itemData.memberId != null && itemData?.memberType?.term_id.toString() !== collaboratorTypeId.toString();
      toggleElHidden(memberEl,!showMemberLink)
      if (showMemberLink) {
        const memberLogoEl = el.querySelector(`.${classPrefix}__member__logo`)
        memberLogoEl.src = itemData.memberLogo ?? '';
        memberLogoEl.alt = itemData.memberName ?? '';
        const memberNameEl = el.querySelector(`.${classPrefix}__member__name`)
        memberNameEl.textContent = itemData.memberName ?? '';
        memberEl.setAttribute('href',`?focus=member&id=${itemData.memberId}#${this.hashKey}`);
      }

      const projectLinkEl = el.querySelector(`.${classPrefix}__website-link`)
      const showProjectLink = itemData.url && itemData.url.length;
      projectLinkEl.setAttribute('href',itemData?.url ?? '');
      toggleElHidden(projectLinkEl,showProjectLink)
  
      this.setCategoryChips(el.querySelector(`.${classPrefix}__categories`), itemData)
  
      const descriptionEl = el.querySelector(`.${classPrefix}__description`);
      descriptionEl.innerHTML = itemData.description;
  
      setTimeout(() => {
        toggleElHidden(el, false);
        el.style.opacity = 1;
      }, 100)
    });
  };
  setCategoryChips = (el, itemData) => {
    el.innerHTML = '';

    const categories = this.getItemCategories(itemData);
    
    for (const category of categories) {
      el.appendChild(this.getCategoryChip(category));
    }
  }
  getItemCategories = (itemData) => {
    return [
      ...(itemData?.topics ?? []),
      ...(itemData?.expertise ?? [])
    ].sort((a,b) => a.name < b.name ? -1 : 1);
  }
  getCategoryChip = (category) => {
    if (!category || !category.name || !category.name.length) return null;

    const chip = document.createElement('li');

    chip.className = 'category-chip'
    
    const button = document.createElement('button')
    button.type = 'button'
    button.textContent = category.name;
    button.dataset.id = category.term_id ?? category.id ?? category.name;
    button.dataset.taxonomy = category.taxonomy ?? '';

    chip.appendChild(button)

    return chip;
  }
  reset = () => {
    this.state = "landing";
    this.focus = false;
    this.focusId = null;
    this.open = false;
    this.history.reset()

    this.isDirectory && this.directorySearch.reset();

    this.updateDom();
  };
}

class MemberTabs {
  constructor (tabs, tabPanels, container) {
    this.tabEls = tabs;
    this.tabPanelEls = tabPanels;
    this.containerEl = container;
    
    this.firstTab = 0;
    this.lastTab = this.tabEls.length - 1;
    
    this.focusedTab = null;
    this.tabPanel = this.tabPanelEls[0];

    this.selectedIndex = 0;

    this.tabs = new Map();
    this.validTabs = new Map();

    this.tabEls.forEach((tab,index) => {
      tab.dataset.index = index
      const tabPanel = container.querySelector(`#${tab.getAttribute('aria-controls')}`);
      if (!tabPanel) return;
      tabPanel.dataset.index = index;

      this.tabs.set(tab, tabPanel);
      this.validTabs.set(tab, tabPanel);
    });
    
    this.tabEls.forEach((tabEl) => {
      tabEl.addEventListener("click", this.onClick),
      tabEl.addEventListener("keydown", this.onKeyDown),
      tabEl.addEventListener("focus", this.onFocusChange)
    });
  }
  onClick = (e) => {
    const tab = e.target.closest('[role=tab]')

    if (!this.validTabs.has(tab)) return;

    this.setSelectedIndex(tab.dataset.index);
  }
  setSelectedIndex = (selectedIndex = this.selectedIndex) => {
    this.selectedIndex = selectedIndex;

    this.tab = this.tabEls.find((tab) => {
      return tab.dataset.index.toString() === selectedIndex.toString();
    });
    this.tabPanel = this.tabPanelEls[selectedIndex];

    this.updateDom(selectedIndex);
  };
  updateTabs = () => {
    
    this.validTabs = new Map();
    let selectedIndex;
    let index = -1;
    this.tabs.forEach((tabPanel, tab) => {
      index++;
      const hidden = true === tab.hidden;
      if (hidden) {
        toggleElHidden(tab, true),
        toggleElHidden(tabPanel, true);
        return;
      }
      this.validTabs.set(tab,tabPanel);
      if (selectedIndex != null) return toggleElHidden(tabPanel, true);
      selectedIndex = index;
    })

    this.setSelectedIndex(selectedIndex);
  }
  toggleElHidden = (el, hidden = true) => {
    if (!el) return;
    el.hidden = hidden;
    el.setAttribute("aria-hidden", hidden);
    el.tabIndex = hidden ? -1 : 0;
  }
  updateDom = (
    selectedIndex = this.selectedIndex
  ) => {
    this.validTabs.forEach((tabPanel, tab) => {
      const index = tab.dataset.index;
      const isCurrent = index.toString() === selectedIndex.toString();
  
      isCurrent
        ? tab.setAttribute("aria-selected", true)
        : tab.removeAttribute("aria-selected");
  
      tab.tabIndex = isCurrent ? 0 : -1;
  
      toggleElHidden(tabPanel, !isCurrent);
    })
  };
  onKeyDown = (e) => {
    const { key } = e;
    const delta =
      key === "ArrowLeft"
        ? -1
        : key === "ArrowRight"
        ? 1
        : key === "Home"
        ? -10
        : key === "End"
        ? 10
        : 0;
    this.setFocusedTab(delta);
    if (!["ArrowLeft", "ArrowRight", "Home", "End"].includes(key)) return;
    e.stopPropagation();
    e.preventDefault();
  };
  onFocusChange = (e) => {
    const { target, type } = e;
    this.focusedTab = type === "focus" ? target.closest('[role="tab"]') : null;
  };
  setFocusedTab(delta) {
    if (delta === 0) return;

    const currentIndex = +(this.focusedTab.dataset?.index ?? 0);
    // const lastTab = this.lastTab + (this.searchActive ? 0 : -1);
    const lastTab = this.lastTab;

    let nextIndex = 0;
    // Home or end
    if (delta === 10) {
      nextIndex = lastTab;
    }
    // +1 - 1
    else if (delta !== -10) {
      nextIndex =
        currentIndex + delta < 0
          ? lastTab
          : (currentIndex + delta) % (lastTab + 1);
    }

    this.focusedTab = this.tabEls.find(
      (tab) => +tab.dataset.index === nextIndex
    );
    this.focusedTab?.focus();
  }
}

export default Tabs;

function getItemId(type, id) {
  if (!type || !id) return null;
  return type + "--" + id;
}
function getRelativeHref(href) {
  const url = new URL(href);
  const searchParamsString = url.searchParams.toString();
  const searchParams = searchParamsString.length
    ? `?${searchParamsString}`
    : "";
  const hash = url.hash;

  return searchParams + hash;
}
function matchItemId(itemId) {
  return (item) => (item.properties.itemId ?? item.properties.item_id) === itemId;
}
function getFeaturesByItemId(features, itemId) {
  return features.filter(matchItemId(itemId));
}
function getItemsByItemId (items, itemId) {
  if (!itemId || !items) return null; 
  return items.find(item => item.item_id.toString() === itemId.toString());
}
