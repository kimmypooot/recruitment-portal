// Applies the saved/system theme before first paint to avoid a light flash.
// External file (not inline) because the CSP only allows script-src 'self'.
(function () {
  try {
    var t = localStorage.getItem('theme');
    var dark = t === 'dark' || (t !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    if (dark) document.documentElement.classList.add('dark');
  } catch (e) {}
})();
