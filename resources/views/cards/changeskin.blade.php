@push('styles')
    <style>
        #skinPreview {
            width: 250px;
            image-rendering: crisp-edges; /* Firefox */
            image-rendering: pixelated; /* Chrome and Safari */
        }
    </style>
@endpush

@push('footer-scripts')
    <script>
        const skinInput = document.getElementById('skin');

        skinInput.addEventListener('change', function () {
            if (!skinInput.files || !skinInput.files[0]) {
                return;
            }

            const file = skinInput.files[0];

            if (file.name !== undefined && file.name !== '') {
                document.getElementById('skinLabel').innerText = file.name;
            }

            const reader = new FileReader();

            reader.onload = function (e) {
                const preview = document.getElementById('skinPreview');
                preview.src = e.currentTarget.result;
                preview.classList.remove('d-none');
            };

            reader.readAsDataURL(skinInput.files[0]);
        });
    </script>
@endpush

<form action="{{ route('skin-api.update') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="mb-3">
        <label for="skin">{{ trans('skin-api::messages.skin') }}</label>       
        <img src="{{ route('skin-api.api.show', auth()->user()->name) }}" alt="{{ trans('skin-api::messages.skin') }}" id="skinPreview" class="mt-3 img-fluid mx-auto d-block">
    </div>  

    <div class="mb-3">
        <div class="custom-file">
            <input type="file" class="form-control @error('skin') is-invalid @enderror" id="skin" name="skin" accept=".png" required>
            <label class="form-label" for="skin" id="skinLabel">
                {{ trans('messages.actions.choose_file') }}
            </label>

            @error('skin')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>

    <button type="submit" class="btn btn-primary">
        {{ trans('messages.actions.save') }}
    </button>
</form>
