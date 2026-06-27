/**
 * Navigate with a seamless full-page transition:
 * 1. Marks sessionStorage so the incoming page knows to start veiled.
 * 2. Fades the current page out via a raw DOM overlay (survives Vue teardown).
 * 3. The new page's inline script (app.blade.php) immediately recreates the
 *    veil before Vue boots, then fades it out once the page is loaded.
 */
export function navigateTo(url) {
  sessionStorage.setItem('csc_page_transition', '1')

  const veil = document.createElement('div')
  veil.style.cssText = [
    'position:fixed', 'inset:0', 'z-index:999999', 'pointer-events:all',
    'background:linear-gradient(135deg,#f0eef9 0%,#e8eafa 50%,#fdeef0 100%)',
    'opacity:0', 'transition:opacity 0.3s ease',
  ].join(';')
  document.body.appendChild(veil)

  requestAnimationFrame(() => {
    veil.style.opacity = '1'
    setTimeout(() => { window.location.href = url }, 320)
  })
}
