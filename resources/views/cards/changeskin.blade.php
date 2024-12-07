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
        const form = document.querySelector('form');

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

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);

            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                const data = await response.json();
                
                if(data.success) {
                    // Update the preview image with a new timestamp
                    const preview = document.getElementById('skinPreview');
                    const currentSrc = preview.src.split('?')[0];
                    preview.src = currentSrc + '?t=' + new Date().getTime();
                    
                    // Update any other skin previews on the page
                    document.querySelectorAll('img[data-skin-preview]').forEach(img => {
                        const src = img.src.split('?')[0];
                        img.src = src + '?t=' + new Date().getTime();
                    });

                    // Show success message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success';
                    alert.textContent = data.message;
                    form.insertBefore(alert, form.firstChild);
                    
                    setTimeout(() => alert.remove(), 3000);
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                // Show error message
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger';
                alert.textContent = error.message || 'An error occurred while uploading the skin';
                form.insertBefore(alert, form.firstChild);
                
                setTimeout(() => alert.remove(), 3000);
            }
        });
    </script>
@endpush

<form action="{{ route('skin-api.skin.update') }}" method="POST" enctype="multipart/form-data" data-turbolinks="false">
    @csrf

    <div class="mb-3">
        <label for="skin">{{ trans('skin-api::messages.skin') }}</label>       
        <img src="{{ route('skin-api.api.show', auth()->user()->name) }}?t={{ time() }}" alt="{{ trans('skin-api::messages.skin') }}" id="skinPreview" class="mt-3 img-fluid mx-auto d-block" data-skin-preview>
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
        {{ trans('messages.actions.upload') }}
    </button>
</form>
