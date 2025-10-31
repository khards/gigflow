<?php
// https://elitebookingsystem.com/admin/auth/user

namespace App\Http\Livewire\Backend;

use App\Domains\Auth\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class UsersTable extends DataTableComponent
{

    /**
     * @var string
     */
    public $sortField = 'name';

    /**
     * @var string
     */
    public $status;

    public function configure(): void
    {
        $this->setPrimaryKey('id');
    }

    /**
     * @var array
     */
    protected $options = [
        'bootstrap.container' => false,
        'bootstrap.classes.table' => 'table table-striped',
    ];

    /**
     * @param  string  $status
     */
    public function mount($status = 'active'): void
    {
        $this->status = $status;
    }

    /**
     * @return Builder
     */
    public function builder(): Builder
    {
        $query = User::with('roles', 'twoFactorAuth', 'permissions')
            ->withCount('twoFactorAuth');

        if ($this->status === 'deleted') {
            return $query->onlyTrashed();
        }

        if ($this->status === 'deactivated') {
            return $query->onlyDeactivated();
        }

        return $query->onlyActive();
    }

    /**
     * @return array
     */
    public function columns(): array
    {
        return [
            Column::make(__('Id'), 'id')
                ->sortable(),

            Column::make(__('Type'), 'type')
                ->sortable()
                ->format(function ($value, User $model, $column) {
                    return view('backend.auth.user.includes.type', ['user' => $model]);
                }),

            Column::make(__('Name'), 'name')
                ->searchable()
                ->sortable(),

            Column::make(__('E-mail'), 'email')
                ->searchable()
                ->sortable()
                ->format(function ($value, User $model, $column) {
                    return $model->email;
                }),

            Column::make(__('Verified'), 'email_verified_at')
                ->sortable()
                ->format(function ($value, User $model, $column) {
                    return view('backend.auth.user.includes.verified', ['user' => $model]);
                }),

            Column::make(__('2FA'))
                ->sortable(function ($builder, $direction) {
                    return $builder->orderBy('two_factor_auth_count', $direction);
                })
                ->label(function ($row, $column) {
                    return view('backend.auth.user.includes.2fa', ['user' => $row]);
                }),

            Column::make(__('Roles'))
                ->sortable()
                ->label(function ($row) {
                    if($role = $row->roles->first()) {
                        return $role->name;
                    }
                })
                ->searchable(function ($builder, $term) {
                    return $builder->orWhereHas('roles', function ($query) use ($term) {
                        return $query->where('name', 'like', '%'.$term.'%');
                    });
                }),

            Column::make(__('Additional Permissions'))
                ->searchable(function ($builder, $term) {
                    return $builder->orWhereHas('permissions', function ($query) use ($term) {
                        return $query->where('name', 'like', '%'.$term.'%');
                    });
                })
                ->label(function (User $model) {
                    return $model->permissions_label;
                }),

            Column::make(__('Actions'))
                ->label(function ($value) {
                    return view('backend.auth.user.includes.actions', ['user' => $value]);
                }),

        ];
    }

    /**
     * This was used by default and for version 0.3 in 2020. Keep for reference during upgrade to v2.0
     *
     * @return array
     */
    public function v1_columns(): array
    {
        return [
            Column::make(__('Type'), 'type')
                ->sortable()
                ->format(function (User $model) {
                    return view('backend.auth.user.includes.type', ['user' => $model]);
                }),
            Column::make(__('Name'), 'name')
                ->searchable()
                ->sortable(),
            Column::make(__('E-mail'), 'email')
                ->searchable()
                ->sortable()
                ->format(function (User $model) {
                    return $this->mailto($model->email);
                }),
            Column::make(__('Verified'), 'email_verified_at')
                ->sortable()
                ->format(function (User $model) {
                    return view('backend.auth.user.includes.verified', ['user' => $model]);
                }),
            Column::make(__('2FA'))
                ->sortable(function ($builder, $direction) {
                    return $builder->orderBy('two_factor_auth_count', $direction);
                })
                ->format(function (User $model) {
                    return view('backend.auth.user.includes.2fa', ['user' => $model]);
                }),
            Column::make(__('Roles'), 'roles_label')
                ->searchable(function ($builder, $term) {
                    return $builder->orWhereHas('roles', function ($query) use ($term) {
                        return $query->where('name', 'like', '%'.$term.'%');
                    });
                })
                ->format(function (User $model) {
                    return $this->html($model->roles_label);
                }),
            Column::make(__('Additional Permissions'), 'permissions_label')
                ->searchable(function ($builder, $term) {
                    return $builder->orWhereHas('permissions', function ($query) use ($term) {
                        return $query->where('name', 'like', '%'.$term.'%');
                    });
                })
                ->format(function (User $model) {
                    return $this->html($model->permissions_label);
                }),
            Column::make(__('Actions'))
                ->format(function (User $model) {
                    return view('backend.auth.user.includes.actions', ['user' => $model]);
                }),
        ];
    }
}
