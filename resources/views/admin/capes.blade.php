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
                            <code>GET {{ url('/api/skin-api/capes/{user_id}') }}</code>
                        </div>

                        <div class="mb-2">
                            <div>Using Username:</div>
                            <code>GET {{ url('/api/skin-api/capes/name/{username}') }}</code>
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

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="widthInput">{{ trans('skin-api::admin.fields.width') }}</label>
                        <input type="text" class="form-control @error('width') is-invalid @enderror" id="widthInput" name="width" value="{{ old('width', $width) }}">

                        @error('width')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="form-group col-md-6">
                        <label for="heightInput">{{ trans('skin-api::admin.fields.height') }}</label>
                        <input type="text" class="form-control @error('height') is-invalid @enderror" id="heightInput" name="height" value="{{ old('height', $height) }}">

                        @error('height')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="showNavButton" name="show_nav_button" value="1" {{ old('show_nav_button', $show_nav_button) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="showNavButton">Show Cape button in navigation</label>
                    </div>

                    @error('show_nav_button')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="showInProfile" name="show_in_profile" value="1" {{ old('show_in_profile', $show_in_profile) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="showInProfile">Show Cape in Profile</label>
                    </div>

                    @error('show_in_profile')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="navIconInput">Navigation Icon</label>
                    <input type="text" class="form-control @error('nav_icon') is-invalid @enderror" id="navIconInput" name="nav_icon" value="{{ old('nav_icon', $nav_icon) }}">
                    <small class="form-text text-muted">Enter a Bootstrap icon class (e.g., bi bi-person-circle). You can find icons at <a href="https://icons.getbootstrap.com/" target="_blank">Bootstrap Icons</a><br>Leave empty to hide the navigation icon</small>

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
