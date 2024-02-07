export default function appendMemberPopupContent (container, itemData) {
    const logo = document.createElement("img");
    logo.className = "m-6 col-start-1 col-end-2 row-start-1 row-end-2";
    logo.src = itemData?.logo ?? "";
    logo.alt = itemData?.name ?? "";
    logo.className = "w-map-logo h-map-logo";
  
    container.appendChild(logo);
  
    const contentContainer = document.createElement("div");
    contentContainer.className =
      "col-start-2 col-end-3 row-start-1 row-end-2 my-6 mr-6 w-full";
  
    const type = document.createElement("div");
    type.textContent = itemData?.memberType ?? "Partner";
    type.className = "uppercase text-green-500 text-h5";
    contentContainer.appendChild(type);
  
    const name = document.createElement("h4");
    name.textContent =
      itemData?.name + (itemData?.abbreviation ? ` (${itemData.abbreviation})` : "") ?? "";
    name.className = "mt-1 text-green-800 text-h4";
    contentContainer.appendChild(name);
  
    container.appendChild(contentContainer);
    container.dataset.type = itemData.type;
  }