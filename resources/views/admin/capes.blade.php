@extends('admin.layouts.admin')

@section('title', trans('skin-api::admin.capes.title'))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="mb-3">
                <h5>API Information</h5>
                <div class="card bg-dark text-white">
                    <div class="card-body">
                        <div class="mb-2">
                            <div>Using User ID:</div>
                            <code>GET {{ url('/api/cape-api/cape/{user_id}') }}</code>
                        </div>

                        <div class="mb-2">
                            <div>Using Username:</div>
                            <code>GET {{ url('/api/cape-api/cape/name/{username}') }}</code>
                        </div>

                        <div class="mt-3">
                            You can use either:
                            <ul>
                                <li>Replace {user_id} with the user's ID number</li>
                                <li>Replace {username} with the user's username</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('skin-api.admin.capes.update') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="widthInput">{{ trans('skin-api::admin.fields.width') }}</label>
                            <input type="number" min="1" class="form-control @error('width') is-invalid @enderror" id="widthInput" name="width" value="{{ old('width', $width) }}" required>

                            @error('width')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="heightInput">{{ trans('skin-api::admin.fields.height') }}</label>
                            <input type="number" min="1" class="form-control @error('height') is-invalid @enderror" id="heightInput" name="height" value="{{ old('height', $height) }}" required>

                            @error('height')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="showNavButton" name="show_nav_button" @checked($show_nav_button)>
                    <label class="custom-control-label" for="showNavButton">Show Cape button in navigation</label>
                </div>

                <div class="form-group custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="showInProfile" name="show_in_profile" @checked($show_in_profile)>
                    <label class="custom-control-label" for="showInProfile">Show Cape in Profile</label>
                </div>

                <div class="form-group">
                    <label for="navIconInput">Navigation Icon</label>
                    <input type="text" class="form-control @error('nav_icon') is-invalid @enderror" id="navIconInput" name="nav_icon" value="{{ old('nav_icon', $nav_icon) }}">
                    <small class="form-text text-muted">Enter a Bootstrap icon class (e.g., bi bi-person-circle). You can find icons at <a href="https://icons.getbootstrap.com/" target="_blank">Bootstrap Icons</a></small>
                    <small class="form-text text-muted">Leave empty to hide the navigation icon</small>

                    @error('nav_icon')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> {{ trans('messages.actions.save') }}
                </button>
            </form>
        </div>
    </div>
@endsection
