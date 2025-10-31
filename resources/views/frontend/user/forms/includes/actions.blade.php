<a
    wire:click="edit({{ $model->id }})"
    href="#"
    class="btn btn-primary btn-sm">
    <i class="fas fa-pencil-alt"></i> Edit</a>

<a
    wire:click="delete({{ $model->id }})"
    href="#"
    class="btn btn-danger btn-sm">
    <i class="fas fa-trash"></i> Delete</a>

<button
    wire:click="$emit('FormLogic:show', {{ $model->id }})"
    type="button"
    class="btn btn-primary btn-sm"><i class="fas fa-magic"></i> Logic</button>

