@push('styles')
    <style>
        #skinPreview,
        #capePreview {
            width: 192px;
            image-rendering: crisp-edges; /* Firefox */
            image-rendering: pixelated; /* Chrome/Safari */
        }
    </style>
@endpush

@push('footer-scripts')
    <script>
        document.querySelectorAll('[data-skin-preview]').forEach(function (input) {
            input.addEventListener('change', function () {
                if (!input.files || !input.files[0]) {
                    return;
                }

                const reader = new FileReader();

                reader.onload = function (e) {
                    const preview = document.getElementById(input.dataset.skinPreview);
                    preview.src = e.currentTarget.result;
                    preview.classList.remove('d-none');
                };

                reader.readAsDataURL(input.files[0]);
            });
        })
    </script>
@endpush

<form action="{{ route('skin-api.update') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="mb-3">
        <label for="skin">{{ trans('skin-api::messages.skin') }}</label>
        <input type="file" class="form-control @error('skin') is-invalid @enderror" id="skinInput" name="skin" accept="image/png" data-skin-preview="skinPreview">

        @error('skin')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror

        <img src="{{ $skinUrl }}" alt="{{ trans('skin-api::messages.skin') }}" id="skinPreview" class="my-3 img-fluid">

        @if($hasSkin)
            <button type="button" class="btn btn-danger ms-md-3" data-bs-toggle="modal" data-bs-target="#deleteSkinModal">
                <i class="bi bi-trash"></i> {{ trans('messages.actions.delete') }}
            </button>
        @endif
    </div>

    <button type="submit" class="btn btn-primary">
        <i class="bi bi-save"></i> {{ trans('messages.actions.save') }}
    </button>
</form>

@if($hasSkin)
    @include('skin-api::profile._skin_modal')
@endif
