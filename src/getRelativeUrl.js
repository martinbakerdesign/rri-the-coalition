export default function getRelativeUrl (newParams, newHash) {
    const params = (newParams.length ? `?${newParams}` : '');
    const hash = ((newHash && newHash.replace('#').length) ? `#${newHash.replace('#','')}` : '')

    return params + hash;
}