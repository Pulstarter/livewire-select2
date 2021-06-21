@props([
'clearable' => false,
'search' => true,
'id' => Str::random(),
'value' => null,
'label' => false,
'name' => null,
'options' => null
])

<div x-data="{
              value: @entangle($attributes->wire('model')),
              options: @entangle($options),
              select: null,
              items: [],
              initialItem: null,
              initialItems: [],
              pageSize: 50,
             }"

     x-init="

            $nextTick(() => {

                select = $('#select{{$id}}');

                items = [];

                options.forEach(function (item) {

                    if (Array.isArray(value)) {

                        if (value.indexOf(item.value.toString()) != -1) {
                            initialItems.push(item)
                        }

                    } else {
                        if (item.value === value) {
                            initialItem = item;
                        }
                    }

                    items.push({id: item.value, text: item.name})

                });

                jQuery.fn.select2.amd.require(['select2/data/array', 'select2/utils'],

                function (ArrayData, Utils) {

                    function CustomData($element, options) {
                        CustomData.__super__.constructor.call(this, $element, options);
                    }

                    Utils.Extend(CustomData, ArrayData);

                    CustomData.prototype.query = function (params, callback) {

                        results = [];

                        if (params.term && params.term !== '') {
                            results = _.filter(items, function (e) {
                                return e.text.toUpperCase().indexOf(params.term.toUpperCase()) >= 0;
                            });
                        } else {
                            results = items;
                        }

                        if (!('page' in params)) {
                            params.page = 1;
                        }

                        var data = {};
                        data.results = results.slice((params.page - 1) * pageSize, params.page * pageSize);
                        data.pagination = {};
                        data.pagination.more = params.page * pageSize < results.length;
                        callback(data);

                    };

                    $(document).ready(function () {

                        select.select2({
                                    ajax: {},
                                    dataAdapter: CustomData,
                                    allowClear: '{{ $clearable }}',
                                    minimumResultsForSearch: '{{ ($search) ? 0 : -1  }}',
                               })
                               .on('select2:select', (event) => {

                                    value = $('#select{{$id}}').val();

                               })
                               .on('select2:unselect', (event) => {

                                    value = $('#select{{$id}}').val();

                               });

                        // Preselección simple
                        if (initialItem) {

                            select.append(new Option(initialItem.name, initialItem.value, true, true));

                        }

                        // Preselección múltiple
                        if (initialItems.length > 0) {

                            initialItems.forEach(function(item){
                                select.append(new Option(item.name, item.value, true, true));
                            });

                        }

                    });

                });

             });

                $watch('value', () => {

                    $('#select{{$id}}').empty();

                    if (Array.isArray(value)) {

                        value.forEach(function(v){

                            var item = options.find(function(item){
                                return item.value == v;
                            });

                            if (item) {
                                $('#select{{$id}}').append(new Option(item.name, item.value, true, true));
                            }

                        });

                    } else {

                        var item = options.find(function(item){
                            return item.value == value;
                        });

                        if (item) {
                            $('#select{{$id}}').append(new Option(item.name, item.value, true, true));
                        }

                    }

                });

                $watch('options', () => {

                    if (Array.isArray(value)) {

                        if (items.every(elem => value.indexOf(elem) > -1)) {
                            value = 0;
                        }

                    } else {

                        var option_exists = items.find(function(s){
                            return s.id == value
                        })

                        if (!option_exists) {
                            value = 0;
                        }

                    }

                });

                ">

    <div

        @if ($label)

            <label for="{{$id}}">
                {{$label}}
            </label>

        @endif

        <div>

            <div wire:ignore>

                <select id="select{{$id}}"
                        name="value[]"
                        x-model="value"
                    {{ $attributes->whereDoesntStartWith('wire:model') }}>
                </select>

            </div>

        </div>

    </div>

</div>

