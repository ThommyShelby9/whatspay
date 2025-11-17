<!-- Page Header Start-->
<div class="page-header">
    <div class="header-wrapper row m-0">

        @include('admin.logomenu')
        @include('admin.search')

        <div class="nav-right col-xl-8 col-lg-12 col-auto pull-right right-header p-0">
            <ul class="nav-menus">
                <li class="profile-nav onhover-dropdown pe-0 py-0">
                    <div class="d-flex align-items-center profile-media"><img class="b-r-25"
                            src="/design/admin/assets/images/dashboard/profile.png" alt="">
                        <div class="flex-grow-1 user"><span>{{ $viewData['userfirstname'] }}
                                {{ $viewData['userlastname'] }}</span>
                            <p class="mb-0 font-nunito">{{ $viewData['userprofile'] }}
                                <svg>
                                    <use href="/design/admin/assets/svg/icon-sprite.svg#header-arrow-down"></use>
                                </svg>
                            </p>
                        </div>
                    </div>
                    <ul class="profile-dropdown onhover-show-div">
                        <li>
                            @if (isset($viewData['userprofile']))
                                @if ($viewData['userprofile'] == 'DIFFUSEUR')
                                    <a href="{{ route('influencer.profile') }}"><i data-feather="user"></i><span>Mon
                                            Profil</span></a>
                                @elseif($viewData['userprofile'] == 'ANNONCEUR')
                                    <a href="/admin/client/profile"><i data-feather="user"></i><span>Mon
                                            Profil</span></a>
                                @elseif($viewData['userprofile'] == 'ADMIN')
                                    <a href="/admin/profil"><i data-feather="user"></i><span>Mon Profil</span></a>
                                @else
                                    <a href="/admin/profil"><i data-feather="user"></i><span>Mon Profil</span></a>
                                @endif
                            @else
                                <a href="/admin/profil"><i data-feather="user"></i><span>Mon Profil</span></a>
                            @endif
                        </li>
                        <li><a href="/admin/logout"> <i data-feather="log-in"></i><span>D&eacute;connexion</span></a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
        <script class="result-template" type="text/x-handlebars-template">
      <div class="ProfileCard u-cf">
        <div class="ProfileCard-avatar"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-airplay m-0"><path d="M5 17H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-1"></path><polygon points="12 15 17 21 7 21 12 15"></polygon></svg></div>
        <div class="ProfileCard-details">
          <div class="ProfileCard-realName">{{$viewData["userfirstname"]}}</div>
        </div>
      </div>
    </script>
        <script class="empty-template" type="text/x-handlebars-template"><div class="EmptyMessage">Votre recherche a donn&eacute; 0 resultats</div></script>
    </div>
</div>
<!-- Page Header Ends -->
