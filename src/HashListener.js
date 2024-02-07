import {PartnersSection, Tabs} from './Tabs'

// const host = 'rightsandresouces.org'; // PROD
// const host = 'rightsandresouces.local'; // DEV
const host = 'localhost:5173'; // DEV

export class HashListener {
    constructor (hashes, map, search) {
        this.hashKeys = Object.keys(hashes);
        this.hashes = {}

        let opts;
        for (const hashKey of this.hashKeys) {
            opts = hashes[hashKey];
            this.hashes[hashKey] = new Hash(hashKey, opts);
        }

        this.hashKey = this.getHashKey()
        this.hash = this.hashes[this.hashKey];
        this.searchParamsString = this.getSearchParamsString()
        this.searchParams = this.getSearchParams();

        this.tabs = new Tabs(document.querySelector('#mapping-platform'), map, search);

        // this.partnersSection = new PartnersSection(
        //     document.querySelector('#partners'),
        //     mapData
        // );

        window.addEventListener('load',() => {
            this.init()
        })
    }
    init = () => {
        if (this.hash) {
            this.hash.onEnter(this.searchParams);

            // this.hashKey !== 'partners'
            //     ? this.tabs.onHashChange(this.hashKey, this.searchParams)
            //     : this.partnersSection.onEnter(this.searchParams);
            this.tabs.onHashChange(this.hashKey, this.searchParams)
        }

        window.addEventListener('hashchange',this.onHashChange);

        Object.values(this.hashes).forEach(hash => (hash.isPageLoad = false));
    }
    onHashChange = (e) => {
        const {oldURL,newURL} = e;
        const {searchParams, hash} = new URL(newURL);
        const searchParamsString = searchParams.toString();

        const parsedHash = hash.replace('#','');

        if (!this.hashKeys.includes(parsedHash)) return;

        this.searchParams = this.getSearchParams();
        this.searchParamsString = searchParamsString;

        if (this.isSameHash(parsedHash)) {
            if (this.isSameSearchParams(searchParamsString)) {
                return this.hash.scrollIntoView();
            }

            // handle new search params
            this.hash.setSearchParams(this.searchParams);
            // this.hashKey !== 'partners'
            //     ? this.tabs.onSearchParamsChange(this.searchParams)
            //     : this.partnersSection.onParamsChange(this.searchParams);
            this.tabs.onSearchParamsChange(this.searchParams)
            return;
        }

        // leave old hash
        this.hash && this.hash.onLeave()

        // handle new hash and params
        this.setHashKey(parsedHash);
        this.hash.onEnter(this.searchParams);
        // this.hashKey !== 'partners'
        //     ? this.tabs.onHashChange(this.hashKey, this.searchParams)
        //     : this.partnersSection.onEnter(this.searchParams)
        this.tabs.onHashChange(this.hashKey, this.searchParams)
    }
    setHashKey = (hashKey) => {
        this.hashKey = this.hashKeys.includes(hashKey) ? hashKey : null;
        this.hash = this.hashKey ? this.hashes[hashKey] : null;
        // this.hashKey !== 'partners'
        //     && this.tabs.map.onHashKeyChange(this.hashKey);
        this.tabs.map.onHashKeyChange(this.hashKey);
    }
    getHashKey = (url = new URL(window.location.href)) => {
        return url.hash.replace('#','');
    }
    getSearchParamsString = (url = new URL(window.location.href)) => {
        return url.searchParams.toString();
    }
    getSearchParams = () => {
        const searchParams = {}
        for (const  [key, value] of new URL(window.location.href).searchParams) {
            if (!['id','focus'].includes(key)) continue;
            searchParams[key] = key !== 'id' ? value : +value;
        }
        return searchParams;
    }
    isSameHash = (h1 = '') => {
        return h1.replace('#', '') === this.hashKey;
    }
    isSameSearchParams = (p1) => {
        return p1 === this.searchParams;
    }
}

class Hash {
    constructor (hash, opts = {}) {
        this.hash = hash

        this.onEnterCallback = opts?.onEnter ?? null;
        this.onLeaveCallback = opts?.onLeave ?? null;

        this.anchorEl = document.getElementById(this.hash);
    
        this.searchParams = {};
        this.searchParamsString = '';

        this.isPageLoad = true;

        this.init();
    }
    init = () => {}
    onEnter = (searchParams) => {
        this.setSearchParams(searchParams)

        this.scrollIntoView();

        this.onEnterCallback && this.onEnterCallback()
    }
    onLeave = () => {
        this.onLeaveCallback && this.onLeaveCallback()
    }
    setSearchParams = (searchParams) => {
        this.searchParams = searchParams;
    }
    isInViewport = () => {
        if (!this.anchorEl) return true;

        const {top, bottom} = this.anchorEl.getBoundingClientRect();

        return top <= window.innerHeight && bottom >= 0;
    }
    scrollIntoView = () => {
        if (!this.isPageLoad && (!this.anchorEl || this.isInViewport())) return;

        const top = this.anchorEl.getBoundingClientRect().top + window.scrollY;
        window.scrollTo(0,top)
    }
    scrollToTop = () => {
        const top = this.anchorEl.getBoundingClientRect().top + window.scrollY;
        window.scrollTo(0,top)
    }
}

// class Link {
//     constructor (el) {
//         this.el = el;
//         this.href = el.getAttribute('href').trim();

//         this.hash = this.href.replace('#','').split('?')[0];
//         this.searchParams = '?'+this.href.split('?')[1];

//         this.url = window.location.origin + window.location.pathname + this.href

//         this.addListeners()
//     }
//     addListeners = () => {
//         this.el.addEventListener('click',this.handleClick)
//     }
//     handleClick = (e) => {
//         e.preventDefault();
//         history.pushState(null,'',this.url);
//         hashListener.onHashChange({
//             newURL: this.url
//         });
//     }
// }

export class CollaboratorLink {
    constructor (el) {
        this.el = el;
        this.href = el.getAttribute('href')

        this.addListener()
    }
    addListener = () => {
        this.el.addEventListener('click',this.onClick);
    }
    onClick = () => {
        const url = window.location.protocol + '//' + window.location.host + window.location.pathname + this.href;
        history.pushState(null, '', url)
    }
}

export const hashes = {
    'directory': {
        onEnter: () => {},
        onLeave: () => {},
    },
    'regions': {
        onEnter: () => {},
        onLeave: () => {},
    },
    'topics': {
        onEnter: () => {},
        onLeave: () => {},
    },
    'expertise': {
        onEnter: () => {},
        onLeave: () => {},
    },
    'partners': {
        onEnter: () => {},
        onLeave: () => {},
    },
    'collaborators': {
        onEnter: () => {},
        onLeave: () => {},
    },
    'fellows': {
        onEnter: () => {},
        onLeave: () => {},
    },
}

// const links = [...document.querySelectorAll('a')]
//     .filter(isValidLink)
//     .map(link => new Link(link));

export function isValidLink (link) {
    return null != link.href
        && '' !== link.href.trim()
        && link.href.includes('#')
        && Object.keys(hashes).includes(link.href.split('#')[1])
        && new URL(link.href).host === host;
}