(function () {
  if (!sessionStorage.getItem('csc_page_transition')) return
  sessionStorage.removeItem('csc_page_transition')

  var veil = document.createElement('div')
  veil.id = 'csc-page-veil'
  veil.style.cssText = [
    'position:fixed', 'inset:0', 'z-index:999999', 'pointer-events:all',
    'background:#f5f6fa', // the IMIS page colour, as in every system under IMIS
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
