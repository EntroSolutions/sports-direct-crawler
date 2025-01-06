<!-- select2 from array -->
@php
    $field['allows_null'] = $field['allows_null'] ?? $crud->model::isColumnNullable($field['name']);
@endphp
@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    <select
        name="{{ $field['name'] }}@if (isset($field['allows_multiple']) && $field['allows_multiple']==true)[]@endif"
        style="width: 100%"
        data-init-function="bpFieldInitSelect2FromArrayElement"
        data-field-is-inline="{{var_export($inlineCreate ?? false)}}"
        data-language="{{ str_replace('_', '-', app()->getLocale()) }}"
        @include('crud::fields.inc.attributes', ['default_class' =>  'form-control select2_from_array'])
        @if (isset($field['allows_multiple']) && $field['allows_multiple']==true)multiple @endif
        >

        @if ($field['allows_null'])
            <option value="">-</option>
        @endif

        @if (count($field['options']))
            @foreach ($field['options'] as $key => $value)
                @if((old(square_brackets_to_dots($field['name'])) !== null && (
                        $key == old(square_brackets_to_dots($field['name'])) ||
                        (is_array(old(square_brackets_to_dots($field['name']))) &&
                        in_array($key, old(square_brackets_to_dots($field['name'])))))) ||
                        (null === old(square_brackets_to_dots($field['name'])) &&
                            ((isset($field['value']) && (
                                        $key == $field['value'] || (
                                                is_array($field['value']) &&
                                                in_array($key, $field['value'])
                                                )
                                        )) ||
                                (!isset($field['value']) && isset($field['default']) &&
                                ($key == $field['default'] || (
                                                is_array($field['default']) &&
                                                in_array($key, $field['default'])
                                            )
                                        )
                                ))
                        ))
                    <option value="{{ $key }}" selected>{{ $value }}</option>
                @else
                    <option value="{{ $key }}">{{ $value }}</option>
                @endif
            @endforeach
        @endif
    </select>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
    <!-- include select2 css-->
    <link href="{{ asset('packages/select2/dist/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('packages/select2-bootstrap-theme/dist/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    <!-- include select2 js-->
    <script src="{{ asset('packages/select2/dist/js/select2.full.min.js') }}"></script>
    @if (app()->getLocale() !== 'en')
    <script src="{{ asset('packages/select2/dist/js/i18n/' . str_replace('_', '-', app()->getLocale()) . '.js') }}"></script>
    @endif
    <script>
        function bpFieldInitSelect2FromArrayElement(element) {
            if (!element.hasClass("select2-hidden-accessible"))
                {
                    let $isFieldInline = element.data('field-is-inline');

                    element.select2({
                        theme: "bootstrap",
                        dropdownParent: $isFieldInline ? $('#inline-create-dialog .modal-content') : document.body
                    }).on('select2:unselect', function(e) {
                        if ($(this).attr('multiple') && $(this).val().length == 0) {
                            $(this).val(null).trigger('change');
                        }
                    });
                }
        }


        let isDeliverySelected = false;

        $(document).on('change', 'select[name="{{ $field['depending_select'] }}"]', function (e) {
            setSettings($(this).val());

            // Delivery optoins variants
            if($(document).find('select[name="{{ $field['depending_select'] }}"]').val() == 'delivery'){

                let html = '<div class="form-group col-sm-12 required" id="delivery_options" element="div">';
                html += '<label>Delivery options</label>';

                html += '<select ' +
                'name="feature_variant_select" ' +
                'style="width: 100%" ' +
                'data-init-function="bpFieldInitSelect2FromArrayElement" ' +
                'data-field-is-inline="false" ' +
                'data-language="en">';

                html += '</select>';

                html += '</div>';


                $(html).insertAfter($(document).find('.form-group.col-sm-12.required').last());

                $(document).find('select[name="feature_variant_select"]').select2({
                    theme: "bootstrap",
                    dropdownParent: document.body
                }).on('select2:unselect', function(e) {
                    if ($(this).attr('multiple') && $(this).val().length == 0) {
                        $(this).val(null).trigger('change');
                    }
                });

                isDeliverySelected = true;

            } else {
                $(document).find('#delivery_options').remove();
                isDeliverySelected = false;
            }

            if(isDeliverySelected){
                $(document).on('change', 'select[name="feature_id"]', function(){
                    setDeliveryOptions($(this).val());
                })
            }

        });

        function setSettings(type) {
            $.ajax({
                url: "{{ route('cscartsetting.getCsCartFeatures') }}?feature_type=" + type,
                method: "get"
            })
            .done(function (data) {

                let options = '';

                $.each(data, function (value, text) {
                    options += '<option value="'+value+'">'+text+'</option>';
                });

                $(document).find('select[name="{{ $field['name'] }}"]').html(options);
            });
        }

        function setDeliveryOptions(setting_id) {
            $.ajax({
                url: "{{ route('cscartsetting.getCsCartFeatureById') }}?setting_id=" + setting_id,
                method: "get",
            })
                .done(function (data) {

                    let options = '<option value="">Please select</option>';

                    $.each(data, function (value, text) {
                        options += '<option value="'+value+'">'+text+'</option>';
                    });

                    $(document).find('select[name="feature_variant_select"]').html(options);
                });
        }

        $(document).on('change', 'select[name="feature_variant_select"]', function(){
            $(document).find('input[name="feature_variant_id"]').val($(this).val());
        });
    </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
