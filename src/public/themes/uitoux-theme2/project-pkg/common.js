app.factory("ProjectPkgHelper", function() {
    return {
        saveFilter: function() {
            var new_preset_form = '#new-preset-form';

            var v = jQuery(new_preset_form).validate({
                ignore: '',
                rules: {
                    'page_id': {
                        required: true,
                        number: true,
                    },
                    'name': {
                        required: true,
                    },
                    'value': {
                        required: true,
                    },
                },
                invalidHandler: function(event, validator) {
                    console.log(validator.errorList);
                },
                submitHandler: function(form) {
                    // self.filter_value = angular.toJson(self.filter);
                    // $('#filter_value').val(angular.toJson(self.filter));
                    let formData = new FormData($(new_preset_form)[0]);
                    $('#submit').button('loading');
                    $.ajax({
                            url: laravel_routes['saveFilterPreset'],
                            method: "POST",
                            data: formData,
                            processData: false,
                            contentType: false,
                        })
                        .done(function(res) {
                            $('#submit').button('reset');
                            if (!res.success) {
                                showErrorNoty(res);
                                return;
                            }
                            custom_noty('success', res.message);
                            $('#filter-modal').modal('hide');
                        })
                        .fail(function(xhr) {
                            $('#submit').button('reset');
                            custom_noty('error', 'Something went wrong at server');
                        });
                }
            });
            // }
        },

    }
});
