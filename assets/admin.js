jQuery(document).ready(function($) {
    const formFields = $('.papaki-custom-class');
    if (formFields.length > 0) {
        formFields
            .first()
            .before('<div class="papaki-custom-class">')
            .end()
            .last()
            .after('</div>');
    }
});
