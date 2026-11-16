import DOMPurify from 'dompurify'

const CMS_HTML_OPTIONS = {
  ADD_TAGS: ['iframe'],
  ADD_ATTR: [
    'target',
    'rel',
    'width',
    'height',
    'frameborder',
    'allow',
    'allowfullscreen',
    'title',
    'loading',
    'referrerpolicy',
  ],
}

export function sanitizeHtml(content: string): string {
  if (!content) return ''
  return DOMPurify.sanitize(content, { ADD_ATTR: ['target', 'rel'] })
}

export function sanitizeCmsHtml(content: string, apiBaseUrl?: string): string {
  if (!content) return ''

  const cleanHtml = DOMPurify.sanitize(content, CMS_HTML_OPTIONS)
  if (typeof document === 'undefined') return cleanHtml

  const template = document.createElement('template')
  template.innerHTML = cleanHtml

  let backendOrigin = ''
  try {
    backendOrigin = apiBaseUrl ? new URL(apiBaseUrl).origin : ''
  } catch {
    backendOrigin = ''
  }

  for (const image of template.content.querySelectorAll<HTMLImageElement>('img[src]')) {
    try {
      const imageUrl = new URL(
        image.getAttribute('src') || '',
        backendOrigin || window.location.origin,
      )
      if (imageUrl.protocol !== 'http:' && imageUrl.protocol !== 'https:') {
        image.removeAttribute('src')
        continue
      }

      image.setAttribute('src', imageUrl.href)
      image.loading = 'lazy'
      image.referrerPolicy = 'no-referrer'
    } catch {
      image.removeAttribute('src')
    }
  }

  for (const iframe of template.content.querySelectorAll<HTMLIFrameElement>('iframe')) {
    const src = iframe.getAttribute('src')
    let embedUrl: URL | undefined

    try {
      embedUrl = src ? new URL(src) : undefined
    } catch {
      embedUrl = undefined
    }

    const trustedHost =
      embedUrl?.protocol === 'https:' &&
      [
        'youtube.com',
        'www.youtube.com',
        'youtube-nocookie.com',
        'www.youtube-nocookie.com',
      ].includes(embedUrl.hostname) &&
      /^\/embed\/[\w-]+$/.test(embedUrl.pathname)

    if (!trustedHost) {
      iframe.remove()
      continue
    }

    iframe.loading = 'lazy'
    iframe.referrerPolicy = 'strict-origin-when-cross-origin'
    iframe.title ||= 'Video de YouTube'
    iframe.setAttribute('sandbox', 'allow-scripts allow-same-origin allow-presentation')
    iframe.setAttribute(
      'allow',
      'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture',
    )
    iframe.setAttribute('allowfullscreen', '')
  }

  return template.innerHTML
}
