export default function appendFellowPopupContent (container, itemData) {
    const thumbnailContainer = document.createElement("div");
    thumbnailContainer.className =
      "col-start-1 col-end-2 row-start-1 row-end-2 overflow-hidden w-map-thumbnail min-h-map-thumbnail h-full w-full";
    const thumbnail = document.createElement("img");
    thumbnail.src = itemData?.thumbnail ?? "";
    thumbnail.alt = itemData?.name ?? "";
    thumbnail.className = "min-w-full min-h-full h-full object-center object-cover";
  
    thumbnailContainer.appendChild(thumbnail);
    container.appendChild(thumbnailContainer);
  
    const contentContainer = document.createElement("div");
    contentContainer.className =
      "col-start-2 col-end-3 row-start-1 row-end-2 my-6 mr-6 w-full";
  
    const type = document.createElement("div");
    type.textContent = "Fellow";
    type.className = "uppercase text-green-500 text-h5 font-sans";
    contentContainer.appendChild(type);
  
    const name = document.createElement("h4");
    name.textContent = itemData?.name ?? "";
    name.className = "mt-1 text-green-800 text-h4 font-sans";
    contentContainer.appendChild(name);
  
    container.appendChild(contentContainer);
    container.dataset.type = itemData.type;
  }