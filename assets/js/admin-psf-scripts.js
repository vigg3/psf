jQuery(document).ready(function ($) {
  $('#add-row').on('click', function () {
    var currentLength = $('#repeatable-tbody')[0].children.length;
    var row = $('.empty-row.screen-reader-text')
      .clone(true)
      .removeClass('empty-row screen-reader-text');
    row.insertBefore($('#repeatable-tbody>tr:last'));
    if (currentLength == 2) {
      $('#repeatable-tbody>tr:first>td:last').append(
        '<a class="button remove-row" href="#">Ta bort</a>',
      );
    }
    return false;
  });

  $('.remove-row').on('click', function () {
    $(this).parent().parent().remove();
    var currentLength = $('#repeatable-tbody')[0].children.length;
    if (currentLength == 2) {
      $('#repeatable-tbody>tr:first').find('.remove-row').remove();
    }
    return false;
  });

  $('.enabled_pages_wrapper').on(
    'click',
    'input[type="checkbox"]',
    function () {
      var currentPagesEl = $('#enabled_pages');
      var enabledPages = [];
      var newValue = $(this).val();

      if ($(currentPagesEl).val() === '') {
        enabledPages.push(newValue);
      } else {
        enabledPages = $(currentPagesEl).val().split(',');
        enabledPages.indexOf(newValue) === -1
          ? enabledPages.push(newValue)
          : enabledPages.splice(enabledPages.indexOf(newValue), 1);
      }

      $(currentPagesEl).attr('value', enabledPages.join(','));

      console.log('enabledPages: ', enabledPages);
    },
  );
});
