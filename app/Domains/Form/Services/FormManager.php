<?php

namespace App\Domains\Form\Services;

use App\Booking\Business;
use App\Booking\Product;
use App\Domains\Form\Contracts\FormManager as FormManagerContract;
use App\Domains\Form\Contracts\FormResponseManager;
use App\Domains\Form\Models\Form;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class FormManager implements FormManagerContract
{
    private $formResponseManager;

    public function __construct(FormResponseManager $formResponseManager)
    {
        $this->formResponseManager = $formResponseManager;
    }

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'action' => [
            'type' => '',
            'logic_question_name' => '',
            'logic_response' => '',
            'logic_form' => '',
        ],
    ];

    /*
        required                // required 1 or 0
        type                    // empty or 'next' (show next form)
        logic_question_name     // Question name to perform logic on
        logic_response          // Unused?
        logic_form.*            // When "Skip to next form" not based on response, this is the form to skip to.

        'logic' => array (
            'wedding' => '20',
            'civil ceremony' => '20',
            'birthday' => '21',
            'corporate' => '45',
            'other' => '45',
        ),

     */

    /**
     * Create single form.
     *
     * @param $business
     * @param array $formDetails
     * @return Form
     */
    public function create($business, array $formDetails): Form
    {
        if (! is_object($business)) {
            $business = Business::findOrFail($business);
        }

        if (empty($formDetails['action'])) {
            $formDetails['action'] = $this->attributes['action'];
        }

        $form = Form::create([
            'name' => $formDetails['name'] ?? 'New Form',
            'owner_type' => $business->getMorphClass(),
            'owner_id' => $business->id,
            'data' => json_encode($formDetails['data'] ?? []),
            'action' => $formDetails['action'],
            'settings' => $formDetails['settings'] ?? [],
            'required' => false,
        ]);
        $this->update($form, $formDetails); // ['name','data','action','settings', 'required'])

        return $form;
    }

    /**
     * Read a form.
     *
     * @param $id
     * @return Form
     */
    public function read($id): Form
    {
        return Form::findOrFail($id);
    }

    /**
     * Get all forms for a business, optionally filter.
     *
     * @param Business $business
     * @param array $filter
     * @return Collection
     */
    public function all($business, $filter = []): Collection
    {
        if (! is_object($business)) {
            $business = Business::findOrFail($business);
        }

        return $business->forms()->get();
    }

    /**
     * Update a form.
     *
     * @param $form
     * @param array $formDetails
     * @return Form
     */
    public function update($form, array $formDetails): Form
    {
        if (! is_object($form)) {
            $form = $this->read($form);
        }
        $form->update(collect($formDetails)->only(['name', 'data', 'action', 'settings', 'required'])->toArray());

        return $form;
    }

    /**
     * Create a  copy / clone.
     *
     * @param Form|int $form
     *
     * @return Form
     */
    public function clone($form): Form
    {
        if (! is_object($form)) {
            $form = $this->read($form);
        }

        //Clone it
        $clone = $form->replicate(['id']);
        $clone->save();

        //Update it's name after save as I don't know which id it is
        $clone->name = trim($clone->name).' '.$clone->id;
        $clone->save();

        return $clone;
    }

    /**
     * @param Form|int $form
     * @return void
     */
    public function delete($form): void
    {
        if (! is_object($form)) {
            $form = Form::findOrFail($form);
        }
        $form->delete();
    }

    // @TODO

    /**
     * Returns the next form ID.
     *
     * @param int $businessId
     * @param string $cartId
     * @param array|\stdClass $formData
     * @param array $prodcutIds
     * @param array $visitedFormIds
     *
     * @return int
     */
    public function getNextFormId(int $businessId, string $cartId, array|\stdClass $formData, array $productIds, array $visitedFormIds): ?int
    {
        $requiredFormIds = $this->getAllRequiredFormIds($businessId, $productIds);

        // No forms to complete.
        if (empty($requiredFormIds)) {
            return null;
        }

        // If current form id == 0, return first form
        if (empty($visitedFormIds)) {
            return $requiredFormIds[0];
        }

        // Must be submitting a form, Get the submitted form ID
        $submittedFormId = end($visitedFormIds);

        // Skip to a form based on submitted form
        if (! empty($formData)) {
            if ($skipToFormId = $this->formResponseManager->skipToForm($formData, $submittedFormId)) {
                return $skipToFormId;
            }
        }

        // Form logic skip to another form
        if($skipToPageNextPage = $this->skipToFormByFormLogicNextPage($formData, $submittedFormId)) {
            return $skipToPageNextPage;
        }

        // Get the current form index
        $currentPosition = array_search($submittedFormId, $requiredFormIds);

        // If [submitted form ID] is not in the required formIDs, then we are currently submitting a skiped form,
        // Find the first form we haven't vised in the back array.
        if ($currentPosition === false) {
            foreach ($requiredFormIds as $formId) {
                if (array_search($formId, $visitedFormIds) === false) {
                    return $formId;
                }
            }
            // We have completed all of the required form Id's
            return null;
        }

        // Must be the next form ID.
        if (($currentPosition + 1) < count($requiredFormIds)) {
            return $requiredFormIds[$currentPosition + 1];
        }

        // All forms completed, so return null
        return null;
    }

    /**
     * Skip to a form based on logic skip to form (Not based on submission, but the form logic)
     *
     * @param $formData
     * @param $submittedFormId
     * @return void
     */
    private function skipToFormByFormLogicNextPage($formData, $submittedFormId) {
        if (! empty($formData)) {
            $form = Form::find($submittedFormId);
            if ($form) {
                $action = $form->action;
                $action = (object)$action->toArray();//works live.
                if ( $action) {
                    // It is possible to simply skip to another form, not based on a response.
                    // form->action = '{"type":"next","logic_question_name":"","logic_response":"","logic_form":"43","logic":[]}'
                    if (property_exists($action, 'type') && property_exists($action, 'logic_form') && $action->type ==='next') {
                        if (Form::where('id', '=', $action->logic_form)->count() > 0) {
                            return $action->logic_form;
                        }
                    }
                }
            }
        }
    }

    /**
     * Get all required form id's for products and business in order.
     *
     * @param int $businessId
     * @param array $productIds
     */
    public function getAllRequiredFormIds(int $businessId, array $productIds): array
    {
        $business = Business::findOrFail($businessId);

        // Get all forms for business, where the forms are required.
        $requiredForms = $business->forms()->required()->shared(false)->get();
        $requiredFormIds = $requiredForms->pluck('id');

        // Get all the form id's for the products in the cart
        $productFormIds = $this->getProductFormIds($productIds);

        // Merge and unique the product id's
        $merged = $requiredFormIds->merge($productFormIds);
        $unique = $merged->unique();

        // Shared required forms such as required user registration details.
        // If any of the required forms have the same name as the shared forms then they will override them
        // May review this sometime as the forms now have a type, however that is not yet user accessible.
        $sharedRequiredForms = $this->getSharedRequiredForms();

        // Filter the shared required forms that have the same name as the user form.
        // This allows the user to override contact form etc.
        $forms = Form::whereIn('id', $unique)->get();
        $forms->each(function ($requiredForm) use ($sharedRequiredForms) {
            $sharedRequiredForms->filter(function ($sharedForm) use ($requiredForm) {
                return $requiredForm->name != $sharedForm->name;
            });
        });
        $merged = $unique->merge($sharedRequiredForms->pluck('id'));
        $ids = $merged->toArray();

        return $ids;
    }

    /**
     * TODO@ This is just used in a test.
     *
     * @deprecated
     * Get all forms for business, where the forms are required.
     */
    public function getRequiredFormIds(int $businessId): Collection
    {
        // Get all forms for business, where the forms are required.
        $business = Business::findOrFail($businessId);

        return
            $business->forms()->
            required()->
            get(['id'])->
            pluck('id')->
            map(function ($item, $key) {
                return (int) $item;
            });
    }

    /**
     * Get products associated form id's.
     */
    public function getProductFormIds(array $productIds): Collection
    {
        $productIds =
            Product::whereNotNull('form_id')->
            whereIn('id', $productIds)->
            get(['form_id'])->
            pluck('form_id')->
            map(function ($item, $key) {
                return (int) $item;
            });

        return $productIds;
    }

    /**
     * Get shared required forms.
     *
     * @return Collection
     */
    public function getSharedRequiredForms(): Collection
    {
        return Form::shared()->required()->get();
    }
}
