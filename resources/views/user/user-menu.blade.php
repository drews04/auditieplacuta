
<div class="user-menu">
    <span id="user-name" class="username" style="cursor: pointer;">
        @auth
            Salut, {{ Auth::user()->name }}
        @else
            Salut, vizitator
        @endauth
    </span>

    <ul id="user-dropdown" class="hidden">
        <li><a href="{{ route('user.user_profile') }}">Profilul meu</a></li>
        <li><a href="{{ route('user.statistics') }}">Statistici personale</a></li>
        <li><a href="{{ route('user.songs') }}">Melodiile mele</a></li>
        <li><a href="{{ route('user.user-trivia') }}">Trivia mea</a></li>
        <li><a href="{{ route('user.abilities') }}">Abilitățile mele</a></li>
        <li><a href="{{ route('user.votes') }}">Voturile mele</a></li>
        <li><a href="{{ route('user.settings') }}">Setări cont</a></li>
        <li class="logout">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" style="all: unset; color: #ff4c4c; font-weight: 600; cursor: pointer;">
                    Deconectare
                </button>
            </form>
        </li>
    </ul>
</div>
