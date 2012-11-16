// Get the div that holds the collection of tags
var tagCollectionHolder = $('div#champion_champion_tags');

var i18nCollectionHolder = $('div#champion_champion_i18ns');

// setup an "add a tag" link
var $addTagLink = $('<a href="#" class="btn btn-success" id="add-tag-link"><i class="icon-white icon-plus-sign"></i> Add a tag</a>');
var $addI18nLink = $('<a href="#" class="btn btn-success" id="add-tag-link"><i class="icon-white icon-plus-sign"></i> Add a translation</a>');

jQuery(document).ready(function() {
	tagCollectionHolder.before($addTagLink);
	i18nCollectionHolder.before($addI18nLink);

	$addTagLink.on('click', function(e) {
		e.preventDefault();
		addTagForm(tagCollectionHolder, $addTagLink);
	});

	$addI18nLink.on('click', function(e) {
		// prevent the link from creating a "#" on the URL
		e.preventDefault();

		// add a new tag form (see next code block)
		addI18nForm(i18nCollectionHolder, $addI18nLink);
	});

	// add a delete link to all of the existing tag form li elements
	$('#champion_champion_tags').children('div').each(function() {
		addTagFormDeleteLink($(this));
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

function addTagForm(tagCollectionHolder, $addTagLink) {
	// Get the data-prototype we explained earlier
	var prototype = tagCollectionHolder.attr('data-prototype');

	var newForm = prototype.replace(/__name__label__/g, tagCollectionHolder.children().length);
	newForm = newForm.replace(/__name__/g, tagCollectionHolder.children().length);

	tagCollectionHolder.append($(newForm));

	addTagFormDeleteLink(tagCollectionHolder.find('div').last().parent().parent());
}

function addI18nForm(i18nCollectionHolder, $addI18nLink) {
	// Get the data-prototype we explained earlier
	var prototype = i18nCollectionHolder.attr('data-prototype');

	var newForm = prototype.replace(/__name__label__/g, i18nCollectionHolder.children().length);
	newForm = newForm.replace(/__name__/g, i18nCollectionHolder.children().length);

	i18nCollectionHolder.append($(newForm));
}