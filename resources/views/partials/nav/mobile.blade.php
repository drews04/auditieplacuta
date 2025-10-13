{{-- Mobile menu: use plain URLs so missing named routes never crash --}}
<li class="nav-item"><a class="nav-link text-white" href="{{ url('/') }}">Acasă</a></li>

<li class="nav-item">
  <a class="nav-link text-white"
     href="{{ \Illuminate\Support\Facades\Route::has('concurs') ? route('concurs') : url('/concurs') }}">
    Concurs
  </a>
</li>

<li class="nav-item"><a class="nav-link text-white" href="{{ url('/muzica') }}">Muzică</a></li>
<li class="nav-item"><a class="nav-link text-white" href="{{ url('/arena') }}">Arena</a></li>
<li class="nav-item"><a class="nav-link text-white" href="{{ url('/magazin') }}">Magazin</a></li>
