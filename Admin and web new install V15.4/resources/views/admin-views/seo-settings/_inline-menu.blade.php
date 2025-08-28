<div class="position-relative nav--tab-wrapper mb-4">
    <ul class="nav nav-pills nav--tab" id="pills-tab" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ Request::is('admin/seo-settings/web-master-tool') ? 'active' : '' }}" href="{{ route('admin.seo-settings.web-master-tool') }}">
                {{ translate('Webmaster_Tools') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Request::is('admin/seo-settings/robot-txt') ? 'active' : '' }}" href="{{ route('admin.seo-settings.robot-txt') }}">
                {{ translate('Robots.txt') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Request::is('admin/seo-settings/sitemap') ? 'active' : '' }}" href="{{ route('admin.seo-settings.sitemap') }}">
                {{ translate('Sitemap') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Request::is('admin/seo-settings/robots-meta-content*') ? 'active' : '' }}" href="{{ route('admin.seo-settings.robots-meta-content.index') }}">
                {{ translate('Robots_Meta_Content') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Request::is('admin/seo-settings/error-logs/index') ? 'active' : '' }}"
               href="{{ route('admin.seo-settings.error-logs.index') }}">
                {{ translate('404_Logs') }}
            </a>
        </li>
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
