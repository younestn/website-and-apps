<div class="modal fade" id="toggle-status-publish-modal" tabindex="-1" aria-labelledby="toggle-modal" aria-hidden="true" style="z-index: 999999">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header border-0 pb-0 d-flex justify-content-end">
                <button type="button" class="btn-close border-0 btn-circle bg-section2 shadow-none" data-bs-dismiss="modal" aria-label="Close"><i class="fi fi-rr-cross-small"></i></button>
            </div>
            <div class="modal-body px-4 px-sm-5 pt-0">
                <div class="d-flex flex-column align-items-center text-center gap-2 mb-2">
                    <div class="toggle-modal-img-box d-flex flex-column justify-content-center align-items-center mb-3 position-relative">
                        <img src="" class="status-icon"  alt="" width="30"/>
                        <img src="{{dynamicAsset(path: 'public/assets/back-end/img/modal/publish.png')}}" alt="" />
                    </div>
                    <h3 class="modal-title">{{ translate('are_you_sure_to_publish_the_blog_on_the_website') }}?</h3>
                    <div class="text-center">{{ translate('once_you_publish_the_blog_it_will_be_displayed_on_the_website_for_viewers') }}</div>
                </div>
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <button type="button" class="btn btn-primary text-capitalize publish-blog" data-bs-dismiss="modal">{{ translate('Yes') }}</button>
                    <button type="button" class="btn bg-light text-dark border-0" data-bs-dismiss="modal">{{translate('Not_Now')}}</button>
                </div>
            </div>
        </div>
    </div>
</div>
