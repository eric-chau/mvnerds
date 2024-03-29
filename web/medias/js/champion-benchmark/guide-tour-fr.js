var nextButton = {name: "<em>S</em>uivant", classString: "btn-next", onclick: guiders.next},
    closeButton = {name: "<em>F</em>ermer", classString: "btn-close", onclick: onCloseCallBack},
    prevButton = {name: "<em>P</em>récédent", classString: "btn-prv", onclick: guiders.prev};

// STEP #1 : Présentation
guiders.createGuider({
  buttons: [nextButton, closeButton],
  description: "Nous allons effectuer ensemble un tour rapide du module des champions afin de vous permettre de profiter au mieux de toutes les fontionnalités. Le 'Champions benchmark' vous permet de confronter les champions de votre choix entre eux pour comparer les caractéristiques importantes telles que les points de vie ou les dégâts d'attaque.<i>Note 1 : vous pouvez interrompre la visite à tout moment en cliquant sur 'Fermer'.</i><i>Note 2 : utilisez la touche 's' pour afficher l'étape suivante ou 'p' pour l'étape précédente et la touche 'f' pour arrêter la visite guidée.</i>",
  id: "first",
  next: "second",
  overlay: true,
  title: "Visite guidée <span class='shortcut'>(Raccourci clavier : V)</span>"
});
/* .show() means that this guider will get shown immediately after creation. */

// STEP #2 : Aperçu champion
guiders.createGuider({
  //attachTo: "div.champions-handler-container ul#isotope-list li.champion",
  buttons: [prevButton, nextButton, closeButton],
  description: "Cliquez sur un champion pour consulter ses caractéristiques au niveau 1, son coût à l'acquisition en points d'influence et en riot points.",
  id: "second",
  next: "third",
  highlight: "div.champion-benchmark-container ul#isotope-list li.champion",
  overlay: true,
  title: "Aperçu rapide d'un champion"
});

// STEP #3 : liste de comparaison
guiders.createGuider({
  buttons: [prevButton, nextButton, closeButton],
  description: "Deux possibilités vous sont offertes pour ajouter un champion à la liste de comparaison : <ul><li>Glisser / déposer un champion dans la liste de comparaison</li><li>Double-clic sur un champion</li></ul><i>Note : le premier champion dans la liste (celui le plus en haut, à gauche) est considéré comme le champion de référence.</i>",
  id: "third",
  next: "fourth",
  highlight: "div.champion-benchmark-container ul.action-buttons li.comparison-list",
  overlay: true,
  title: "Liste de comparaison <span class='shortcut'>(Raccourci clavier : L)</span>"
});

// STEP #4 : Affiner le filtrage
guiders.createGuider({
  buttons: [prevButton, nextButton, closeButton],
  description: "Saisissez le début ou le nom complet d'un champion pour le retrouver plus rapidement. Vous pouvez également filtrer les champions selon des tags prédéfinis.<br /><i>Note : un bouton en bas à droite du menu 'Affiner le filtrage' apparaît lorsque vous appliquez un filtre. Il permet d'ajouter l'ensemble des champions filtrés à la liste de comparaison.</i>",
  id: "fourth",
  next: "finally",
  highlight: "div.champion-benchmark-container ul.action-buttons li#filters-block, div.champion-benchmark-container ul.action-buttons li.search-action",
  overlay: true,
  title: "Filtrer par tag et recherche <span class='shortcut'>(Raccourci clavier : F et R)</span>"
});

// STEP #5 : Aller à la page de comparaison
guiders.createGuider({
  buttons: [prevButton, {name: "<em>F</em>in de la visite, merci !", classString: "btn-close", onclick: onCloseCallBack}],
  description: "Deux champions doivent être au minimum présent dans la liste pour pouvoir démarrer une comparaison. A noter que vous pouvez comparer au maximum 64 champions simultanément.",
   id: "finally",
  highlight: "div.champion-benchmark-container ul.action-buttons li.btn-compare",
  overlay: true,
  title: "Comparer les champions <span class='shortcut'>(Raccourci clavier : C)</span>"
});
