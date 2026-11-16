import { onBeforeUnmount, onMounted, type Ref } from 'vue'
import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

interface RevealOptions {
  selector?: string
  stagger?: number
  y?: number
  duration?: number
  delay?: number
  ease?: string
  once?: boolean
}

export function useGsapReveal(
  containerRef: Ref<HTMLElement | null>,
  options: RevealOptions = {},
) {
  let ctx: gsap.Context | undefined

  const {
    selector = '[data-reveal]',
    stagger = 0.08,
    y = 24,
    duration = 0.6,
    delay = 0,
    ease = 'power2.out',
    once = true,
  } = options

  onMounted(() => {
    if (typeof window === 'undefined') return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
    if (!containerRef.value) return

    gsap.registerPlugin(ScrollTrigger)

    ctx = gsap.context(() => {
      const elements = gsap.utils.toArray<HTMLElement>(selector, containerRef.value)
      if (!elements.length) return

      // Animate single items or groups with ScrollTrigger
      elements.forEach((el, index) => {
        // If element is inside viewport already, animate directly, else use ScrollTrigger
        gsap.from(el, {
          autoAlpha: 0,
          y,
          duration,
          delay: delay + (index % 4) * stagger,
          ease,
          scrollTrigger: {
            trigger: el,
            start: 'top 92%',
            once,
          },
        })
      })
    }, containerRef.value)
  })

  onBeforeUnmount(() => {
    ctx?.revert()
  })

  return {
    revert: () => ctx?.revert(),
  }
}
