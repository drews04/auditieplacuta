{{-- Styled Theme picker (same look as your old modal / winner UI) --}}
<div class="ap-theme-picker p-3">
<form method="POST" action="{{ route('concurs.theme.pick') }}">
    @csrf

    <div class="d-flex flex-wrap justify-content-center gap-2 alege-tema-inputs">
      <select name="category" class="form-select alege-tema-select" style="max-width:220px;">
        <option value="CSD">CSD — Cu și despre</option>
        <option value="ITC">ITC — În titlu cuvânt</option>
        <option value="Artiști">Artiști</option>
        <option value="Genuri">Genuri</option>
      </select>

      <input
        type="text"
        name="theme"
        class="form-control alege-tema-text"
        placeholder="ex: Ploaie / Metal / Queen"
        style="max-width:320px;"
        maxlength="120"
        required
      >
    </div>

    <div class="w-pill mt-2 alege-tema-preview" aria-live="polite">
      Tema finală: <strong class="ap-preview">—</strong>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3">
      <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Anulează</button>
      <button type="submit" class="btn btn-success btn-sm">{{ $submitLabel ?? 'Salvează' }}</button>
    </div>
  </form>
</div>

{{-- Scope the preview logic to THIS block only --}}
<script>
(() => {
  const root = document.currentScript.previousElementSibling; // .ap-theme-picker
  const form = root.querySelector('form');
  const cat  = form.querySelector('select[name="category"]');
  const th   = form.querySelector('input[name="theme"]');
  const pv   = root.querySelector('.ap-preview');

  function update() {
    const c = (cat.value || '').trim();
    const t = (th.value || '').trim();
    pv.textContent = t ? `${c} — ${t}` : `${c} —`;
  }
  cat.addEventListener('change', update);
  th.addEventListener('input', update);
  update();
})();
</script>

