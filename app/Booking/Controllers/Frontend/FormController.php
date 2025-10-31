<?php

namespace App\Booking\Controllers\Frontend;

use App\Booking\Business;
use App\Booking\Contracts\ScheduleManager as ScheduleManagerContract;
use App\Domains\Form\Contracts\FormManager as FormManagerContract;
use App\Domains\Form\Models\Form;
use App\Http\Controllers\Controller;


/**
 *     // Forms.
Route::get('form/{formId}', [FormController::class, 'edit'])->name('form.edit');
Route::post('form/{formId}', [FormController::class, 'update'])->name('form.update');
Route::delete('form/{formId}', [FormController::class, 'delete'])->name('form.delete');
Route::post('form/create/{businessId}', [FormController::class, 'create'])->name('form.create');
Route::post('form/clone/{formId}', [FormController::class, 'clone'])->name('form.clone');
Route::get('business/{businessId}/forms', [FormController::class, 'index'])->name('forms.view');
 */

/**
 * Class FormController.
 */
class FormController extends Controller
{
    /**
     * @var FormManagerContract
     */
    private $formManager;

    /**
     * FormController constructor.
     *
     * @param FormManagerContract $formManager
     * @param ScheduleManagerContract $scheduleManager
     */
    public function __construct(FormManagerContract $formManager)
    {
        $this->formManager = $formManager;
    }

    /**
     * Get all forms and render a table.
     *
     * @param $businessId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Business $businessId)
    {
        $business = $businessId;

        return view('frontend.user.forms.forms', compact(['business']));
    }

    /**
     * Return the edit a form screen.
     *
     * @param Form $form
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit($formId)
    {
        $form = Form::findOrFail($formId);

        return view('frontend.user.forms.edit', compact(['form']));
    }

    /**
     * Update a form.
     *
     * @param $formId
     * @param Update $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update($formId, Update $request)
    {
    }

    /**
     * Create a new form.
     *
     * @param $businessId
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create($businessId)
    {
    }

    /**
     * Delete form (soft delete).
     *
     * @param $formId
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delete($formId)
    {
    }
}
