@if($confirming === $model->id)
    <button
        wire:click="kill({{ $model->id }})"
        class="btn btn-danger btn-sm">Sure?</button>

    <button
        wire:click="confirmDelete(0)"
        class="btn btn-success btn-sm">Cancel</button>
@else
    <a
        wire:click="edit({{ $model->id }})"
        href="#"
        class="btn btn-primary btn-sm">
        <i class="fas fa-pencil-alt"></i> Edit</a>

    <button
        wire:click="confirmDelete({{ $model->id }})"
        class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Delete</button>
@endif
