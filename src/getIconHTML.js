export default function getIconHTML(type, count = 1, label='') {
    const symbolId = `#symbol-${type}-${count}`;
  
    const symbolDef = document.querySelector(symbolId);
    const width = symbolDef?.getAttribute("width");
    const height = symbolDef?.getAttribute("height");
  
    const iconHTML = `<svg xlmns="http://www.w3.org/2000/svg" data-type="${type}" data-count="${count}" width="${width}" height="${height}" class="relative z-0 fill-inherit">
    <title>${label}</title>
    <use href="${symbolId}" />
    </svg>`;
  
    return iconHTML;
  }