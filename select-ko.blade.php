@props([
'clearable' => false,
'search' => true,
'id' => Str::uuid(),
'options' => [],
'entangle' => '',
'label' => false,
'name' => null,
'required' => false,
'inline' => false,
])

<div>

    <div x-data="{value: @entangle($attributes->wire('model')),
                 options: {{json_encode($options)}},
                 select: null,
                 items: [],
                 initialItem: null,
                 initialItems: [],
                 pageSize: 50}"

         x-init="

                select = $('#select{{ $id }}');

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

                                    //value = event.target.value

                                    value = select.val();

                               })
                               .on('select2:unselect', (event) => {

                                    value = select.val();

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

                $watch('value', () => {

                    select.empty();

                    if (Array.isArray(value)) {

                        value.forEach(function(v){

                            var item = options.find(function(item){
                                return item.value == v;
                            });

                            if (item) {
                                select.append(new Option(item.name, item.value, true, true));
                            }

                        });

                    } else {

                        var item = options.find(function(item){
                            return item.value == value;
                        });

                        if (item) {
                            select.append(new Option(item.name, item.value, true, true));
                        }

                    }

                    select.val(value).trigger('change');

                });

                $watch('options', () => {

                    items = [];

                    options.forEach(function (item) {
                        items.push({id: item.value, text: item.name})
                    });

                    select.val(value).trigger('change');

                });

                ">

        <div
            @if($inline) class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5" @endif>

            @if ($label)

                <label for="{{$id}}"
                       class="block text-sm font-medium text-gray-500 relative @if($inline)sm:mt-px sm:pt-2 @endif">
                    {{$label}} @if($required) <span class="text-red-500">*</span> @endif
                </label>

            @endif

            <div class="col-span-2 @error($name) has-errors @enderror">

                <div class="mt-1 relative rounded shadow-sm" wire:ignore>

                    <select id="select{{$id}}"
                            name="value[]"
                            x-model="value"
                            class="block w-full focus:outline-none sm:text-sm rounded border-gray-400 py-1"
                            style="width: 100%"
                        {{ $attributes->whereDoesntStartWith('wire:model') }}>
                    </select>

                </div>

                <div>
                    @error($name)
                    <p class="mt-2 text-sm text-red-600 is-error">{{$message}}</p>
                    @enderror
                </div>

            </div>

        </div>

    </div>

</div>

