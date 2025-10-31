<?php

use App\Booking\Business;
use App\Domains\Auth\Models\User;
use App\Domains\Form\Models\Form;
use App\Http\Livewire\Forms\FormLogic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormLogicLivewireTest extends TestCase
{
    use RefreshDatabase;

    private function formData()
    {
        return [
            0 => (object) [
               'type' => 'header',
               'subtype' => 'h1',
               'label' => 'Header',
               'access' => false,
            ],
            1 => (object) [
               'type' => 'text',
               'required' => false,
               'label' => 'First Name',
               'className' => 'form-control',
               'name' => 'firstname',
               'access' => false,
               'subtype' => 'text',
            ],
            2 => (object) [
               'type' => 'text',
               'required' => false,
               'label' => 'Last Name',
               'className' => 'form-control',
               'name' => 'lastname',
               'access' => false,
               'subtype' => 'text',
            ],
            3 => (object) [
               'type' => 'autocomplete',
               'required' => false,
               'label' => 'Event Type',
               'className' => 'form-control',
               'name' => 'event-type',
               'access' => false,
               'requireValidOption' => false,
               'values' => [
                0 => (object) [
                   'label' => 'Wedding',
                   'value' => 'wedding',
                   'selected' => true,
                ],
                1 => (object) [
                   'label' => 'Birthday',
                   'value' => 'birthday',
                   'selected' => false,
                ],
                2 => (object) [
                   'label' => 'Other',
                   'value' => 'other',
                   'selected' => false,
                ],
              ],
            ],
            4 => (object) [
               'type' => 'radio-group',
               'required' => false,
               'label' => 'Radio Group',
               'inline' => false,
               'name' => 'radio-group-1629966624736',
               'access' => false,
               'other' => false,
               'values' => [
                0 => (object) [
                   'label' => 'Option 1',
                   'value' => 'option-1',
                   'selected' => false,
                ],
                1 => (object) [
                   'label' => 'Option 2',
                   'value' => 'option-2',
                   'selected' => false,
                ],
                2 => (object) [
                   'label' => 'Option 3',
                   'value' => 'option-3',
                   'selected' => false,
                ],
              ],
            ],
            5 => (object) [
               'type' => 'select',
               'required' => false,
               'label' => 'Select',
               'className' => 'form-control',
               'name' => 'select-1629966628848',
               'access' => false,
               'multiple' => false,
               'values' => [
                0 => (object) [
                   'label' => 'Option 1',
                   'value' => 'option-1',
                   'selected' => true,
                ],
                1 => (object) [
                   'label' => 'Option 2',
                   'value' => 'option-2',
                   'selected' => false,
                ],
                2 => (object) [
                   'label' => 'Option 3',
                   'value' => 'option-3',
                   'selected' => false,
                ],
              ],
            ],
        ];
    }

    /**
     * Try and hack in via another business.
     */
    public function test_can_not_show_logic_editor()
    {
        $user = User::factory()->create(['password' => '1234']);
        $this->actingAs($user);

        $business = $this->getBusiness('none of my  business');

        $livewire = Livewire::test(FormLogic::class, ['business' => $business]);
        $message = $livewire->lastResponse?->getSession()->get('flash_danger');
        $this->assertEquals('This action is unauthorized.', $message);
    }

    /**
     * Can view the logic editor.
     */
    public function test_can_show_logic_editor()
    {
        $user = User::factory()->create(['password' => '1234']);
        $business = $this->getBusiness('test business');
        $business->users()->attach($user);
        $this->actingAs($user);

        $form = Form::factory()->create([
            'name' => 'form name',
            'data' => json_encode($this->formData()),
            'action' => ['logic_question_name' => 'event-type'],
            'settings' => json_encode(['setting' => 'wankers']),
        ]);

        $questionTypes = ['select', 'radio-group', 'autocomplete'];
        $filtered = collect($this->formData())->whereIn('type', $questionTypes);

        $filteredQuestion = collect($this->formData())->whereIn('name', 'event-type')->first();
        $values = $filteredQuestion?->values;

        // show form and validate the calculated properties.
        $test = Livewire::test(FormLogic::class, ['business' => $business]);
        $test->call('show', $form->id);
        $test->assertSet('questions', $filtered);
        $test->assertSet('responseValues', collect($values));
    }

    // can_save
    public function test_can_save_logic()
    {
        $user = User::factory()->create(['password' => '1234']);
        $business = $this->getBusiness('test business');
        $business->users()->attach($user);
        $this->actingAs($user);

        $form = Form::factory()->create([
            'name' => 'form name',
            'data' => json_encode($this->formData()),
            'action' => ['logic_question_name' => 'event-type'],
            'settings' => json_encode(['setting' => 'wankers']),
        ]);

        // show form
        $lw = Livewire::test(FormLogic::class, ['business' => $business])->call('show', $form->id);

        // Adjust the properties
        $lw->set('form.required', true);
        $lw->set('form.action.type', 'mofo');
        $lw->set('form.action.logic_question_name', 'event-type');
        $lw->set('form.action.logic_response', 'voodoo');
        $lw->set('form.action.logic_form', 'form name');
        $lw->set('bind_logic.0', 'muphf');

        // Save
        $lw->call('save');

        $expected_action = (object) [
            'logic_question_name' => 'event-type',
            'type' => 'mofo',
            'logic_response' => 'voodoo',
            'logic_form' => 'form name',
            'logic' => [
                0 => 'muphf',
            ],
        ];

        $expectedJson = json_encode($expected_action);

        $updateForm = Form::first();
        $this->assertSame($expectedJson, (string) $updateForm->action);
        $this->assertEquals(true, $updateForm->required);
    }
}
