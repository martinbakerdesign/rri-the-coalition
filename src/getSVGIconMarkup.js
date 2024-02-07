export default function getSVGIconMarkup (iconId) {
    const symbolId = `#symbol-${iconId}`;
    const symbolDef = document.querySelector(symbolId);
    const width = symbolDef?.getAttribute("width");
    const height = symbolDef?.getAttribute("height");
  
    return `<svg xlmns="http://www.w3.org/2000/svg" width="${width}" height="${height}" class="relative z-0">
          <use href="${symbolId}" />
      </svg>`;
  }