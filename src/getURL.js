export default function getURL (newParams, newHash) {
    const params = (newParams.length ? `?${newParams}` : '');
    const hash = ((newHash && newHash.replace('#').length) ? `#${newHash.replace('#','')}` : '')
    const url = window.location.origin + window.location.pathname + params + hash;

    return url;
}