<x-forms.patch :action="route('frontend.user.business.profile.update', $business->id)">
    <div class="form-group row mb-1 mt-0">
        <div class="col-md-12 text-right">
            <button class="btn btn-sm btn-primary float-right" type="submit">@lang('Update')</button>
        </div>
    </div><!--form-group-->

    <div class="card">
        <div class="card-header">Business Details</div>
        <div class="card-body">
            <!-- name -->
            <div class="form-group row">
                <label for="name" class="col-md-3 col-form-label text-md-left">@lang('Name')</label>

                <div class="col-md-9">
                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        placeholder="{{ __('Name') }}"
                        value="{{ old('name') ?? $business->name }}"
                        required autofocus autocomplete="name" />
                </div>
            </div><!--form-group-->

            <!-- email -->
            <div class="form-group row">
                <label for="email" class="col-md-3 col-form-label text-md-left">@lang('Email')</label>

                <div class="col-md-9">
                    <input
                        type="text"
                        name="email"
                        class="form-control"
                        placeholder="{{ __('Email') }}"
                        value="{{ old('email') ?? $business->email }}"
                        required autofocus autocomplete="email" />
                </div>
            </div><!--form-group-->

            <!-- phone -->
            <div class="form-group row">
                <label for="phone" class="col-md-3 col-form-label text-md-left">@lang('Phone')</label>

                <div class="col-md-9">
                    <input
                        type="text"
                        name="phone"
                        class="form-control"
                        placeholder="{{ __('Phone') }}"
                        value="{{ old('phone') ?? $business->phone }}"
                        required autofocus autocomplete="phone" />
                </div>
            </div><!--form-group-->

            <!-- URL -->
            <div class="form-group row">
                <label for="url" class="col-md-3 col-form-label text-md-left">@lang('URL')</label>

                <div class="col-md-9">
                    <input
                        type="text"
                        name="url"
                        class="form-control"
                        placeholder="{{ __('URL') }}"
                        value="{{ old('url') ?? $business->url }}"
                        required autofocus autocomplete="url" />
                </div>
            </div><!--form-group-->

            <!-- timezone -->
            <div class="form-group row">
                <label for="timezone" class="col-md-3 col-form-label text-md-left">@lang('Timezone')</label>
                <div class="col-md-9">
                    <select type="select" name="timezone" class="form-control"
                        placeholder="{{ __('Timezone') }}"
                        required autofocus autocomplete="name" >
                        @foreach($timezoneList as $timezone)
                            <option
                                id="{{ $timezone }}"
                                @if( (old('timezone') ?? $business->timezone) == $timezone)
                                    selected="selected"
                                @endif

                            > {{ $timezone }} </option>
                        @endforeach
                    </select>
                </div>
            </div><!--form-group-->

            <!-- Currency -->
            <div class="form-group row">
                <label for="currency" class="col-md-3 col-form-label text-md-left">@lang('Currency')</label>
                <div class="col-md-9">
                    <select type="select" name="currency" class="form-control"
                            placeholder="{{ __('Currency') }}"
                            required autofocus autocomplete="name" >
                        @foreach($currencyList as $currency)
                            <option
                                id="{{ $currency['code'] }}"
                                @if( (old('currency') ?? $business->currency) == $currency['code'])
                                selected="selected"
                                @endif
                                value="{{ $currency['code'] }}"
                            > {{ $currency['symbol'] . ' - ' . $currency['code'] . ' - ' . $currency['name'] }} </option>
                        @endforeach
                    </select>
                </div>
            </div><!--form-group-->

        </div>
    </div>
    <!-- Address -->
    @include('frontend.includes.partials.address')

    <div class="form-group row mb-0">
        <div class="col-md-12 text-right">
            <button class="btn btn-sm btn-primary float-right" type="submit">@lang('Update')</button>
        </div>
    </div><!--form-group-->

</x-forms.patch>
