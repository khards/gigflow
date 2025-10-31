<?php

namespace App\Domains\Schedules\Controllers;

use App\Booking\Availability\Schedule;
use App\Booking\Business;
use App\Booking\Contracts\ScheduleManager as ScheduleManagerContract;
use App\Booking\Requests\Frontend\Schedule\Update;
use App\Http\Controllers\Controller;
use function __;
use function redirect;
use function view;

/**
 * Class ScheduleController.
 */
class ScheduleController extends Controller
{
    /**
     * @var ScheduleManagerContract|null
     */
    private $scheduleManager = null;

    /**
     * ScheduleController constructor.
     * @param ScheduleManagerContract $scheduleManager
     */
    public function __construct(ScheduleManagerContract $scheduleManager)
    {
        $this->scheduleManager = $scheduleManager;
    }

    /**
     * Get all schedules for business.
     *
     * @param $businessId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function all($businessId)
    {
        /** @var Business $business */
        $business = Business::findOrFail($businessId);

        if (! $this->authorize('viewAllByBusiness', [Schedule::class, $business])) {
            return redirect()->route('frontend.user.dashboard')->withFlashDanger(__('Invalid business, please try again.'));
        }

        $schedules = $this->scheduleManager->all($business);

        return view('frontend.user.schedules.schedules', compact(['business', 'schedules']));
    }

    /**
     * Return the edit a schedule screen.
     *
     * @param $scheduleId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit($scheduleId)
    {
        $schedule = $this->scheduleManager->read($scheduleId);

        if (! $this->authorize('view', $schedule)) {
            return redirect()->route('frontend.user.dashboard')->withFlashDanger(__('Invalid schedule, please try again.'));
        }

        return view('frontend.user.schedules.schedule', compact(['schedule']));
    }

    /**
     * Update a schedule.
     *
     * @param $scheduleId
     * @param Update $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update($scheduleId, Update $request)
    {
        $schedule = Schedule::findOrFail($scheduleId);

        if (! $this->authorize('update', $schedule)) {
            return redirect()->route('frontend.user.dashboard')->withFlashDanger(__('Invalid schedule, please try again.'));
        }

        $scheduleData = [];

        $scheduleData['start_datetime'] = $request->get('startDate').' '.$request->get('startTime');
        $scheduleData['end_datetime'] = $request->get('endDate').' '.$request->get('endTime');
        $scheduleData['summary'] = $request->get('schedule_summary', '');
        $scheduleData['state'] = $request->get('schedule_state', 'active');
        $scheduleData['rrule'] = $request->get('rrule', '');
        $scheduleData['timezone'] = $request->get('timezone');

        $this->scheduleManager->update($schedule, $scheduleData);

        return redirect()->route('frontend.user.schedule.edit', $schedule->id)->withFlashSuccess(__('Schedule successfully updated.'));
    }

    /**
     * Create a new schedule for a given business.
     *
     * @param $businessId
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create($businessId)
    {
        $business = Business::findOrFail($businessId);

        if (! $this->authorize('create', [Schedule::class, $business])) {
            return redirect()->route('frontend.user.dashboard')->withFlashDanger(__('Invalid business, please try again.'));
        }

        $schedule = $this->scheduleManager->create($business, [
            'summary' => 'New schedule',
            'state' => 'draft',
            'rrule' => '',
            'timezone' => '',
            'start_datetime' => '',
            'end_datetime' => '',
        ]);

        return redirect()->route('frontend.user.schedule.edit', $schedule->id)->withFlashSuccess(__('Schedule successfully created.'));
    }

//    public function delete($scheduleId)
//    {
//        $schedule = Schedule::findOrFail($scheduleId);
//        $businessId = $schedule->model_id;
//
//        if (! $this->authorize('delete', $schedule)) {
//            return redirect()->route('frontend.user.dashboard')->withFlashDanger(__('Invalid schedule, please try again.'));
//        }
//
//        $this->scheduleManager->delete($schedule);
//
//        return redirect()->route('frontend.user.schedules.view', ['businessId' => $businessId]);
//    }
}
