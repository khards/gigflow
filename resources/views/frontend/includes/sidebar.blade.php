<div class="c-sidebar c-sidebar-dark c-sidebar-fixed c-sidebar-lg-show" id="sidebar">
    <div class="c-sidebar-brand d-lg-down-none">
        <svg class="c-sidebar-brand-full" width="118" height="46" alt="CoreUI Logo">
            <use xlink:href="{{ asset('img/brand/coreui.svg#full') }}"></use>
        </svg>
        <svg class="c-sidebar-brand-minimized" width="46" height="46" alt="CoreUI Logo">
            <use xlink:href="{{ asset('img/brand/coreui.svg#signet') }}"></use>
        </svg>
    </div><!--c-sidebar-brand-->

    <ul class="c-sidebar-nav">
        <li class="c-sidebar-nav-item">
            <x-utils.link
                class="c-sidebar-nav-link"
                :href="route('admin.dashboard')"
                :active="activeClass(Route::is('admin.dashboard'), 'c-active')"
                icon="c-sidebar-nav-icon cil-speedometer"
                :text="__('Dashboard')" />
        </li>


            <li class="c-sidebar-nav-title">@lang('My Account')</li>
            <li class="c-sidebar-nav-item">
                <x-utils.link
                    class="c-sidebar-nav-link"
                    href="/account/calendar/"
                    icon="c-sidebar-nav-icon cil-list"
                    :text="__('My Calendar')"
                />
            </li>


            @if ($logged_in_user->isAdmin())
            <li class="c-sidebar-nav-title">@lang('System')</li>
            <li class="c-sidebar-nav-dropdown {{ activeClass(Route::is('admin.auth.user.*') || Route::is('admin.auth.role.*'), 'c-open c-show') }}">
                <x-utils.link
                    href="#"
                    icon="c-sidebar-nav-icon cil-user"
                    class="c-sidebar-nav-dropdown-toggle"
                    :text="__('Access')" />

                <ul class="c-sidebar-nav-dropdown-items">
                        <li class="c-sidebar-nav-item">
                            <x-utils.link
                                :href="route('admin.auth.user.index')"
                                class="c-sidebar-nav-link"
                                :text="__('User Management')"
                                :active="activeClass(Route::is('admin.auth.user.*'), 'c-active')" />
                        </li>

                        <li class="c-sidebar-nav-item">
                            <x-utils.link
                                :href="route('admin.auth.role.index')"
                                class="c-sidebar-nav-link"
                                :text="__('Role Management')"
                                :active="activeClass(Route::is('admin.auth.role.*'), 'c-active')" />
                        </li>
                </ul>
            </li>

            <li class="c-sidebar-nav-item">
                <x-utils.link
                    class="c-sidebar-nav-link"
                    href="/admin/log-viewer"
                    icon="c-sidebar-nav-icon cil-list"
                    :text="__('Logs')" />
            </li>
        @endif

        <!--
        <li class="c-sidebar-nav-item">
            <x-utils.link
                class="c-sidebar-nav-link"
                href="/business"
                icon="c-sidebar-nav-icon cil-list"
                :text="__('Business')"
            />
        </li>
-->







        <li class="c-sidebar-nav-dropdown c-open c-show">
        <!--
            <x-utils.link
                href="#"
                icon="c-sidebar-nav-icon cil-user"
                class="c-sidebar-nav-dropdown-toggle"
                :text="__('Business')" />
-->
            <ul class="c-sidebar-nav-dropdown-items">
                @foreach (Auth::user()->businesses()->get() as $business)
                    <li class="c-sidebar-nav-item">
                        <x-utils.link
                            class="c-sidebar-nav-link"
                            href="/business/view/{{ $business->id }}"
                            icon="c-sidebar-nav-icon cil-list"
                            :text="__('Business Settings')"
                        />
                    </li>
                    <li class="c-sidebar-nav-item">
                        <x-utils.link
                            class="c-sidebar-nav-link"
                            href="/business/{{ $business->id }}/products"
                            icon="c-sidebar-nav-icon cil-list"
                            :text="__('Products')"
                        />
                    </li>

                    <li class="c-sidebar-nav-item">
                        <x-utils.link
                            class="c-sidebar-nav-link"
                            href="/business/{{ $business->id }}/orders"
                            icon="c-sidebar-nav-icon cil-list"
                            :text="__('Orders & Bookings')"
                        />
                    </li>


                    <li class="c-sidebar-nav-item">
                        <x-utils.link
                            class="c-sidebar-nav-link"
                            href="/business/{{ $business->id }}/schedules"
                            icon="c-sidebar-nav-icon cil-list"
                            :text="__('Schedules')"
                        />
                    </li>
                    <li class="c-sidebar-nav-item">
                        <x-utils.link
                            class="c-sidebar-nav-link"
                            href="/business/{{ $business->id }}/forms"
                            icon="c-sidebar-nav-icon cil-list"
                            :text="__('Forms')"
                        />
                    </li>
<?php
/*
 * Not working: href="{{ route('message-template.view', ['business' => $business->id]) }}"
 * Route [message-template.view] not defined.
 */
 ?>

                    <li class="c-sidebar-nav-item">
                        <x-utils.link
                            class="c-sidebar-nav-link"
                            href="/business/{{ $business->id }}/message-templates"
                            icon="c-sidebar-nav-icon cil-list"
                            :text="__('Message Templates')"
                        />
                    </li>

                    <li class="c-sidebar-nav-item">
                        <x-utils.link
                            class="c-sidebar-nav-link"
                            href="{{ route('unisharp.lfm.show') }}"
                            icon="c-sidebar-nav-icon cil-list"
                            :text="__('File Manager')"
                        />
                    </li>

                    <hr/>

                    <li class="c-sidebar-nav-item">
                        <x-utils.link
                            class="c-sidebar-nav-link"
                            href="/messages"
                            icon="c-sidebar-nav-icon cil-list"
                            :text="__('Messages')"
                        />
                    </li>

                    <hr/>

                    <li class="c-sidebar-nav-item">
                        <x-utils.link
                            class="c-sidebar-nav-link"
                            href="/order/{{ $business->id }}"
                            icon="c-sidebar-nav-icon cil-list"
                            :text="__('New booking')"
                        />
                    </li>
                @endforeach
            </ul>
        </li>


    </ul>

    <button class="c-sidebar-minimizer c-class-toggler" type="button" data-target="_parent" data-class="c-sidebar-minimized"></button>
</div><!--sidebar-->
