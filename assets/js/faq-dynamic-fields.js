var max_fields = 10; //maximum input boxes allowed
var wrapper = jQuery('.psf-faq-wrapper'); //Fields wrapper

var x = 1; //initlal text box count
jQuery('.add_field_button').click(function (e) {
  e.preventDefault();
  if (x < max_fields) {
    //max input box allowed
    x++; //text box increment
    jQuery(wrapper).append(`
    <div class="psf-faq-wrapper">
      <div class="psf-question-wrapper">
        <label for="psf_faq_question[]">Fråga</label>
        <input type="text" name="psf_faq_question[]" id="psf_faq_question[]">
      </div>
      <div class="psf-answer-wrapper">
        <label for="psf_faq_answer[]">Svar</label>
        <textarea type="text" name="psf_faq_answer[]" id="psf_faq_answer[]"></textarea>
      </div>
      <a href="#" class="remove_field">Ta bort</a>
    </div>
    `);
  }
});

jQuery(wrapper).on('click', '.remove_field', function (e) {
  //user click on remove text
  e.preventDefault();
  jQuery(this).parent('div').remove();
  x--;
});
