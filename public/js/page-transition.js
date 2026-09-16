(function () {
  if (!sessionStorage.getItem('csc_page_transition')) return
  sessionStorage.removeItem('csc_page_transition')

  var veil = document.createElement('div')
  veil.id = 'csc-page-veil'
  veil.style.cssText = [
    'position:fixed', 'inset:0', 'z-index:999999', 'pointer-events:all',
    'background:linear-gradient(135deg,#f0eef9 0%,#e8eafa 50%,#fdeef0 100%)',
    'opacity:1', 'transition:opacity 0.4s ease',
  ].join(';')
  document.documentElement.appendChild(veil)

  window.addEventListener('load', function () {
    requestAnimationFrame(function () {
      veil.style.opacity = '0'
      setTimeout(function () { veil.remove() }, 420)
    })
  })
})()
