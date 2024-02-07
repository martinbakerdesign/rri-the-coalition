export default function appendProjectPopupContent (container, itemData) {
    const thumbnailContainer = document.createElement("div");
    thumbnailContainer.className =
      "col-start-1 col-end-2 row-start-1 row-end-3 overflow-hidden w-map-thumbnail min-h-map-thumbnail h-full";
    const thumbnail = document.createElement("img");
    thumbnail.src = itemData?.thumbnail ?? "";
    thumbnail.alt = itemData?.name ?? "";
    thumbnail.className = "w-full h-full object-center object-cover";
  
    thumbnailContainer.appendChild(thumbnail);
    container.appendChild(thumbnailContainer);
  
    const contentContainer = document.createElement("div");
    contentContainer.className =
      "col-start-2 col-end-3 row-start-1 row-end-2 mt-6 mr-6 w-full";
  
    const type = document.createElement("div");
    type.textContent = "Project";
    type.className = "uppercase text-green-500 text-h5 font-sans";
    contentContainer.appendChild(type);
  
    const name = document.createElement("h4");
    name.textContent = itemData?.name ?? "";
    name.className = "mt-1 text-green-800 text-h4 font-sans";
    contentContainer.appendChild(name);
  
    container.appendChild(contentContainer);
  
    const memberCard = document.createElement("a");
    memberCard.className =
      "flex items-center gap-x-4 mt-4 mb-6 text-h6 text-green-700 col-start-2 col-end-3 row-start-2 row-end-3 mr-6 w-full justify-content-start";
    memberCard.setAttribute(
      "href",
      `/who-we-are/the-coalition#members?id=${itemData?.memberId}`
    );
  
    const memberLogo = document.createElement("img");
    memberLogo.src = itemData?.memberLogo ?? "";
    memberLogo.alt = itemData?.memberName ?? "";
  
    memberCard.appendChild(memberLogo);
  
    memberCard.innerHTML += itemData?.memberName ?? "";
  
    container.appendChild(memberCard);
    container.dataset.type = itemData.type;
  }