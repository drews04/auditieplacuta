<script src="{{ asset('js/user-menu.js') }}?v={{ filemtime(public_path('js/user-menu.js')) }}"></script>

<div class="user-menu-container" id="ap-user-menu">
    @auth
        <span id="user-name" class="user-dropdown-toggle">Salut, {{ Auth::user()->name }}</span>

        <ul id="user-dropdown" class="user-dropdown-list hidden">
            <li><a href="{{ route('user.user_profile') }}">Profilul meu</a></li>
            <li><a href="{{ route('user.statistics') }}">Statistici personale</a></li>
            <li><a href="{{ route('abilities.index') }}">Abilitățile mele</a></li>
            <li><a href="{{ route('user.settings') }}">Setări cont</a></li>
            <li class="logout">
                <a href="{{ route('logout.get') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Deconectare</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
            </li>
        </ul>
    @else
        <span class="username">Salut, vizitator</span>
    @endauth
</div>

