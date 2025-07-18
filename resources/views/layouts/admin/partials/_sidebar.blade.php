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
                    @if(feature_enabled('core.dashboard'))
                            <li class="navbar-vertical-aside-has-menu {{Request::is('admin')?'show active':''}}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link"
                                   href="{{route('admin.dashboard')}}" title="{{translate('dashboard')}}">
                                    <i class="tio-home-vs-1-outlined nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{translate('dashboard')}}
                                </span>
                                </a>
                            </li>
                    @endif

                    @if(feature_enabled('orders.enabled'))
                            <li class="navbar-vertical-aside-has-menu {{Request::is('admin/pos*')?'active':''}}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                   href="javascript:" title="{{translate('POS')}}">
                                    <i class="tio-shopping nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{translate('POS')}}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{Request::is('admin/pos*')?'block':'none'}}">
                                    <li class="nav-item {{Request::is('admin/pos')?'active':''}}">
                                        <a class="nav-link " href="{{route('admin.pos.index')}}"
                                           title="{{translate('New Sale')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="text-truncate">{{translate('New Sale')}}</span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{Request::is('admin/pos/orders')?'active':''}}">
                                        <a class="nav-link " href="{{route('admin.pos.orders')}}"
                                           title="{{translate('orders')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate sidebar--badge-container">
                                                <span>{{translate('orders')}}</span>
                                                <span class="badge badge-soft-info badge-pill ml-1">
                                                    {{\App\Model\Order::Pos()->count()}}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        @if(feature_enabled('orders.enabled'))
                            <li class="nav-item">
                                <small
                                    class="nav-subtitle">{{translate('order_management')}}</small>
                                <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                            </li>

                            <li class="navbar-vertical-aside-has-menu {{Request::is('admin/verify-offline-payment*') ?'show active':''}}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link"
                                   href="{{route('admin.verify-offline-payment', ['pending'])}}" title="{{translate('Verify_Offline_Payment')}}">
                                    <i class="tio-shopping-basket nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{translate('Verify_Offline_Payment')}}
                                </span>
                                </a>
                            </li>

                            <li class="navbar-vertical-aside-has-menu {{Request::is('admin/orders*')?'active':''}}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                   href="javascript:" title="{{translate('orders')}}">
                                    <i class="tio-shopping-cart nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{translate('orders')}}
                                    </span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{Request::is('admin/order*')?'block':'none'}}">
                                    <li class="nav-item {{Request::is('admin/orders/list/all')?'active':''}}">
                                        <a class="nav-link" href="{{route('admin.orders.list',['all'])}}"
                                           title="{{translate('all_orders')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate sidebar--badge-container">
                                                <span>{{translate('all')}}</span>
                                                <span class="badge badge-info badge-pill ml-1">
                                                    {{\App\Model\Order::notPos()->count()}}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{Request::is('admin/orders/list/pending')?'active':''}}">
                                        <a class="nav-link " href="{{route('admin.orders.list',['pending'])}}"
                                           title="{{translate('pending_orders')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate sidebar--badge-container">
                                                <span>{{translate('pending')}}</span>
                                                <span class="badge badge-soft-info badge-pill ml-1">
                                                    {{\App\Model\Order::where(['order_status'=>'pending'])->count()}}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{Request::is('admin/orders/list/confirmed')?'active':''}}">
                                        <a class="nav-link " href="{{route('admin.orders.list',['confirmed'])}}"
                                           title="{{translate('confirmed_orders')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate sidebar--badge-container">
                                                <span>{{translate('confirmed')}}</span>
                                                    <span class="badge badge-soft-success badge-pill ml-1">
                                                    {{\App\Model\Order::where(['order_status'=>'confirmed'])->count()}}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{Request::is('admin/orders/list/processing')?'active':''}}">
                                        <a class="nav-link " href="{{route('admin.orders.list',['processing'])}}"
                                           title="{{translate('processing_orders')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate  sidebar--badge-container">
                                                <span>{{translate('packaging')}}</span>
                                                    <span class="badge badge-soft-warning badge-pill ml-1">
                                                    {{\App\Model\Order::where(['order_status'=>'processing'])->count()}}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{Request::is('admin/orders/list/out_for_delivery')?'active':''}}">
                                        <a class="nav-link " href="{{route('admin.orders.list',['out_for_delivery'])}}"
                                           title="{{translate('out_for_delivery_orders')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate  sidebar--badge-container">
                                                <span>{{translate('out_for_delivery')}}</span>
                                                    <span class="badge badge-soft-warning badge-pill ml-1">
                                                    {{\App\Model\Order::where(['order_status'=>'out_for_delivery'])->count()}}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{Request::is('admin/orders/list/delivered')?'active':''}}">
                                        <a class="nav-link " href="{{route('admin.orders.list',['delivered'])}}"
                                           title="{{translate('delivered_orders')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate  sidebar--badge-container">
                                                <span>{{translate('delivered')}}</span>
                                                    <span class="badge badge-soft-success badge-pill ml-1">
                                                    {{\App\Model\Order::notPos()->where(['order_status'=>'delivered'])->count()}}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{Request::is('admin/orders/list/returned')?'active':''}}">
                                        <a class="nav-link " href="{{route('admin.orders.list',['returned'])}}"
                                           title="{{translate('returned_orders')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate  sidebar--badge-container">
                                                <span>{{translate('returned')}}</span>
                                                    <span class="badge badge-soft-danger badge-pill ml-1">
                                                    {{\App\Model\Order::where(['order_status'=>'returned'])->count()}}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{Request::is('admin/orders/list/failed')?'active':''}}">
                                        <a class="nav-link " href="{{route('admin.orders.list',['failed'])}}"
                                           title="{{translate('failed_orders')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate  sidebar--badge-container">
                                                <span>{{translate('failed')}}</span>
                                                <span class="badge badge-soft-danger badge-pill ml-1">
                                                    {{\App\Model\Order::where(['order_status'=>'failed'])->count()}}
                                                </span>
                                            </span>
                                        </a>
                                    </li>

                                    <li class="nav-item {{Request::is('admin/orders/list/canceled')?'active':''}}">
                                        <a class="nav-link " href="{{route('admin.orders.list',['canceled'])}}"
                                           title="{{translate('canceled_orders')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate  sidebar--badge-container">
                                                <span>{{translate('canceled')}}</span>
                                                    <span class="badge badge-soft-light badge-pill ml-1">
                                                    {{\App\Model\Order::where(['order_status'=>'canceled'])->count()}}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        @if(feature_enabled('products.enabled'))
                            <li class="nav-item">
                                <small
                                    class="nav-subtitle">{{translate('product_management')}} </small>
                                <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                            </li>

                            <li class="navbar-vertical-aside-has-menu {{Request::is('admin/category*')?'active':''}}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                   href="javascript:" title="{{translate('category setup')}}"
                                >
                                    <i class="tio-category nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{translate('category setup')}}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{Request::is('admin/category*')?'block':'none'}}">
                                    <li class="nav-item {{Request::is('admin/category/add')?'active':''}}">
                                        <a class="nav-link " href="{{route('admin.category.add')}}"
                                           title="{{translate('categories')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{translate('categories')}}</span>
                                        </a>
                                    </li>

                                    <li class="nav-item {{Request::is('admin/category/add-sub-category')?'active':''}}">
                                        <a class="nav-link " href="{{route('admin.category.add-sub-category')}}"
                                           title="{{translate('sub_categories')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{translate('sub_categories')}}</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li class="navbar-vertical-aside-has-menu {{Request::is('admin/product*') || Request::is('admin/attribute*')?'active':''}}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                   href="javascript:"
                                   title="{{translate('product setup')}}"
                                >
                                    <i class="tio-premium-outlined nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{translate('product setup')}}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{Request::is('admin/product*') || Request::is('admin/attribute*') ? 'block' : 'none'}}">

                                    <li class="nav-item {{Request::is('admin/attribute*')?'active':''}}">
                                        <a class="nav-link"
                                           href="{{route('admin.attribute.add-new')}}"
                                           title="{{translate('product attribute')}}"
                                        >
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{translate('product attribute')}}</span>
                                        </a>
                                    </li>

                                    <li class="nav-item {{Request::is('admin/product/list*')?'active':''}} {{Request::is('admin/product/add-new')?'active':''}}">
                                        <a class="nav-link " href="{{route('admin.product.list')}}"
                                           title="{{translate('list')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{translate('product list')}}</span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{Request::is('admin/product/bulk-import')?'active':''}}">
                                        <a class="nav-link " href="{{route('admin.product.bulk-import')}}"
                                           title="{{translate('bulk_import')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{translate('bulk_import')}}</span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{Request::is('admin/product/bulk-export-index')?'active':''}}">
                                        <a class="nav-link " href="{{route('admin.product.bulk-export-index')}}"
                                           title="{{translate('bulk_export')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{translate('bulk_export')}}</span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{Request::is('admin/product/limited-stock')?'active':''}}">
                                        <a class="nav-link" href="{{route('admin.product.limited-stock')}}"
                                           title="{{translate('Limited Stocks')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{translate('Limited Stocks')}}</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        @if(feature_enabled('marketing.enabled'))
                            <li class="nav-item">
                                <small
                                    class="nav-subtitle">{{translate('promotion_management')}} </small>
                                <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                            </li>

                            <li class="navbar-vertical-aside-has-menu {{Request::is('admin/banner*')?'active':''}}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link"
                                   href="{{route('admin.banner.add-new')}}"
                                   title="{{translate('banner')}}"
                                >
                                    <i class="tio-image nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{translate('banner')}}</span>
                                </a>
                            </li>

                            <li class="navbar-vertical-aside-has-menu {{Request::is('admin/coupon*')?'active':''}}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link"
                                   href="{{route('admin.coupon.add-new')}}"
                                   title="{{translate('coupons')}}"
                                >
                                    <i class="tio-gift nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{translate('coupons')}}</span>
                                </a>
                            </li>

                            <li class="navbar-vertical-aside-has-menu {{Request::is('admin/notification*')?'active':''}}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link"
                                   href="{{route('admin.notification.add-new')}}"
                                   title="{{translate('send notifications')}}"
                                >
                                    <i class="tio-notifications nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{translate('send')}} {{translate('notifications')}}
                                    </span>
                                </a>
                            </li>
                            <li class="navbar-vertical-aside-has-menu {{Request::is('admin/offer*')?'active':''}}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link"
                                   href="{{route('admin.offer.flash.index')}}"
                                   title="{{translate('flash_sale')}}"
                                >
                                    <i class="tio-alarm-alert nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{translate('flash_sale')}}
                                    </span>
                                </a>
                            </li>


                            <li class="navbar-vertical-aside-has-menu {{Request::is('admin/discount*')?'active':''}}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{route('admin.discount.add-new')}}"
                                   title="{{translate('category_discount')}}">
                                    <i class="tio-layers-outlined nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{translate('category_discount')}}</span>
                                </a>
                            </li>
                        @endif

                        @if(feature_enabled('customers.customer_support'))
                            <li class="nav-item">
                                <small class="nav-subtitle"
                                       title="Layouts">{{translate('Help & Support Section')}}</small>
                                <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                            </li>

                            <li class="navbar-vertical-aside-has-menu {{Request::is('admin/message*')?'active':''}}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link"
                                   href="{{route('admin.message.list')}}"
                                   title="{{translate('messages')}}"
                                >
                                    <i class="tio-messages nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{translate('messages')}}
                                    </span>
                                </a>
                            </li>
                        @endif

                        @if(feature_enabled('analytics.enabled'))
                            <li class="nav-item">
                                <small class="nav-subtitle"
                                       title="Documentation">{{translate('report_and_analytics')}}</small>
                                <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                            </li>

                            <li class="navbar-vertical-aside-has-menu {{Request::is('admin/report/sale-report')?'active':''}}">
                                <a class="nav-link " href="{{route('admin.report.sale-report')}}"
                                   title="{{translate('sale')}} {{translate('report')}}">
                                    <span class="tio-chart-bar-1 nav-icon"></span>
                                    <span class="text-truncate">{{translate('Sales Report')}}</span>
                                </a>
                            </li>
                            <li class="navbar-vertical-aside-has-menu {{Request::is('admin/report/order')?'active':''}}">
                                <a class="nav-link " href="{{route('admin.report.order')}}"
                                   title="{{translate('order')}} {{translate('report')}}">
                                    <span class="tio-chart-bar-2 nav-icon"></span>
                                    <span class="text-truncate">{{translate('Order Report')}}</span>
                                </a>
                            </li>
                            <li class="navbar-vertical-aside-has-menu {{Request::is('admin/report/earning')?'active':''}}">
                                <a class="nav-link " href="{{route('admin.report.earning')}}"
                                   title="{{translate('earning')}} {{translate('report')}}"
                                >
                                    <span class="tio-chart-pie-1 nav-icon"></span>
                                    <span
                                        class="text-truncate">{{translate('earning')}} {{translate('report')}}</span>
                                </a>
                            </li>

                            <li class="navbar-vertical-aside-has-menu {{Request::is('admin/report/expense')?'active':''}}">
                                <a class="nav-link " href="{{route('admin.report.expense')}}"
                                   title="{{translate('expense')}} {{translate('report')}}"
                                >
                                    <span class="tio-chart-bar-4 nav-icon"></span>
                                    <span
                                        class="text-truncate">{{translate('expense')}} {{translate('report')}}</span>
                                </a>
                            </li>

                            <li class="navbar-vertical-aside-has-menu {{Request::is('admin/analytics*')?'active':''}}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                   href="javascript:" title="{{translate('Analytics')}}">
                                    <i class="tio-chart-donut-2 nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{translate('Analytics')}}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{Request::is('admin/analytics*')?'block':'none'}}">
                                    <li class="nav-item {{Request::is('admin/analytics/keyword-search')?'active':''}}">
                                        <a class="nav-link " href="{{route('admin.analytics.keyword-search', ['date_range'=>'today', 'date_range_2'=>'today'])}}"
                                           title="{{translate('keyword-search')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{translate('Keyword Search')}}</span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{Request::is('admin/analytics/customer-search')?'active':''}}">
                                        <a class="nav-link " href="{{route('admin.analytics.customer-search', ['date_range'=>'today', 'date_range_2'=>'today'])}}"
                                           title="{{translate('customer-search')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{translate('customer search')}}</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif


                        @if(feature_enabled('customers.enabled') || feature_enabled('system.user_management'))
                            <li class="nav-item">
                                <small class="nav-subtitle"
                                       title="Documentation">{{translate('user management')}}</small>
                                <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                            </li>

                            <li class="navbar-vertical-aside-has-menu {{Request::is('admin/customer/list') || Request::is('admin/customer/view*')?'active':''}}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link"
                                   href="{{route('admin.customer.list')}}"
                                   title="{{translate('customer')}} {{translate('list')}}"
                                >
                                    <i class="tio-poi-user nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{translate('customer')}} {{translate('list')}}
                                    </span>
                                </a>
                            </li>

                            <li class="navbar-vertical-aside-has-menu {{Request::is('admin/customer/wallet/*')?'active':''}}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                   href="javascript:" title="{{translate('Customer Wallet')}}">
                                    <i class="tio-wallet-outlined nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                            {{translate('Customer Wallet')}}
                                        </span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{Request::is('admin/customer/wallet*')?'block':'none'}}">

                                    <li class="nav-item {{Request::is('admin/customer/wallet/add-fund')?'active':''}}">
                                        <a class="nav-link"
                                           href="{{route('admin.customer.wallet.add-fund')}}"
                                           title="{{translate('add_fund')}}"
                                        >
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{translate('add_fund')}}
                                            </span>
                                        </a>
                                    </li>

                                    <li class="nav-item {{Request::is('admin/customer/wallet/report')?'active':''}}">
                                        <a class="nav-link"
                                           href="{{route('admin.customer.wallet.report')}}"
                                           title="{{translate('report')}}"
                                        >
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{translate('report')}}
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{Request::is('admin/customer/wallet/bonus*')?'active':''}}">
                                        <a class="nav-link"
                                           href="{{route('admin.customer.wallet.bonus.index')}}"
                                           title="{{translate('wallet_bonus_setup')}}"
                                        >
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{translate('wallet_bonus_setup')}}
                                            </span>
                                        </a>
                                    </li>

                                </ul>
                            </li>

                            <li class="navbar-vertical-aside-has-menu {{Request::is('admin/customer/loyalty-point*')?'active':''}}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                   href="javascript:" title="{{translate('Customer Loyalty Point')}}">
                                    <i class="tio-medal nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                            {{translate('Customer Loyalty Point')}}
                                        </span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{Request::is('admin/customer/loyalty-point*')?'block':'none'}}">

                                    <li class="nav-item {{Request::is('admin/customer/loyalty-point/report')?'active':''}}">
                                        <a class="nav-link"
                                           href="{{route('admin.customer.loyalty-point.report')}}"
                                           title="{{translate('report')}}"
                                        >
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{translate('report')}}
                                            </span>
                                        </a>
                                    </li>

                                </ul>
                            </li>

                            <li class="navbar-vertical-aside-has-menu {{Request::is('admin/reviews*')?'active':''}}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link"
                                   href="{{route('admin.reviews.list')}}"
                                   title="{{translate('product')}} {{translate('reviews')}}"
                                >
                                    <i class="tio-star nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{translate('product')}} {{translate('reviews')}}
                                    </span>
                                </a>
                            </li>
                            <li class="navbar-vertical-aside-has-menu {{Request::is('admin/customer/subscribed-email*')?'active':''}}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link"
                                   href="{{route('admin.customer.subscribed_emails')}}"
                                   title="{{translate('Subscribed Emails')}}">
                                    <i class="tio-email-outlined nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{translate('Subscribed Emails')}}
                                    </span>
                                </a>
                            </li>

                            <li class="navbar-vertical-aside-has-menu {{Request::is('admin/delivery-man/*')?'active':''}}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                   href="javascript:" title="{{translate('deliveryman')}}">
                                    <i class="tio-user nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                            {{translate('deliveryman')}}
                                        </span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{Request::is('admin/delivery-man*')?'block':'none'}}">

                                    <li class="nav-item {{Request::is('admin/delivery-man/list')?'active':''}}">
                                        <a class="nav-link"
                                           href="{{route('admin.delivery-man.list')}}"
                                           title="{{translate('list')}}"
                                        >
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{translate('Delivery Man List')}}
                                            </span>
                                        </a>
                                    </li>

                                    <li class="nav-item {{Request::is('admin/delivery-man/add')?'active':''}}">
                                        <a class="nav-link"
                                           href="{{route('admin.delivery-man.add')}}"
                                           title="{{translate('register')}}"
                                        >
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{translate('Add New Delivery Man')}}
                                            </span>
                                        </a>
                                    </li>

                                    <li class="nav-item {{Request::is('admin/delivery-man/pending/list') || Request::is('admin/delivery-man/denied/list')?'active':''}}">
                                        <a class="nav-link"
                                           href="{{route('admin.delivery-man.pending')}}"
                                           title="{{translate('joining request')}}"
                                        >
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{translate('New Joining Request')}}
                                            </span>
                                        </a>
                                    </li>

                                    <li class="nav-item {{Request::is('admin/delivery-man/reviews/list')?'active':''}}">
                                        <a class="nav-link"
                                           href="{{route('admin.delivery-man.reviews.list')}}"
                                           title="{{translate('reviews')}}"
                                        >
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{translate('Delivery Man Reviews')}}
                                            </span>
                                        </a>
                                    </li>

                                </ul>
                            </li>
                            @if(auth('admin')->user()->admin_role_id == 1)
                                <li class="navbar-vertical-aside-has-menu {{Request::is('admin/employee*')?'active':''}}  {{Request::is('admin/custom-role*')?'active':''}}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                   href="javascript:" title="{{translate('employees')}}">
                                    <i class="tio-incognito nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                            {{translate('employees')}}
                                        </span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{Request::is('admin/employee*') || Request::is('admin/custom-role*') || Request::is('admin/employee/update/*') ?'block':'none'}}">

                                    <li class="nav-item {{Request::is('admin/custom-role*')?'active':''}}">
                                        <a class="nav-link"
                                           href="{{route('admin.custom-role.create')}}"
                                           title="{{translate('Employee Role Setup')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                    {{translate('Employee Role Setup')}}</span>
                                        </a>
                                    </li>

                                    <li class="nav-item {{Request::is('admin/employee/list')?'active':''}}">
                                        <a class="nav-link" href="{{route('admin.employee.list')}}"
                                           title="{{translate('List')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{translate('Employee List')}}</span>
                                        </a>
                                    </li>

                                    <li class="nav-item {{Request::is('admin/employee/add-new')?'active':''}}">
                                        <a class="nav-link " href="{{route('admin.employee.add-new')}}"
                                           title="{{translate('add_new')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{translate('Add New Employee')}}</span>
                                        </a>
                                    </li>

                                </ul>
                            </li>
                            @endif
                        @endif

                        @if(feature_enabled('system.enabled'))
                            <li class="nav-item">
                                <small class="nav-subtitle"
                                       title="Layouts">{{translate('system setting')}}</small>
                                <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                            </li>

                            <li class="navbar-vertical-aside-has-menu {{Request::is('admin/business-settings/store*')?'active':''}}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link"
                                   href="{{route('admin.business-settings.store.ecom-setup')}}"
                                   title="{{translate('Business Setup')}}">
                                    <i class="tio-settings nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{translate('Business Setup')}}
                                    </span>
                                </a>
                            </li>

                            <li class="navbar-vertical-aside-has-menu {{Request::is('admin/branch*')?'active':''}}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                   href="javascript:"
                                   title="{{translate('Branch Setup')}}"
                                >
                                    <i class="tio-shop nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{translate('Branch Setup')}}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{Request::is('admin/branch*')?'block':'none'}}">
                                    <li class="nav-item {{Request::is('admin/branch/add-new')?'active':''}}">
                                        <a class="nav-link " href="{{route('admin.branch.add-new')}}"
                                           title="{{translate('add New')}}"
                                        >
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="text-truncate">{{translate('Add New')}}</span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{Request::is('admin/branch/list')?'active':''}}">
                                        <a class="nav-link " href="{{route('admin.branch.list')}}"
                                           title="{{translate('list')}}"
                                        >
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="text-truncate">{{translate('list')}}</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li class="navbar-vertical-aside-has-menu {{Request::is('admin/business-settings/web-app/payment-method*')
                                        || Request::is('admin/business-settings/web-app/third-party/fcm*')
                                       || Request::is('admin/business-settings/web-app/third-party*')
                                        || Request::is('admin/business-settings/web-app/mail-config*')
                                        || Request::is('admin/business-settings/web-app/sms-module*')?'active':''}}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                   href="javascript:"
                                   title="{{translate('Branch Setup')}}"
                                >
                                    <i class="tio-website nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{translate('3rd Party')}}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{Request::is('admin/business-settings/web-app/payment-method*')
                                        || Request::is('admin/business-settings/web-app/third-party/fcm*')
                                        || Request::is('admin/business-settings/web-app/third-party*')
                                        || Request::is('admin/business-settings/web-app/mail-config*')
                                        || Request::is('admin/business-settings/web-app/sms-module*') ?'block':'none'}}">
                                    <li class="nav-item {{Request::is('admin/business-settings/web-app/payment-method')
                                        || Request::is('admin/business-settings/web-app/third-party/fcm*')
                                        || Request::is('admin/business-settings/web-app/third-party*')
                                        || Request::is('admin/business-settings/web-app/mail-config*')
                                        || Request::is('admin/business-settings/web-app/sms-module*') ?'active':''}}">

                                        <a class="nav-link " href="{{route('admin.business-settings.web-app.payment-method')}}"
                                           title="{{translate('3rd party configuration')}}"
                                        >
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="text-truncate">{{translate('3rd party configuration')}}</span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{Request::is('admin/business-settings/web-app/third-party/fcm*')?'active':''}}">
                                        <a class="nav-link " href="{{route('admin.business-settings.web-app.third-party.fcm-index')}}"
                                           title="{{translate('Push Notification')}}"
                                        >
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{translate('Push Notification')}}</span>
                                        </a>
                                    </li>

                                    <li class="nav-item {{Request::is('admin/business-settings/web-app/third-party/offline*')?'active':''}}">
                                        <a class="nav-link " href="{{route('admin.business-settings.web-app.third-party.offline-payment.list')}}"
                                           title="{{translate('Offline Payment')}}"
                                        >
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{translate('Offline Payment')}}</span>
                                        </a>
                                    </li>

                                    @if(feature_enabled('integrations.google_analytics'))
                                    <li class="nav-item {{Request::is('admin/business-settings/web-app/third-party/google-analytics*')?'active':''}}">
                                        <a class="nav-link " href="{{route('admin.business-settings.web-app.third-party.google-analytics')}}"
                                           title="{{translate('Google Analytics')}}"
                                        >
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{translate('Google Analytics')}}</span>
                                        </a>
                                    </li>
                                    @endif

                                    @if(feature_enabled('integrations.facebook_pixel'))
                                    <li class="nav-item {{Request::is('admin/business-settings/web-app/third-party/facebook-pixel*')?'active':''}}">
                                        <a class="nav-link " href="{{route('admin.business-settings.web-app.third-party.facebook-pixel')}}"
                                           title="{{translate('Facebook Pixel')}}"
                                        >
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{translate('Facebook Pixel')}}</span>
                                        </a>
                                    </li>
                                    @endif

                                    @if(feature_enabled('integrations.whatsapp_integration'))
                                    <li class="nav-item {{Request::is('admin/business-settings/web-app/third-party/chat-index*')?'active':''}}">
                                        <a class="nav-link " href="{{route('admin.business-settings.web-app.third-party.chat-index')}}"
                                           title="{{translate('WhatsApp Integration')}}"
                                        >
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{translate('WhatsApp Integration')}}</span>
                                        </a>
                                    </li>
                                    @endif

                                    @if(feature_enabled('integrations.sms_gateway'))
                                    <li class="nav-item {{Request::is('admin/business-settings/web-app/sms-module*')?'active':''}}">
                                        <a class="nav-link " href="{{route('admin.business-settings.web-app.sms-module')}}"
                                           title="{{translate('SMS Gateway')}}"
                                        >
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{translate('SMS Gateway')}}</span>
                                        </a>
                                    </li>
                                    @endif

                                    @if(feature_enabled('integrations.accounting_software'))
                                    <li class="nav-item {{Request::is('admin/business-settings/web-app/third-party/accounting-software*')?'active':''}}">
                                        <a class="nav-link " href="{{route('admin.business-settings.web-app.third-party.accounting-software')}}"
                                           title="{{translate('Accounting Software')}}"
                                        >
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{translate('Accounting Software')}}</span>
                                        </a>
                                    </li>
                                    @endif

                                    @if(feature_enabled('integrations.crm_integration'))
                                    <li class="nav-item {{Request::is('admin/business-settings/web-app/third-party/crm-integration*')?'active':''}}">
                                        <a class="nav-link " href="{{route('admin.business-settings.web-app.third-party.crm-integration')}}"
                                           title="{{translate('CRM Integration')}}"
                                        >
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{translate('CRM Integration')}}</span>
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </li>

                            @if(feature_enabled('content.enabled'))
                            <li class="navbar-vertical-aside-has-menu {{Request::is('admin/business-settings/page-setup/*')?'active':''}}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                   href="javascript:"
                                   title="{{translate('Content Management')}}"
                                >
                                    <i class="tio-pages-outlined nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{translate('Content Management')}}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{Request::is('admin/business-settings/page-setup/*')?'block':''}} {{Request::is('admin/business-settings/web-app/third-party/social-media')?'block':''}}">

                                    @if(feature_enabled('content.pages'))
                                    <li class="nav-item {{Request::is('admin/business-settings/page-setup/about-us')?'active':''}}">
                                        <a class="nav-link"
                                           href="{{route('admin.business-settings.page-setup.about-us')}}"
                                           title="{{translate('About Us')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{translate('About Us')}}</span>
                                        </a>
                                    </li>
                                    @endif

                                    @if(feature_enabled('content.terms_conditions'))
                                    <li class="nav-item {{Request::is('admin/business-settings/page-setup/terms-and-conditions')?'active':''}}">
                                        <a class="nav-link"
                                           href="{{route('admin.business-settings.page-setup.terms-and-conditions')}}"
                                           title="{{translate('Terms & Conditions')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{translate('Terms & Conditions')}}</span>
                                        </a>
                                    </li>
                                    @endif

                                    @if(feature_enabled('content.privacy_policy'))
                                    <li class="nav-item {{Request::is('admin/business-settings/page-setup/privacy-policy')?'active':''}}">
                                        <a class="nav-link"
                                           href="{{route('admin.business-settings.page-setup.privacy-policy')}}"
                                           title="{{translate('Privacy Policy')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{translate('Privacy Policy')}}</span>
                                        </a>
                                    </li>
                                    @endif

                                    @if(feature_enabled('content.faqs'))
                                    <li class="nav-item {{Request::is('admin/business-settings/page-setup/faq')?'active':''}}">
                                        <a class="nav-link"
                                           href="{{route('admin.business-settings.page-setup.faq')}}"
                                           title="{{translate('FAQs')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{translate('FAQs')}}</span>
                                        </a>
                                    </li>
                                    @endif

                                    @if(feature_enabled('content.blogs'))
                                    <li class="nav-item {{Request::is('admin/business-settings/blog*')?'active':''}}">
                                        <a class="nav-link"
                                           href="{{route('admin.business-settings.blog.index')}}"
                                           title="{{translate('Blogs')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{translate('Blogs')}}</span>
                                        </a>
                                    </li>
                                    @endif

                                    <li class="nav-item {{Request::is('admin/business-settings/page-setup/cancellation-policy')?'active':''}}">
                                        <a class="nav-link"
                                           href="{{route('admin.business-settings.page-setup.cancellation-policy')}}"
                                           title="{{translate('Cancellation Policy')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{translate('Cancellation Policy')}}</span>
                                        </a>
                                    </li>

                                    <li class="nav-item {{Request::is('admin/business-settings/page-setup/refund-policy')?'active':''}}">
                                        <a class="nav-link"
                                           href="{{route('admin.business-settings.page-setup.refund-policy')}}"
                                           title="{{translate('Refund Policy')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{translate('Refund Policy')}}</span>
                                        </a>
                                    </li>

                                    <li class="nav-item {{Request::is('admin/business-settings/page-setup/return-policy')?'active':''}}">
                                        <a class="nav-link"
                                           href="{{route('admin.business-settings.page-setup.return-policy')}}"
                                           title="{{translate('Return Policy')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{translate('Return Policy')}}</span>
                                        </a>
                                    </li>

                                    <li class="nav-item {{Request::is('admin/business-settings/web-app/third-party/social-media')?'active':''}}">
                                        <a class="nav-link "
                                           href="{{route('admin.business-settings.web-app.third-party.social-media')}}"
                                           title="{{translate('Social Media')}}"
                                        >
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{translate('Social Media')}}</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            @endif

                            <li class="nav-item mt-0
                                {{Request::is('admin/business-settings/web-app/system-setup*')?'active':''}}">
                                <a class="nav-link"
                                   href="{{route('admin.business-settings.web-app.system-setup.language.index')}}"
                                   title="{{translate('system_settings')}}">
                                    <i class="tio-security-on-outlined nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{translate('system_setup')}}</span>
                                </a>
                            </li>

                        @endif

                        @if(feature_enabled('system.enabled'))

                            <li class="nav-item">
                                <small class="nav-subtitle">{{translate('system')}} {{translate('addon')}}</small>
                                <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                            </li>

                            @php
                                // Simple check for addon directories
                                $hasAddons = false;
                                $modulesPath = base_path('Modules');
                                if (is_dir($modulesPath)) {
                                    $directories = array_diff(scandir($modulesPath), array('.', '..', 'readme.txt'));
                                    foreach ($directories as $directory) {
                                        if (is_dir($modulesPath . '/' . $directory) && is_dir($modulesPath . '/' . $directory . '/Addon')) {
                                            $hasAddons = true;
                                            break;
                                        }
                                    }
                                }
                            @endphp

                            <li class="navbar-vertical-aside-has-menu {{Request::is('admin/system-addon')?'active':''}} {{ !$hasAddons ? 'disabled-menu-item' : '' }}">
                                @if($hasAddons)
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                       href="{{route('admin.system-addon.index')}}" title="{{translate('System Addons')}}">
                                        <i class="tio-add-circle-outlined nav-icon"></i>
                                        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{translate('System Addons')}}
                                    </span>
                                    </a>
                                @else
                                    <a class="js-navbar-vertical-aside-menu-link nav-link disabled-link"
                                       href="javascript:void(0)"
                                       title="{{translate('No system addons installed. Upload addon files to enable this feature.')}}"
                                       data-toggle="tooltip"
                                       data-placement="right"
                                       style="opacity: 0.5; cursor: not-allowed; pointer-events: none;">
                                        <i class="tio-add-circle-outlined nav-icon"></i>
                                        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{translate('System Addons')}} <small>({{translate('No addons')}})</small>
                                    </span>
                                    </a>
                                @endif
                            </li>

                            @if(count(config('addon_admin_routes'))>0)
                                <li class="navbar-vertical-aside-has-menu {{Request::is('admin/payment/configuration/*') || Request::is('admin/sms/configuration/*')?'active':''}} mb-5">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:" >
                                        <i class="tio-puzzle nav-icon"></i>
                                        <span  class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{translate('Addon Menus')}}</span>
                                    </a>
                                    <ul class="js-navbar-vertical-aside-submenu nav nav-sub" style="display: {{Request::is('admin/payment/configuration/*') || Request::is('admin/sms/configuration/*')?'block':'none'}}">
                                        @foreach(config('addon_admin_routes') as $routes)
                                            @foreach($routes as $route)
                                                <li class="navbar-vertical-aside-has-menu {{Request::is($route['path'])  ? 'active' :''}}">
                                                    <a class="js-navbar-vertical-aside-menu-link nav-link "
                                                       href="{{ $route['url'] }}" title="{{ translate($route['name']) }}">
                                                        <span class="tio-circle nav-indicator-icon"></span>
                                                        <span class="text-truncate">{{ translate($route['name']) }}</span>
                                                    </a>
                                                </li>
                                            @endforeach
                                        @endforeach
                                    </ul>
                                </li>
                            @endif

                        @endif

                        <li class="nav-item">
                            <div class="nav-divider"></div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </aside>
</div>


@push('script_2')
    <script>
        $(window).on('load', function () {
            if ($(".navbar-vertical-content li.active").length) {
                $('.navbar-vertical-content').animate({
                    scrollTop: $(".navbar-vertical-content li.active").offset().top - 150
                }, 10);
            }
        });

        var $rows = $('#navbar-vertical-content .navbar-nav > li');
        $('#search-sidebar-menu').keyup(function () {
            var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();

            $rows.show().filter(function () {
                var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
                return !~text.indexOf(val);
            }).hide();
        });
    </script>
@endpush
