@php
use App\Enums\EmailTemplateKey;
@endphp

<div class="position-relative nav--tab-wrapper mb-4">
    <ul class="nav nav-pills nav--tab" id="pills-tab" role="tablist">
        @foreach(EmailTemplateKey::ADMIN_EMAIL_LIST as $key=>$value)
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/system-setup/email-templates/admin/'.$value) ?'active':'' }}"
                    href="{{ route('admin.system-setup.email-templates.view',['admin',$value]) }}">
                    {{ translate(str_replace('-','_',$value)) }}
                </a>
            </li>
        @endforeach
    </ul>
    <div class="nav--tab__prev">
        <button class="btn btn-circle border-0 bg-white text-primary">
            <i class="fi fi-sr-angle-left"></i>
        </button>
    </div>
    <div class="nav--tab__next">
        <button class="btn btn-circle border-0 bg-white text-primary">
            <i class="fi fi-sr-angle-right"></i>
        </button>
    </div>
</div>
