@extends('layouts.app')

@section('title', trans('skin-api::messages.title'))

@push('styles')
    <style>
        #skinPreview {
            width: 350px;
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

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('skin-api.skin.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <h2>{{ trans('skin-api::messages.change') }}</h2>

                <div class="mb-3">
                    <label for="skin">{{ trans('skin-api::messages.skin') }}</label>
                    <div class="custom-file">
                        <input type="file" class="form-control @error('skin') is-invalid @enderror" id="skin" name="skin" accept=".png" required>
                        <label class="form-label" for="skin" data-browse="{{ trans('messages.actions.browse') }}" id="skinLabel">
                            {{ trans('messages.actions.choose_file') }}
                        </label>

                        @error('skin')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                     <img src="{{ $skinUrl }}" alt="{{ trans('skin-api::messages.skin') }}" id="skinPreview" class="mt-3 img-fluid">
                </div>

                <button type="submit" class="btn btn-primary">
                    {{ trans('messages.actions.save') }}
                </button>
            </form>
        </div>
    </div>
@endsection
