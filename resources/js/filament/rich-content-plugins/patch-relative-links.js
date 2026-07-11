// Patchuje link-modal w Filament RichEditor tak, by akceptował linki względne.
// Zamienia <input type="url"> na <input type="text"> dopiero, gdy modal pojawi się w DOM.
// Działa niezależnie od wersji motywu, trzyma się selektorów modali Filament (fi-modal).

(function () {
  const tweakInputs = () => {
    // Szukamy inputów URL w aktywnych modalach Filament
    document.querySelectorAll('.fi-modal input[type="url"], .fi-modal [type="url"]').forEach((el) => {
      // heurystyki: zwykle name="href" lub placeholder wygląda na URL
      const looksLikeHref =
        (el.name && el.name.toLowerCase() === 'href') ||
        /https?:\/\//i.test(el.getAttribute('placeholder') || '') ||
        el.autocomplete === 'url';

      if (!looksLikeHref) return;

      try {
        el.setAttribute('type', 'text');          // odblokuj wpisywanie / ./ ../ #
        el.removeAttribute('pattern');            // usuń ewentualny regex
        el.removeAttribute('inputmode');          // nie sugeruj klawiatury URL
        el.setCustomValidity && el.setCustomValidity(''); // wyczyść błąd
      } catch (_) {}
    });
  };

  // 1) gdy modal się montuje / zmienia
  const mo = new MutationObserver(() => tweakInputs());
  mo.observe(document.documentElement, { subtree: true, childList: true });

  // 2) na wszelki wypadek po loadzie i po kliknięciu w dokument
  window.addEventListener('load', tweakInputs, { once: true });
  document.addEventListener('click', () => setTimeout(tweakInputs, 0));
})();
