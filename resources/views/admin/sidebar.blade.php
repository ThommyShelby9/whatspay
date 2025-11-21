<!-- Page Sidebar Start-->
<div class="sidebar-wrapper" data-layout="stroke-svg">
    <div>
        <div class="logo-wrapper"><a href="/"> <img class="img-fluid for-light" src="/design/logo/logo_large.png"
                    alt="" style="width: 150px; height: 50px;"><img class="img-fluid for-dark"
                    src="/design/logo/logo_large.png" alt="" style="width: 150px; height: 50px;"></a>
            <div class="toggle-sidebar">
                <svg class="sidebar-toggle">
                    <use href="/design/admin/assets/svg/icon-sprite.svg#toggle-icon"></use>
                </svg>
            </div>
        </div>
        <div class="logo-icon-wrapper"><a href="/"><img class="img-fluid" src="/design/logo/logo.png"
                    alt="" style="width: 50px; height: 50px;"></a></div>
        <nav class="sidebar-main ">
            <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
            <div id="sidebar-menu">
                <ul class="sidebar-links" id="simple-bar">


                    @if ($viewData['userprofile'] == 'ADMIN')
                        <li class="sidebar-main-title">
                            <div>
                                <h6>ADMIN</h6>
                            </div>
                        </li>
                        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                            <a class="sidebar-link sidebar-title {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                                href="{{ route('admin.dashboard') }}">
                                <svg class="stroke-icon">
                                    <use href="/design/admin/assets/svg/icon-sprite.svg#stroke-home"></use>
                                </svg>
                                <svg class="fill-icon">
                                    <use href="/design/admin/assets/svg/icon-sprite.svg#fill-home"></use>
                                </svg>
                                <span>Tableau de bord</span>
                            </a>
                        </li>
                        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                            <a class="sidebar-link sidebar-title" href="#">
                                <i class="stroke-icon fa fa-user"></i>
                                <i class="fill-icon fa fa-user"></i>
                                <span>Utilisateurs</span>
                            </a>
                            <ul class="sidebar-submenu">
                                <li><a href="/admin/users_admin">Admins</a></li>
                                <li><a href="/admin/users_annonceur">Annonceurs</a></li>
                                <li><a href="/admin/users_diffuseur">Diffuseurs</a></li>
                            </ul>
                        </li>

                        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                            <a class="sidebar-link sidebar-title" href="#">
                                <i class="stroke-icon fa fa-bullhorn"></i>
                                <i class="fill-icon fa fa-bullhorn"></i>
                                <span>Campagnes</span>
                            </a>
                            <ul class="sidebar-submenu">
                                <li><a href="/admin/tasks">Demandes</a></li>
                                <!-- li><a  href="/admin/assignments">Soumissions</a></li -->
                            </ul>
                        </li>
                        <!-- Add this to your admin navigation menu -->
                        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                            <a class="sidebar-link sidebar-title {{ request()->routeIs('admin.whatsapp_messaging') ? 'active' : '' }}"
                                href="#">
                                <i class="stroke-icon fa fa-whatsapp"></i>
                                <i class="fill-icon fa fa-whatsapp"></i>
                                <span>WhatsApp</span>
                            </a>
                            <ul class="sidebar-submenu">
                                <li><a href="{{ route('admin.whatsapp_messaging') }}">Messages</a></li>
                                <li><a href="/admin/whatsappnumbers"
                                        class="{{ request()->is('admin/whatsappnumbers') ? 'active' : '' }}">Numéros
                                        WhatsApp</a></li>
                            </ul>
                        </li>
                    @endif

                    @if ($viewData['userprofile'] == 'ANNONCEUR')
                        <li class="sidebar-main-title">
                            <div>
                                <h6>Client</h6>
                            </div>
                        </li>

                        <!-- Dashboard -->
                        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                            <a class="sidebar-link sidebar-title {{ request()->routeIs('admin.client.dashboard') ? 'active' : '' }}"
                                href="{{ route('admin.client.dashboard') }}">
                                <svg class="stroke-icon">
                                    <use href="/design/admin/assets/svg/icon-sprite.svg#stroke-home"></use>
                                </svg>
                                <svg class="fill-icon">
                                    <use href="/design/admin/assets/svg/icon-sprite.svg#fill-home"></use>
                                </svg>
                                <span>Tableau de bord</span>
                            </a>
                        </li>

                        <!-- Campagnes -->
                        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                            <a class="sidebar-link sidebar-title {{ request()->routeIs('announcer.campaigns.*') ? 'active' : '' }}"
                                href="{{ route('announcer.campaigns.index') }}">
                                <svg class="stroke-icon">
                                    <use href="/design/admin/assets/svg/icon-sprite.svg#stroke-ecommerce"></use>
                                </svg>
                                <svg class="fill-icon">
                                    <use href="/design/admin/assets/svg/icon-sprite.svg#fill-ecommerce"></use>
                                </svg>
                                <span>Mes Campagnes</span>
                            </a>
                        </li>

                        <!-- Diffuseurs -->


                        <!-- Rapports -->
                        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                            <a class="sidebar-link sidebar-title {{ request()->routeIs('announcer.reports.*') ? 'active' : '' }}"
                                href="{{ route('announcer.reports.index') }}">
                                <i class="stroke-icon fa fa-file"></i>
                                <i class="fill-icon fa fa-file"></i>
                                <span>Rapports & Analyses</span>
                            </a>
                        </li>

                        <!-- Portefeuille -->
                        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                            <a class="sidebar-link sidebar-title {{ request()->routeIs('announcer.wallet.*') ? 'active' : '' }}"
                                href="{{ route('announcer.wallet') }}">
                                <i class="stroke-icon fa fa-money"></i>
                                <i class="fill-icon fa fa-money"></i>
                                <span>Portefeuille</span>
                            </a>
                        </li>

                        <!-- Messages -->


                        <!-- Paramètres -->
                        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                            <a class="sidebar-link sidebar-title {{ request()->routeIs('announcer.settings.*') ? 'active' : '' }}"
                                href="{{ route('announcer.settings.index') }}">
                                <i class="stroke-icon fa fa-cog"></i>
                                <i class="fill-icon fa fa-cog"></i>
                                <span>Paramètres</span>
                            </a>
                        </li>

                        <!-- Menu existant (WhatsApp) -->
                        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                            <a class="sidebar-link sidebar-title" href="#">
                                <i class="stroke-icon fa fa-whatsapp"></i>
                                <i class="fill-icon fa fa-whatsapp"></i>
                                <span>Whatsapp</span>
                            </a>
                            <ul class="sidebar-submenu">
                                <li><a href="/admin/whatsappnumbers">Configuration</a></li>
                                <!-- li><a href="/admin/tasks">Demandes</a></li -->
                                <!-- li><a href="/admin/assignments">Soumissions</a></li -->
                            </ul>
                        </li>
                    @endif

                    @if ($viewData['userprofile'] == 'DIFFUSEUR')
                        <li class="sidebar-main-title">
                            <div>
                                <h6>Diffuseur</h6>
                            </div>
                        </li>

                        <!-- Dashboard -->
                        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                            <a class="sidebar-link sidebar-title {{ request()->routeIs('influencer.dashboard') ? 'active' : '' }}"
                                href="{{ route('influencer.dashboard') }}">
                                <svg class="stroke-icon">
                                    <use href="/design/admin/assets/svg/icon-sprite.svg#stroke-home"></use>
                                </svg>
                                <svg class="fill-icon">
                                    <use href="/design/admin/assets/svg/icon-sprite.svg#fill-home"></use>
                                </svg>
                                <span>Tableau de bord</span>
                            </a>
                        </li>

                        <!-- Campagnes -->
                        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                            <a class="sidebar-link sidebar-title" href="#">
                                <svg class="stroke-icon">
                                    <use href="/design/admin/assets/svg/icon-sprite.svg#stroke-task"></use>
                                </svg>
                                <svg class="fill-icon">
                                    <use href="/design/admin/assets/svg/icon-sprite.svg#fill-task"></use>
                                </svg>
                                <span>Campagnes</span>
                            </a>
                            <ul class="sidebar-submenu">
                                <li><a href="{{ route('influencer.campaigns.available') }}"
                                        class="{{ request()->routeIs('influencer.campaigns.available') ? 'active' : '' }}">
                                        Disponibles</a></li>
                                <li><a href="{{ route('influencer.campaigns.accepted') }}"
                                        class="{{ request()->routeIs('influencer.campaigns.accepted') ? 'active' : '' }}">Mes
                                        campagnes</a></li>
                            </ul>
                        </li>

                        <!-- Performances -->
                        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                            <a class="sidebar-link sidebar-title {{ request()->routeIs('influencer.performance') ? 'active' : '' }}"
                                href="{{ route('influencer.performance') }}">
                                <i class="stroke-icon fa fa-certificate"></i>
                                <i class="fill-icon fa fa-certificate"></i>
                                <span>Performances</span>
                            </a>
                        </li>

                        <!-- Gains -->
                        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                            <a class="sidebar-link sidebar-title {{ request()->routeIs('influencer.earnings') ? 'active' : '' }}"
                                href="{{ route('influencer.earnings') }}">
                                <i class="stroke-icon fa fa-money"></i>
                                <i class="fill-icon fa fa-money"></i>
                                <span>Mes gains</span>
                            </a>
                        </li>

                        <!-- Messages -->


                        <!-- Profil -->
                        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                            <a class="sidebar-link sidebar-title {{ request()->routeIs('influencer.profile') ? 'active' : '' }}"
                                href="{{ route('influencer.profile') }}">
                                <svg class="stroke-icon">
                                    <use href="/design/admin/assets/svg/icon-sprite.svg#stroke-user"></use>
                                </svg>
                                <svg class="fill-icon">
                                    <use href="/design/admin/assets/svg/icon-sprite.svg#fill-user"></use>
                                </svg>
                                <span>Mon profil</span>
                            </a>
                        </li>

                        <!-- WhatsApp -->
                        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                            <a class="sidebar-link sidebar-title" href="#">
                                <svg class="stroke-icon">
                                    <use href="/design/admin/assets/svg/icon-sprite.svg#stroke-chat"></use>
                                </svg>
                                <svg class="fill-icon">
                                    <use href="/design/admin/assets/svg/icon-sprite.svg#fill-chat"></use>
                                </svg>
                                <span>WhatsApp</span>
                            </a>
                            <ul class="sidebar-submenu">
                                <li><a href="{{ route('influencer.whatsapp') }}"
                                        class="{{ request()->routeIs('influencer.whatsapp') ? 'active' : '' }}">Configuration</a>
                                </li>
                                {{-- <li><a href="/admin/whatsappnumbers"
                                        class="{{ request()->is('admin/whatsappnumbers') ? 'active' : '' }}">Num&eacute;ros
                                        WhatsApp</a></li> --}}
                            </ul>
                        </li>

                        <!-- Paramètres -->
                        <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                            <a class="sidebar-link sidebar-title {{ request()->routeIs('influencer.settings') ? 'active' : '' }}"
                                href="{{ route('influencer.settings') }}">
                                <i class="stroke-icon fa fa-cog"></i>
                                <i class="fill-icon fa fa-cog"></i>
                                <span>Paramètres</span>
                            </a>
                        </li>
                    @endif

                </ul>
            </div>
            <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
        </nav>
    </div>
</div>
<!-- Page Sidebar Ends-->
