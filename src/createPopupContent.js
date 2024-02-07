import appendFellowPopupContent from "./appendFellowPopupContent";
import appendMemberPopupContent from "./appendMemberPopupContent";
import appendProjectPopupContent from "./appendProjectPopupContent";
import isEmpty from 'is-empty'

export default function createPopupContent (itemData, hashKey) {
    const { name, id, type } = itemData;
    if (!["member", "project", "fellow"].includes(type)) return null;

    console.trace({hashKey})

    const popupContainer = document.createElement("div");
    popupContainer.className =
      "map_popup w-map-popup max-w-popup rounded-m bg-green-50 w-full relative flex flex-col drop-shadow-xl font-sans overflow-hidden";

    const anchor = document.createElement("a");
    anchor.className = "map_popup_anchor";
    anchor.textContent = name ?? "";
    anchor.tabIndex = 0;
    hashKey = isEmpty(hashKey) ? hashKey : 'directory';
    anchor.setAttribute("href", `?focus=${type}&id=${id}#${hashKey}`);
    popupContainer.appendChild(anchor);

    const innerContainer = document.createElement("div");
    innerContainer.className =
      `grid gap-x-10 items-center justify-items-start w-full min-h-[5.25rem] ${type !== 'resource' ? "grid-cols-[auto_1fr]" : 'grid-cols-[1fr_auto]'}`;
    popupContainer.appendChild(innerContainer);

    const appendContentFn = type === 'member'
      ? appendMemberPopupContent
      : type === 'project'
        ? appendProjectPopupContent
        : appendFellowPopupContent;

    appendContentFn(innerContainer, itemData);

    return popupContainer;
  }