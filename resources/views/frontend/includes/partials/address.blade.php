<div class="card">
    <div class="card-header">Business Address</div>
    <div class="card-body">

        <!-- name / addressee -->
        <div class="form-group row">
            <label for="address[name]" class="col-md-3 col-form-label text-md-left">@lang('Addressee')</label>

            <div class="col-md-9">
                <input
                    type="text"
                    name="address[name]"
                    class="form-control"
                    placeholder="{{ __('Name') }}"
                    value="{{ old('name') ?? $business->address->name }}"
                    required
                    autocomplete="name" />
            </div>
        </div><!--form-group-->

        <!-- Address -->
        <div class="form-group row">
            <label for="address[address]" class="col-md-3 col-form-label text-md-left">@lang('Address / Street')</label>

            <div class="col-md-9">
                <input
                    type="text"
                    name="address[address]"
                    class="form-control"
                    placeholder="{{ __('Address / Street') }}"
                    value="{{ old('address') ?? $business->address->address }}"
                    required
                    autocomplete="street-address" />
            </div>
        </div><!--form-group-->

        <!-- city / region -->
        <div class="form-group row">
            <label for="address[city]" class="col-md-3 col-form-label text-md-left">@lang('City / Region')</label>

            <div class="col-md-9">
                <input
                    type="text"
                    name="address[city]"
                    class="form-control"
                    placeholder="{{ __('City') }}"
                    value="{{ old('city') ?? $business->address->city }}"
                    required
                    autocomplete="city" />
            </div>
        </div><!--form-group-->

        <!-- postalcode -->
        <div class="form-group row">
            <label for="address[postalcode]" class="col-md-3 col-form-label text-md-left">@lang('Postcode / Zip')</label>

            <div class="col-md-9">
                <input
                    type="text"
                    name="address[postalcode]"
                    class="form-control"
                    placeholder="{{ __('Postcode / Zip') }}"
                    value="{{ old('postalcode') ?? $business->address->postalcode }}"
                    required
                    autocomplete="postal-code" />
            </div>
        </div><!--form-group-->

        <!-- country -->
        <div class="form-group row">
            <label for="address[country_id]" class="col-md-3 col-form-label text-md-left">@lang('Country')</label>
            <div class="col-md-9">
                <select type="select"
                        name="address[country_id]"
                        class="form-control"
                        placeholder="{{ __('Country') }}"
                        required autocomplete="country" >
                    @foreach($countries as $country)
                        <option
                            value="{{ $country->id }}"
                            @if( (old('country') ?? $business->address->country_id) == $country->id)
                                selected="selected"
                            @endif
                        >{{ $country->name }} (+{{ $country->phonecode }})</option>
                    @endforeach
                </select>
            </div>
        </div><!--form-group-->

    </div>
</div>
