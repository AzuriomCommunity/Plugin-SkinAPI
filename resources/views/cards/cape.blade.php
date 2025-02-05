<form action="{{ route('skin-api.update') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="mb-3">
        <label for="cape">{{ trans('skin-api::messages.cape') }}</label>
        <input type="file" class="form-control @error('cape') is-invalid @enderror" id="cape" name="cape" accept="image/png" data-skin-preview="capePreview">

        @error('cape')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror

        <img src="{{ $capeUrl ?? '#'}}" alt="{{ trans('skin-api::messages.cape') }}" id="capePreview" class="my-3 img-fluid @if(!$capeUrl) d-none @endif">

        @if($hasCape)
            <button type="button" class="btn btn-danger ms-md-3" data-bs-toggle="modal" data-bs-target="#deleteCapeModal">
                <i class="bi bi-trash"></i> {{ trans('messages.actions.delete') }}
            </button>
        @endif
    </div>

    <button type="submit" class="btn btn-primary">
        <i class="bi bi-save"></i> {{ trans('messages.actions.save') }}
    </button>
</form>

@if($hasCape)
    @include('skin-api::profile._cape_modal')
@endif