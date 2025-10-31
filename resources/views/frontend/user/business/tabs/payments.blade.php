
<x-forms.patch :action="route('frontend.user.business.profile.update.payments', $business->id)">
    <div class="form-group row mb-1 mt-0">
        <div class="col-md-12 text-right">
            <button class="btn btn-sm btn-primary float-right" type="submit">@lang('Update')</button>
        </div>
    </div><!--form-group-->

    <div class="card">
        <div class="card-header">Paypal Payment Details</div>
        <div class="card-body">

            <div class="form-group row">
                <label for="account" class="col-sm-4 col-form-label text-md-left">@lang('Account')</label>
                <div class="col-sm-8">
                    <input type="text" name="paypal[account]" class="form-control" placeholder="{{ __('Account') }}" value="{{ old('account') ?? $paypal->get('account') }}" required />
                </div>
            </div>

            <div class="form-group row">
                <label for="clientId" class="col-sm-4 col-form-label text-md-left">@lang('Client Id')</label>
                <div class="col-sm-8">
                    <input type="text" name="paypal[clientId]" class="form-control" placeholder="{{ __('Client Id') }}" value="{{ old('clientId') ?? $paypal->get('clientId') }}" required />
                </div>
            </div>

            <div class="form-group row">
                <label for="descriptor" class="col-sm-4 col-form-label text-md-left">@lang('Payment Descriptor (text on statement)')</label>
                <div class="col-sm-8">
                    <input type="text" name="paypal[descriptor]" class="form-control" placeholder="{{ __('Descriptor') }}" value="{{ old('descriptor') ?? $paypal->get('descriptor') }}" required />
                </div>
            </div>

            <div class="form-group row">
                <label for="currency" class="col-sm-4 col-form-label text-md-left">@lang('Currency')</label>
                <div class="col-sm-8">
                    <select name="paypal[currency]">
                        <?php $selected = old('currency') ?? $paypal->get('currency'); ?>
                        @foreach (Symfony\Component\Intl\Currencies::getCurrencyCodes() as $symbol)
                            <option
                                @if($selected === $symbol)
                                    selected="selected"
                                @endif
                                value="{{ $symbol }}">{{ $symbol }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <hr />

            <div class="form-group row">
                <label for="secret" class="col-sm-4 col-form-label text-md-left">@lang('Secret')</label>
                <div class="col-sm-8">
                    <input type="text" name="paypal[secret]" class="form-control" placeholder="{{ __('Secret') }}" value="{{ old('secret') ?? $paypal->get('secret') }}" required />
                </div>
            </div>

            <div class="form-group row">
                <label for="app_id" class="col-sm-4 col-form-label text-md-left">@lang('App Id')</label>
                <div class="col-sm-8">
                    <input type="text" name="paypal[app_id]" class="form-control" placeholder="{{ __('App Id') }}" value="{{ old('app_id') ?? $paypal->get('app_id') }}" required />
                </div>
            </div>

            <div class="form-group row">
                <label for="webhook_id" class="col-sm-4 col-form-label text-md-left">@lang('Webhook Id')</label>
                <div class="col-sm-8">
                    <input type="text" name="paypal[webhook_id]" class="form-control" placeholder="{{ __('Webhook Id') }}" value="{{ old('webhook_id') ?? $paypal->get('webhook_id') }}" required />
                </div>
            </div>


        </div>
    </div>

    <div class="card">
        <div class="card-header">Bank Payment Details</div>
        <div class="card-body">

            <!-- Bank Name -->
            <div class="form-group row">
                <label for="bankName" class="col-sm-4 col-form-label text-md-left">@lang('Bank Name')</label>

                <div class="col-sm-8">
                    <input
                        type="text"
                        name="bank[name]"
                        class="form-control"
                        placeholder="{{ __('Bank Name') }}"
                        value="{{ old('bankName') ?? $bank->get('name') }}"
                        required autofocus autocomplete="bankName" />
                </div>
            </div>

            <!-- Payee Name -->
            <div class="form-group row">
                <label for="payee" class="col-sm-4 col-form-label text-md-left">@lang('Payee Name')</label>

                <div class="col-sm-8">
                    <input
                        type="text"
                        name="bank[payee]"
                        class="form-control"
                        placeholder="{{ __('Payee Name') }}"
                        value="{{ old('payee') ?? $bank->get('payee') }}"
                        required autofocus autocomplete="payee" />
                </div>
            </div>

            <!-- Bank Account -->
            <div class="form-group row">
                <label for="bankAccount" class="col-sm-4 col-form-label text-md-left">@lang('Account number')</label>

                <div class="col-sm-8">
                    <input
                        type="text"
                        name="bank[account]"
                        class="form-control"
                        placeholder="{{ __('Account number') }}"
                        value="{{ old('bankAccount') ?? $bank->get('account') }}"
                        required autofocus autocomplete="payee" />
                </div>
            </div>

            <!-- Sortcode -->
            <div class="form-group row">
                <label for="sortcode" class="col-sm-4 col-form-label text-md-left">@lang('Sortcode')</label>

                <div class="col-sm-8">
                    <input
                        type="text"
                        name="bank[sortcode]"
                        class="form-control"
                        placeholder="{{ __('Sortcode') }}"
                        value="{{ old('sortcode') ?? $bank->get('sortcode') }}"
                        required autofocus autocomplete="sortcode" />
                </div>
            </div>

        </div>
    </div>


    <div class="form-group row mb-0">
        <div class="col-md-12 text-right">
            <button class="btn btn-sm btn-primary float-right" type="submit">@lang('Update')</button>
        </div>
    </div><!--form-group-->

</x-forms.patch>
