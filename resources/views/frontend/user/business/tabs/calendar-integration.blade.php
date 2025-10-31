<x-forms.patch :action="route('frontend.user.calendar.update')">
    <div class="form-group row">
        <label for="calendar_url" class="col-md-3 col-form-label text-md-right">@lang('Incoming Calendar URL (Holiday)')</label>

        <div class="col-md-9">
            <input type="text" name="calendar_url" class="form-control" placeholder="{{ __('ICS calendar URL') }}" maxlength="512" value="{{ Auth::user()->bookingSettings->get('calendar.url') }}"/>
        </div>
    </div><!--form-group-->

    <div class="form-group row">
        <label for="calendar_schedule_url" class="col-md-3 col-form-label text-md-right">@lang('Incoming Calendar URL (Schedule)')</label>

        <div class="col-md-9">
            <input type="text" name="calendar_schedule_url" class="form-control" placeholder="{{ __('ICS calendar URL') }}" maxlength="512" value="{{ Auth::user()->bookingSettings->get('calendar_schedule.url') }}"/>
        </div>
    </div><!--form-group-->

    <div class="form-group row">
        <label for="outgoing_calendar_url" class="col-md-3 col-form-label text-md-right">@lang('Outgoing Calendar URL')</label>

        <div class="col-md-9">
            <?php
            // https://elitebookingsystem.com/ical/156d9305-33fb-4e44-b494-398721fa7ed4
            $url = route('frontend.external.ical', auth()->user()->uuid);
            ?>

            <input
                style="width: 96%; display: inline-block;"
                type="text"
                name="outgoing_calendar_url"
                id="outgoing_calendar_url"
                class="form-control"
                value="{{ $url }}"
                maxlength="256"
                readonly="readonly"
                required/>
            <i onclick="copyOutgoingUrl()" class="cil-copy"></i>
        </div>
    </div><!--form-group-->
    <div class="form-group row mb-0">
        <div class="col-md-12 text-right">
            <button class="btn btn-sm btn-primary float-right" type="submit">@lang('Update Calendar')</button>
        </div>
    </div><!--form-group-->

</x-forms.patch>
