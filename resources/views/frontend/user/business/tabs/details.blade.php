<div class="table-responsive">
    <table class="table table-striped table-hover table-bordered mb-0">
        <tr>
            <th>@lang('Name')</th>
            <td>{{ $business->name }}</td>
        </tr>
        <tr>
            <th>@lang('Email')</th>
            <td>{{ $business->email }}</td>
        </tr>
        <tr>
            <th>@lang('Phone')</th>
            <td>{{ $business->phone }}</td>
        </tr>
        <tr>
            <th>@lang('URL')</th>
            <td>{{ $business->url }}</td>
        </tr>
        <tr>
            <th>@lang('Timezone')</th>
            <td>{{ $business->timezone ? str_replace('_', ' ', $business->timezone) : __('N/A') }}</td>
        </tr>
        <tr>
            <th>@lang('Currency')</th>
            <td>{{ $business->currency ? str_replace('_', ' ', $business->currency) : __('N/A') }}</td>
        </tr>
        <tr>
            <th>@lang('Account Created')</th>
            <td>@displayDate($business->created_at) ({{ $business->created_at->diffForHumans() }})</td>
        </tr>

        <tr>
            <th>@lang('Last Updated')</th>
            <td>@displayDate($business->updated_at) ({{ $business->updated_at->diffForHumans() }})</td>
        </tr>
    </table>
</div><!--table-responsive-->
