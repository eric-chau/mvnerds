var $btnCompare = $('a#btn-compare'),
  $searchInputText = $('input[type="text"]#filter-value'),
  $btnComparisonList = $('a#drop-comparison-list'),
  $btnHelp = $('li.help-action'),
  $btnFilter = $('a#drop-filter-list'),
  isFirstActionAfterGT = false;

var ctrlDown = false,
    ctrlKey = 17,
    escapeKey = 27,
    compareShortcut = 67,
    listShortcut = 76,
    filterShortcut = 70,
    searchShortcut,
    guideShortcut;

if (locale == 'fr') {
  searchShortcut = 82;
  guideShortcut = 86;
}
else {
  searchShortcut = 83;
  guideShortcut = 71;
}

$(document).ready(function()
{
    $(document).keydown(function(event)
    {
      if (event.which == ctrlKey) {
        ctrlDown = true;
      }
    }).keyup(function(event)
    {
        if (event.which == ctrlKey) {
          ctrlDown = false;
        }
    });

    $('body').bind('keyup', function(event) 
    {
      shortcutListener(event);
    });

    $('input[type="email"]#user_email, input[type="text"]#filter-value, form#sidebar-login-form input').on('focus', function()
    {
      $('body').unbind('keyup');
      $searchInputText.bind('keyup', function(event)
      {
        if (event.which == escapeKey) {
          $(this).blur();
        }
      });
    });



    $('input[type="email"]#user_email, input[type="text"]#filter-value, form#sidebar-login-form input').on('blur', function()
    {
      $('body').bind('keyup', function(event)
      {
        shortcutListener(event);
      });
    });
});

function shortcutListener(event) {
  if (isFirstActionAfterGT) {
    isFirstActionAfterGT = false;
    
    return false;
  }

  if (ctrlDown) {
    return false;
  }

  switch(event.which) {
    case compareShortcut: 
      if ($btnCompare.hasClass('disabled')) {
        return false;
      }

      $btnCompare.trigger('click');
      break;
    case searchShortcut:
      $('.dropdown.open .dropdown-toggle').dropdown('toggle');
      $searchInputText[0].selectionStart = $searchInputText[0].selectionEnd = $searchInputText.val().length;
      break;
    case listShortcut:
      $btnComparisonList.trigger('click');
      break;
    case filterShortcut:
      $btnFilter.trigger('click');
      break;
    case guideShortcut:
      $('.dropdown.open .dropdown-toggle').dropdown('toggle');
      $btnHelp.trigger('click');
    default:
      break;
  }
}
