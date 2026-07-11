// resources/js/filament/rich-content-plugins/relative-links.js
import Link from '@tiptap/extension-link'

export default Link.configure({
  autolink: true,
  openOnClick: false,
  isAllowedUri: (url, ctx) => {
    if (typeof url !== 'string') return false
    const v = url.trim()
    if (!v) return false

    // względne + kotwice
    if (v.startsWith('/') || v.startsWith('./') || v.startsWith('../') || v.startsWith('#')) return true
    // dodatkowe protokoły
    if (/^(mailto:|tel:)/i.test(v)) return true

    // fallback — domyślna walidacja TipTap (http/https, itp.)
    return ctx.defaultValidate(v)
  },
})
