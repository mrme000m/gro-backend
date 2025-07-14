<div id="sidebarMain" class="d-none">
    <aside
        class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered  ">
        <div class="navbar-vertical-container text-capitalize">
            <div class="navbar-vertical-footer-offset">
                <div class="navbar-brand-wrapper justify-content-between">

                    @php
                        $logo = \App\Model\BusinessSetting::where(['key'=>'logo'])->first()->value;
                    @endphp
                    <a class="navbar-brand" href="{{route('admin.dashboard')}}" aria-label="Front">
                        <img class="w-100 side-logo"
                             src="{{ App\CentralLogics\Helpers::onErrorImage($logo, asset('storage/app/public/restaurant') . '/' . $logo, asset('assets/admin/img/160x160/img2.jpg'), 'restaurant/')}}"
                             alt="{{ translate('logo') }}">
                    </a>

                    <button type="button"
                            class="js-navbar-vertical-aside-toggle-invoker navbar-vertical-aside-toggle btn btn-icon btn-xs btn-ghost-dark">
                        <i class="tio-clear tio-lg"></i>
                    </button>
                    <div class="navbar-nav-wrap-content-left d-none d-xl-block">
                        <button type="button" class="js-navbar-vertical-aside-toggle-invoker close">
                            <i class="tio-first-page navbar-vertical-aside-toggle-short-align" data-toggle="tooltip"
                               data-placement="right" title="Collapse"></i>
                            <i class="tio-last-page navbar-vertical-aside-toggle-full-align"></i>
                        </button>
                    </div>
                </div>

                <div class="navbar-vertical-content" id="navbar-vertical-content">
                    <form class="sidebar--search-form">
                        <div class="search--form-group">
                            <button type="button" class="btn"><i class="tio-search"></i></button>
                            <input type="text" class="form-control form--control"
                                   placeholder="{{ translate('Search Menu...') }}" id="search-sidebar-menu">
                        </div>
                    </form>
                    <ul class="navbar-nav navbar-nav-lg nav-tabs">

                    {{-- Use Feature System Navigation --}}
                    @foreach(admin_navigation() as $item)
                        @if($item['enabled'])
                            @php
                                // Check if any submenu item is active
                                $hasActiveSubmenu = false;
                                if(isset($item['submenu'])) {
                                    foreach($item['submenu'] as $subitem) {
                                        if(request()->routeIs($subitem['route'])) {
                                            $hasActiveSubmenu = true;
                                            break;
                                        }
                                    }
                                }
                                $isParentActive = $item['active'] || $hasActiveSubmenu;
                            @endphp

                            {{-- Main Navigation Item --}}
                            <li class="navbar-vertical-aside-has-menu {{ $isParentActive ? 'show active' : '' }}">
                                @if(isset($item['submenu']) && count($item['submenu']) > 0)
                                    {{-- Has Submenu --}}
                                    <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                       href="javascript:" title="{{ $item['name'] }}">
                                        <i class="{{ $item['icon'] }} nav-icon"></i>
                                        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                            {{ $item['name'] }}
                                        </span>
                                    </a>
                                    <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                        style="display: {{ $isParentActive ? 'block' : 'none' }}">
                                        @foreach($item['submenu'] as $subitem)
                                            <li class="nav-item {{ request()->routeIs($subitem['route']) ? 'active' : '' }}">
                                                <a class="nav-link"
                                                   href="{{ isset($subitem['params']) ? route($subitem['route'], $subitem['params']) : route($subitem['route']) }}"
                                                   title="{{ $subitem['name'] }}">
                                                    <span class="tio-circle nav-indicator-icon"></span>
                                                    <span class="text-truncate">{{ $subitem['name'] }}</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    {{-- No Submenu --}}
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                       href="{{ isset($item['route_params']) ? route($item['route'], $item['route_params']) : route($item['route']) }}"
                                       title="{{ $item['name'] }}">
                                        <i class="{{ $item['icon'] }} nav-icon"></i>
                                        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                            {{ $item['name'] }}
                                        </span>
                                    </a>
                                @endif
                            </li>
                        @endif
                    @endforeach

                    </ul>
                </div>
            </div>
        </div>
    </aside>
</div>

{{-- JavaScript is handled by the main layout's existing sidebar scripts --}}
