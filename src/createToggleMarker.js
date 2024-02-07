import getSVGIconMarkup from "./getSVGIconMarkup";

export default function createToggleContainer (count) {
  const container = document.createElement("div");
  container.className =
    "flex items-center map_cluster pointer-events-auto cursor-default rounded-l bg-green-600 relative text-white fill-white text-map-popup-status font-sans relative";

  const prevButton = document.createElement("button");
  const nextButton = document.createElement("button");
  prevButton.dataset.delta = "-1";
  nextButton.dataset.delta = "1";
  prevButton.className = nextButton.className =
    "rounded-l bg-green-500 p-4 h-full cursor-pointer hover:bg-green-600";
  const prevIconMarkup = getSVGIconMarkup("chevron-left-14");
  const nextIconMarkup = getSVGIconMarkup("chevron-right-14");
  prevButton.innerHTML = prevIconMarkup;
  nextButton.innerHTML = nextIconMarkup;

  const status = document.createElement("div");
  status.className = "p-4 status";
  status.textContent = `1 of ${count}`;

  container.appendChild(prevButton);
  container.appendChild(status);
  container.appendChild(nextButton);

  return container;
}