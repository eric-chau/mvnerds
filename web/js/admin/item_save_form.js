// Get the div that holds the collection of tags
var tagCollectionHolder = $('div#item_item_tags');
var gameModeCollectionHolder = $('div#item_item_game_modes');
var primaryEffectCollectionHolder = $('div#item_item_primary_effects');
var geneologyCollectionHolder = $('div#item_item_geneologies_related_by_parent_id_custom');
var i18nCollectionHolder = $('div#item_item_i18ns');
var secondaryEffectsCollectionHolder = $('#item_item_secondary_effects');
var secondaryEffectsI18nCollectionHolder = $('fieldset.item_item_secondary_effect_i18n');

// setup an "add a tag" link
var $addTagLink = $('<a href="#" class="btn btn-success" id="add-tag-link"><i class="icon-white icon-plus-sign"></i> Add a tag</a>');
var $addGameModeLink = $('<a href="#" class="btn btn-success" id="add-game-mode-link"><i class="icon-white icon-plus-sign"></i> Add a game mode</a>');
var $addPrimaryEffectLink = $('<a href="#" class="btn btn-success" id="add-primary-effect-link"><i class="icon-white icon-plus-sign"></i> Add a primary effect</a>');
var $addGeneologyLink = $('<a href="#" class="btn btn-success" id="add-geneology-link"><i class="icon-white icon-plus-sign"></i> Add a child</a>');
var $addI18nLink = $('<a href="#" class="btn btn-success" id="add-i18n-link"><i class="icon-white icon-plus-sign"></i> Add a translation</a>');
var $addSecondaryEffects18nLink = $('a.add-secondary-effects-i18n-link');
var $addSecondaryEffectsLink = $('a#add-secondary-effects-link');

jQuery(document).ready(function() {

	//Add the add buttons
	tagCollectionHolder.before($addTagLink);
	gameModeCollectionHolder.before($addGameModeLink);
	primaryEffectCollectionHolder.before($addPrimaryEffectLink);
	geneologyCollectionHolder.before($addGeneologyLink);
	i18nCollectionHolder.before($addI18nLink);

	//Add event on click addButton
	$addTagLink.on('click', function(e) {
		e.preventDefault();
		addForm(tagCollectionHolder, true);
	});
	$addGameModeLink.on('click', function(e) {
		e.preventDefault();
		addForm(gameModeCollectionHolder, true);
	});
	$addPrimaryEffectLink.on('click', function(e) {
		e.preventDefault();
		addForm(primaryEffectCollectionHolder, true);
	});
	$addGeneologyLink.on('click', function(e) {
		e.preventDefault();
		addForm(geneologyCollectionHolder, true);
	});
	$addI18nLink.on('click', function(e) {
		e.preventDefault();
		addForm(i18nCollectionHolder);
	});
	$addSecondaryEffectsLink.on('click', function(e) {
		e.preventDefault();
		var prototype = secondaryEffectsCollectionHolder.attr('data-prototype');
		var collectionSize = secondaryEffectsCollectionHolder.children('fieldset').length;

		var fieldset='<fieldset class="item_item_secondary_effect" style="margin-left:50px"><legend>Effet secondaire <label>__name__</label> </legend><div style="margin-bottom:9px;"><label for="item_item_secondary_effects___name___category">Catégorie</label><select id="item_item_secondary_effects___name___category" name="item[item_secondary_effects][__name__][category]"><option value="ACTIVE">ACTIVE</option><option value="AURA">AURA</option><option value="PASSIVE">PASSIVE</option><option value="CONSUMABLE">CONSUMABLE</option><option value="OTHER" selected="selected">OTHER</option></select></div><div style="margin-bottom:9px;"><label for="item_item_secondary_effects___name___is_unique">Est unique ?</label><input type="checkbox" id="item_item_secondary_effects___name___is_unique" name="item[item_secondary_effects][__name__][is_unique]" value="__name__" checked="checked"></div><fieldset style="margin-left:100px;"><legend>Traductions de l\'effet secondaire : </legend><a href="#" class="btn btn-success add-secondary-effects-i18n-link"><i class="icon-white icon-plus-sign"></i> Add a secondary effect translation</a></fieldset>';
		var $fieldset = $(fieldset.replace(/__name__/g, collectionSize));

		secondaryEffectsCollectionHolder.append($fieldset);

		addFormDeleteLink(secondaryEffectsCollectionHolder.children('fieldset').last());
		addFormDeleteLink(secondaryEffectsCollectionHolder.children('fieldset').last().children('fieldset').last().children('div').children('div'));
	});
	secondaryEffectsCollectionHolder.on('click', 'a.add-secondary-effects-i18n-link', function(e) {
		e.preventDefault();

		$parent = $(this).parent();
		var collectionSize = $parent.children('div').length;
		var prototype = $parent.parent().parent().attr('data-i18n-prototype').replace(/__name__/g, collectionSize);
		var parentCollectionSize = $parent.parent().parent().children('fieldset').index($parent.parent());
		prototype = prototype.replace(/__parent-name__/g, parentCollectionSize);
		var $prototype = $(prototype).prepend('<label>'+collectionSize+'</label>');
		$parent.append($prototype);

		addFormDeleteLink($(this).parent().children('div').last());
	});

	//Add delete buttons
	tagCollectionHolder.children('div').each(function() {
		addFormDeleteLink($(this));
	});
	gameModeCollectionHolder.children('div').each(function() {
		addFormDeleteLink($(this));
	});
	primaryEffectCollectionHolder.children('div').each(function() {
		addFormDeleteLink($(this));
	});
	geneologyCollectionHolder.children('div').each(function() {
		addFormDeleteLink($(this));
	});
	i18nCollectionHolder.children('div').each(function() {
		addFormDeleteLink($(this));
	});
	secondaryEffectsCollectionHolder.children('fieldset').each(function() {
		addFormDeleteLink($(this));
	});
	secondaryEffectsI18nCollectionHolder.children('div').children('div').each(function(){
		addFormDeleteLink($(this));
	});
});

function addForm(collectionHolder, enableDelete) {
	var prototype = collectionHolder.attr('data-prototype');
	var collectionSize = collectionHolder.children().length;
	var newForm = prototype.replace(/__name__label__/g, collectionSize);
	newForm = newForm.replace(/__name__/g, collectionSize);
	collectionHolder.append($(newForm));
	if(enableDelete != undefined && enableDelete) {
		addFormDeleteLink(collectionHolder.find('div').last().parent().parent());
	}
}
function addFormDeleteLink($formDiv) {
	var $removeFormA = $('<a style="margin:9px 5px;" class="btn btn-mini btn-danger" href="#"><i class="icon-remove icon-white"></i></a>');
	$formDiv.find('label').first().after($removeFormA);

	$removeFormA.on('click', function(e) {
		e.preventDefault();
		$formDiv.remove();
	});
}