<div class="modal fade" id="deleteCapeModal" tabindex="-1" role="dialog" aria-labelledby="capeDeleteLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="capeDeleteLabel">{{ trans('skin-api::messages.delete.title') }}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ trans('messages.actions.close') }}"></button>
            </div>
            <div class="modal-body">{{ trans('skin-api::messages.delete.cape') }}</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i> {{ trans('messages.actions.cancel') }}
                </button>
                <form action="{{ route('skin-api.cape.delete') }}" method="POST">
                    @method('DELETE')
                    @csrf

                    <button class="btn btn-danger" type="submit">
                        <i class="bi bi-trash"></i> {{ trans('messages.actions.delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>