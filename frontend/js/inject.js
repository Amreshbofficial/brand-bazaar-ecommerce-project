function injectComponent(selector, src) {
  fetch(src)
    .then((res) => res.text())
    .then((html) => (document.querySelector(selector).innerHTML = html));
}
