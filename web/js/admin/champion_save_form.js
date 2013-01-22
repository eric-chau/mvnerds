// Get the div that holds the collection of tags
var tagCollectionHolder = $('div#champion_champion_tags');
var i18nCollectionHolder = $('div#champion_champion_i18ns');
var skillCollectionHolder = $('#champion_skills');
var skillI18nCollectionHolder = $('fieldset.champion_skill_i18n');

// setup an "add a tag" link
var $addTagLink = $('<a href="#" class="btn btn-success" id="add-tag-link"><i class="icon-white icon-plus-sign"></i> Add a tag</a>');
var $addI18nLink = $('<a href="#" class="btn btn-success" id="add-tag-link"><i class="icon-white icon-plus-sign"></i> Add a translation</a>');
var $addSkillLink = $('a#add-skills-link');
var $addSkillI18nLink = $('a.add-skill-i18n-link');

jQuery(document).ready(function() {
	tagCollectionHolder.before($addTagLink);
	i18nCollectionHolder.before($addI18nLink);

	$addTagLink.on('click', function(e) {
		e.preventDefault();
		addTagForm(tagCollectionHolder, $addTagLink);
	});
	$addI18nLink.on('click', function(e) {
		e.preventDefault();
		addI18nForm(i18nCollectionHolder, $addI18nLink);
	});
	$addSkillLink.on('click', function(e) {
		e.preventDefault();
		var collectionSize = skillCollectionHolder.children('fieldset').length;

		//'<fieldset class="item_item_secondary_effect" style="margin-left:50px"><legend>Effet secondaire <label>__name__</label> </legend><div style="margin-bottom:9px;"><label for="champion_skills___name___category">Catégorie</label><select id="champion_skills___name___category" name="item[item_secondary_effects][__name__][category]"><option value="ACTIVE">ACTIVE</option><option value="AURA">AURA</option><option value="PASSIVE">PASSIVE</option><option value="CONSUMABLE">CONSUMABLE</option><option value="OTHER" selected="selected">OTHER</option></select></div><div style="margin-bottom:9px;"><label for="champion_skills___name___is_unique">Est unique ?</label><input type="checkbox" id="champion_skills___name___is_unique" name="item[item_secondary_effects][__name__][is_unique]" value="__name__" checked="checked"></div><fieldset style="margin-left:100px;"><legend>Traductions de l\'effet secondaire : </legend><a href="#" class="btn btn-success add-secondary-effects-i18n-link"><i class="icon-white icon-plus-sign"></i> Add a secondary effect translation</a></fieldset>';
		
		var fieldset= '<fieldset class="champion_skill" style="margin-left:50px">'+
					'<legend>Skill <label class="required">__name__</label> </legend>'+
					'<div style="margin-bottom:9px;">'+
						'<label for="champion_skills___name___range" class="required">Range</label>'+
						'<input type="text" id="champion_skills___name___range" name="champion[skills][__name__][range]" required="required" maxlength="45" value="">'+
					'</div>'+
					'<div style="margin-bottom:9px;">'+
						'<label for="champion_skills___name___position" class="required">Position</label>'+
						'<input type="number" id="champion_skills___name___position" name="champion[skills][__name__][position]" required="required" value="">'+
					'</div>'+
					'<div style="margin-bottom:9px;">'+
						'<label for="champion_skills___name___image" class="required">Image</label>'+
						'<input type="text" id="champion_skills___name___image" name="champion[skills][__name__][image]" value="">'+
					'</div>'+
					'<fieldset class="champion_skill_i18n" style="margin-left:100px;">'+
						'<legend>Traductions du skill : </legend>'+
						'<a href="#" class="btn btn-success add-skill-i18n-link"><i class="icon-white icon-plus-sign"></i> Add a skill translation</a>'+
					'</fieldset>'+
				'</fieldset>';
		
		var $fieldset = $(fieldset.replace(/__name__/g, collectionSize));

		skillCollectionHolder.append($fieldset);

		addFormDeleteLink(skillCollectionHolder.children('fieldset').last());
		addFormDeleteLink(skillCollectionHolder.children('fieldset').last().children('fieldset').last().children('div').children('div'));
	});
	skillCollectionHolder.on('click', 'a.add-skill-i18n-link', function(e) {
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

	// add a delete link to all of the existing tag form li elements
	$('#champion_champion_tags').children('div').each(function() {
		addTagFormDeleteLink($(this));
	});
	
	skillCollectionHolder.children('fieldset').each(function() {
		addFormDeleteLink($(this));
	});
	skillI18nCollectionHolder.children('div').children('div').each(function(){
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

function addTagForm(tagCollectionHolder, $addTagLink) {
	// Get the data-prototype we explained earlier
	var prototype = tagCollectionHolder.attr('data-prototype');

	var newForm = prototype.replace(/__name__label__/g, tagCollectionHolder.children().length);
	newForm = newForm.replace(/__name__/g, tagCollectionHolder.children().length);

	tagCollectionHolder.append($(newForm));

	addTagFormDeleteLink(tagCollectionHolder.find('div').last().parent().parent());
}
function addSkillForm(skillCollectionHolder) {
	var prototype = skillCollectionHolder.attr('data-prototype');

	var newForm = prototype.replace(/__name__label__/g, skillCollectionHolder.children().length);
	newForm = newForm.replace(/__name__/g, skillCollectionHolder.children().length);

	skillCollectionHolder.append($(newForm));

	var $removeFormA = $('<a style="margin:10px;" class="btn btn-small btn-danger" href="#"><i class="icon-remove icon-white"></i></a>');
	skillCollectionHolder.children('div').last().children('label').after($removeFormA);
	$removeFormA.on('click', function(e) {
		e.preventDefault();
		$(this).parent().remove();
	});
}
function addI18nForm(i18nCollectionHolder, $addI18nLink) {
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