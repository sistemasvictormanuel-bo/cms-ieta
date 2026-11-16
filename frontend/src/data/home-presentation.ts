import heroLab from '@/assets/images/home/hero-1.jpeg'

export const homePresentation = {
  hero: {
    title: 'Estadística que transforma realidades',
    description:
      'Investigación, formación e interacción social para comprender los datos y aportar al desarrollo de nuestra sociedad.',
    actionLabel: 'Conoce el IETA',
    actionTo: '/acerca-de',
    image: heroLab,
    imageAlt: 'Espacio de trabajo y análisis estadístico',
  },
  quickLinks: [
    { label: 'Líneas de investigación', to: '/investigacion/lineas-investigacion', number: '01' },
    { label: 'Revista Varianza', to: '/revista-varianza', number: '02' },
    { label: 'Servicios estadísticos', to: '/servicios', number: '03' },
    { label: 'Banco de datos', to: '/recursos', number: '04' },
  ],
}
