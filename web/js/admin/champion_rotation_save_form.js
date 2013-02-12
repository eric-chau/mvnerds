// Get the div that holds the collection of tags
var championCollectionHolder = $('div#rotation_champion_rotations');
var i18nCollectionHolder = $('div#rotation_rotation_i18ns');

// setup an "add a tag" link
var $addChampionLink = $('<a href="#" class="btn btn-success" id="add-champion-link"><i class="icon-white icon-plus-sign"></i> Add a champion</a>');
var $addI18nLink = $('<a href="#" class="btn btn-success" id="add-i18n-link"><i class="icon-white icon-plus-sign"></i> Add a translation</a>');

jQuery(document).ready(function() {
	championCollectionHolder.before($addChampionLink);
	i18nCollectionHolder.before($addI18nLink);

	$addChampionLink.on('click', function(e) {
		e.preventDefault();
		addChampionForm(championCollectionHolder, $addChampionLink);
	});
	$addI18nLink.on('click', function(e) {
		e.preventDefault();
		addI18nForm(i18nCollectionHolder, $addI18nLink);
	});

	// add a delete link to all of the existing tag form li elements
	$('#rotation_champion_rotations').children('div').each(function() {
		addFormDeleteLink($(this));
	});
});

function addTagFormDeleteLink($tagFormDiv) {
	var $removeFormA = $('<a style="margin-left:10px;" class="btn btn-small btn-danger" href="#"><i class="icon-remove icon-white"></i></a>');
	$tagFormDiv.children('div').children('div').append($removeFormA);

	$removeFormA.on('click', function(e) {
		e.preventDefault();
		$tagFormDiv.remove();
	});
}

function addChampionForm(championCollectionHolder) {
	// Get the data-prototype we explained earlier
	var prototype = championCollectionHolder.attr('data-prototype');

	var newForm = prototype.replace(/__name__label__/g, championCollectionHolder.children().length);
	newForm = newForm.replace(/__name__/g, championCollectionHolder.children().length);

	championCollectionHolder.append($(newForm));

	addTagFormDeleteLink(championCollectionHolder.find('div').last().parent().parent());
}
function addI18nForm(i18nCollectionHolder) {
	// Get the data-prototype we explained earlier
	var prototype = i18nCollectionHolder.attr('data-prototype');

	var newForm = prototype.replace(/__name__label__/g, i18nCollectionHolder.children().length);
	newForm = newForm.replace(/__name__/g, i18nCollectionHolder.children().length);

	i18nCollectionHolder.append($(newForm));
}

function addFormDeleteLink($formDiv) {
	var $removeFormA = $('<a style="margin:9px 5px;" class="btn btn-mini btn-danger" href="#"><i class="icon-remove icon-white"></i></a>');
	$formDiv.find('label').first().after($removeFormA);

	$removeFormA.on('click', function(e) {
		e.preventDefault();
		$formDiv.remove();
	});
}