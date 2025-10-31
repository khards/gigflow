<?php
// https://elitebookingsystem.com/admin/auth/role

namespace App\Http\Livewire\Backend;

use App\Domains\Auth\Models\Role;
use App\Domains\Auth\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Class RolesTable.
 */
class RolesTable extends DataTableComponent
{

    /**
     * @var string
     */
    public $sortField = 'name';

    /**
     * @var array
     */
    protected $options = [
        'bootstrap.container' => false,
        'bootstrap.classes.table' => 'table table-striped',
    ];

    public function configure(): void
    {
        $this->setPrimaryKey('id');
    }

    /**
     * @return Builder
     */
    public function builder(): Builder
    {
        return Role::with('permissions:id,name,description')
            ->withCount('users');
    }

    /**
     * @return array
     */
    public function columns(): array
    {
        return [
            Column::make(__('Type'), 'type')
                ->sortable()
                ->label(function (Role $model) {
                    if ($model->type === User::TYPE_ADMIN) {
                        return __('Administrator');
                    }

                    if ($model->type === User::TYPE_USER) {
                        return __('User');
                    }

                    return 'N/A';
                }),

            Column::make(__('Name'), 'name')
                ->searchable()
                ->sortable(),

            Column::make(__('Permissions'), 'permissions_label')
                ->searchable(function ($builder, $term) {
                    return $builder->orWhereHas('permissions', function ($query) use ($term) {
                        return $query->where('name', 'like', '%'.$term.'%');
                    });
                })
                ->label(function (Role $model) {
                    return $model->permissions_label;
                }),

            Column::make(__('Number of Users'))
                ->sortable(function ($builder, $direction) {
                    return $builder->orderBy('users_count', $direction);
                })
                ->label(function ($row, $column) {
                    return $row->users_count;
                }),

            Column::make(__('Actions'))
                ->label(function (Role $model) {
                    return view('backend.auth.role.includes.actions', ['model' => $model]);
                }),
        ];
    }
}
