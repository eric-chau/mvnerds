var nextButton = {name: "<em>N</em>ext", classString: "btn-next", onclick: guiders.next},
    closeButton = {name: "<em>C</em>lose", classString: "btn-close", onclick: onCloseCallBack},
    prevButton = {name: "<em>P</em>revious", classString: "btn-prv", onclick: guiders.prev};

// STEP #1 : Présentation
guiders.createGuider({
  buttons: [nextButton, closeButton],
  description: "To make sure you know how to benefit from all functionalities, we are now going to explore the champions module together. In order to compare the important statistics such as life points or attacks damage, the Champions benchmark allows you to confront any champion you choose against any other one.<i>Note : You can interrupt the tour by clicking 'Close' at all times.</i><i>Note 2 : press 'N' to show the next step, and 'P' to go back. Press 'C' to discontinue the tour.</i>",
  id: "first",
  next: "second",
  overlay: true,
  title: "Guide tour <span class='shortcut'>(Shortcut: G)</span>"
});
/* .show() means that this guider will get shown immediately after creation. */

// STEP #2 : Aperçu champion
guiders.createGuider({
  //attachTo: "div.champions-handler-container ul#isotope-list li.champion",
  buttons: [prevButton, nextButton, closeButton],
  description: "Click on any champion to view their statistics at level 1, their purchase price in influence points and riot points.",
  id: "second",
  next: "third",
  highlight: "div.champions-handler-container ul#isotope-list li.champion",
  overlay: true,
  title: "Champion quick overview"
});

// STEP #3 : liste de comparaison
guiders.createGuider({
  buttons: [prevButton, nextButton, closeButton],
  description: "You are offered two possibilities to add a champion in the list of comparison:<ul><li>Drag and drop a champion into comparison list</li><li>Double click on a champion</li></ul><i>Note: The first champion on the list (the higher up one, on the left) is considered as the reference champion.</i>",
  id: "third",
  next: "fourth",
  highlight: "div.champions-handler-container ul.action-buttons li#comparison-list-dropdown",
  overlay: true,
  title: "Comparison List <span class='shortcut'>(Shortcut: L)</span>"
});

// STEP #4 : Affiner le filtrage
guiders.createGuider({
  buttons: [prevButton, nextButton, closeButton],
  description: "The quickest way to find a champion is to type in the beginning or full name of it. You can also filter champions according to their default tags.<br /><i>Note: in the bottom on the right of the menu 'Filter', a button appears when you apply a filter. It enables to add all filtered champions together to the comparison list.</i>",
  id: "fourth",
  next: "finally",
  highlight: "div.champions-handler-container ul.action-buttons li#filters-block, div.champions-handler-container ul.action-buttons li.search-action",
  overlay: true,
  title: "Filter by tags and search <span class='shortcut'>(Shortcut: F and S)</span>"
});

// STEP #5 : Aller à la page de comparaison
guiders.createGuider({
  buttons: [prevButton, {name: "End of guide tour, thank you !", classString: "btn-close", onclick: onCloseCallBack}],
  description: "Two champions from the list are needed to make a comparison. Note that you can compare 64 champions simultaneously.",
   id: "finally",
  highlight: "div.champions-handler-container ul.action-buttons li.red-button",
  overlay: true,
  title: "To compare champions <span class='shortcut'>(Shortcut: C)</span>"
});
