/** @type {import('tailwindcss').Config} */
export default {
  content: ["./src/**/*.{html,js}","./index.html","./main.js"],
  theme: {
    fontFamily: {
      sans: "'Poppins', Arial, Helvetica, sans-serif"
    },
    fontSize: {
      'h1-large': ['4rem',{
        lineHeight: '1.0625em',
        fontWeight: '600',
      }],
      'h1-small': ['2.75rem',{
        lineHeight: '1.0625em',
        fontWeight: '600',
      }],
      'h2-large': ['2.5rem',{
        lineHeight: '1.1em',
        fontWeight: '600',
      }],
      'h2-small': ['2.25rem',{
        lineHeight: '1.1em',
        fontWeight: '600',
      }],
      'h3': ['1.5rem',{
        lineHeight: '2rem',
        fontWeight: '600',
      }],
      'h4': ['1rem',{
        lineHeight: '1.25rem',
        fontWeight: '500',
        letterSpacing: 'calc(0.1/16 * 1em)'
      }],
      'h5': ['0.8125rem',{
        lineHeight: '1em',
        fontWeight: '600',
        letterSpacing: '0.04em'
      }],
      'h6': ['0.75rem',{
        lineHeight: '1rem',
        fontWeight: '600',
        letterSpacing: 'calc(0.6/16 * 1rem)'
      }],
      'button-sm': ['0.875rem',{
        lineHeight: '1rem',
        letterSpacing: 'calc(0.1/16 * 1rem)',
        fontWeight: '500'
      }],
      'button-lg': ['1.0625rem',{
        lineHeight: 'calc(20/17 * 1em)',
        letterSpacing: 'calc(0.1/17 * 1em)',
        fontWeight: '500'
      }],
      'body': ['1rem',{
        lineHeight: '1.5em',
        fontWeight: '400',
        letterSpacing: 'calc(0.1/16 * 1em)'
      }],
      'accordion-trigger': ['1.125rem',{
        lineHeight: 'calc(20/18 * 1em)',
        fontWeight: '500'
      }],
      'stats-label-small': ['1.25rem',{
        lineHeight: '1.2em',
        fontWeight: '500'
      }],
      'filter-trigger-label': ['1rem',{
        lineHeight: '1.25em',
        fontWeight: '500'
      }],
      'filter-trigger-meta': ['.875rem',{
        lineHeight: 'calc(16/14 * 1em)',
        fontWeight: '500'
      }],
      'chip-count-large': ['.875rem',{
        lineHeight: 'calc(20/14 * 1em)',
        fontWeight: '600'
      }],
      'collaborators-cap': ['3rem',{
        lineHeight: '1em',
        fontWeight: '400'
      }],
      'filter-count': ['0.875rem',{
        lineHeight: '1.25rem',
        fontWeight: '600',
        letterSpacing: '0'
      }],
      'filter-label': ['1rem',{
        lineHeight: '1.25rem',
        fontWeight: '500',
        letterSpacing: '0'
      }],
      'filter-preview': ['0.875rem',{
        lineHeight: '1rem',
        fontWeight: '500',
        letterSpacing: 'calc(0.01/14 * 1em)'
      }],
      'category-chip': ['0.75rem',{
        fontWeight: 500,
        lineHeight: '1rem',
        letterSpacing: 'calc(0.01/16 * 1rem)'
      }],
      'member-tab': ['1rem', {
        lineHeight: '1.25rem',
        fontWeight: '500',
        letterSpacing: 'calc(0.1/16 * 1rem)'
      }],
      'count': ['0.75rem',{
        lineHeight: '0.875rem',
        fontWeight: '600',
        letterSpacing: '0'
      }],
      'member-meta': ['0.9375rem',{
        lineHeight: '1rem',
        letterSpacing: 'calc(0.1/16 * 1rem)',
        fontWeight: '400'
      }],
      'achievement': ['1.25rem',{
        fontWeight: '400',
        lineHeight: '1.5rem',
        letterSpacing: 'calc(0.1/16 * 1rem)'
      }]
    },
    colors: {
      transparent: 'transparent',
      white: 'hsl(var(--white))',
      black: 'hsl(var(--black))',
      neutral: {
        '50': 'hsl(var(--neutral-50))',
        '100': 'hsl(var(--neutral-100))',
        '200': 'hsl(var(--neutral-200))',
        '300': 'hsl(var(--neutral-300))',
        '400': 'hsl(var(--neutral-400))',
        '500': 'hsl(var(--neutral-500))',
        '600': 'hsl(var(--neutral-600))',
        '700': 'hsl(var(--neutral-700))',
        '800': 'hsl(var(--neutral-800))',
        '900': 'hsl(var(--neutral-900))',
      },
      green: {
        '50': 'hsl(var(--green-50))',
        '100': 'hsl(var(--green-100))',
        '200': 'hsl(var(--green-200))',
        '300': 'hsl(var(--green-300))',
        '400': 'hsl(var(--green-400))',
        '500': 'hsl(var(--green-500))',
        '600': 'hsl(var(--green-600))',
        '700': 'hsl(var(--green-700))',
        '800': 'hsl(var(--green-800))',
        '900': 'hsl(var(--green-900))',
      },
      apricot: {
        '50': 'hsl(var(--apricot-50))',
        '100': 'hsl(var(--apricot-100))',
        '200': 'hsl(var(--apricot-200))',
        '300': 'hsl(var(--apricot-300))',
        '400': 'hsl(var(--apricot-400))',
        '500': 'hsl(var(--apricot-500))',
        '600': 'hsl(var(--apricot-600))',
        '700': 'hsl(var(--apricot-700))',
        '800': 'hsl(var(--apricot-800))',
        '900': 'hsl(var(--apricot-900))',
      },
      'smoky-blue': {
        '50': 'hsl(var(--smoky-blue-50))',
        '100': 'hsl(var(--smoky-blue-100))',
        '200': 'hsl(var(--smoky-blue-200))',
        '300': 'hsl(var(--smoky-blue-300))',
        '400': 'hsl(var(--smoky-blue-400))',
        '500': 'hsl(var(--smoky-blue-500))',
        '600': 'hsl(var(--smoky-blue-600))',
        '700': 'hsl(var(--smoky-blue-700))',
        '800': 'hsl(var(--smoky-blue-800))',
        '900': 'hsl(var(--smoky-blue-900))',
      },
      text: {
        DEFAULT: 'hsl(var(--text-default))',
        subtle: 'hsl(var(--text-subtle))',
      },
      primary: {
        DEFAULT: 'hsl(var(--green-500))'
      },
      border: 'hsl(var(--neutral-100))'
    },
    spacing: {
      '0': '0',
      '1': '0.125rem',
      '2': '0.25rem',
      '3': '0.375rem',
      '4': '0.5rem',
      '5': '0.625rem',
      '6': '0.75rem',
      '7': '0.875rem',
      '10': '1.25rem',
      '12': '1.5rem',
      '14': '1.75rem',
      '16': '2rem',
      '26': '3.25rem',
      '30': '3.75rem',
      '42': '5.25rem',
      '68': '8.5rem',
      '110': '13.75rem',
      '135': '16.875rem',
      '178': '22.25rem',
    },
    borderRadius: {
      '0': '0',
      'xs': '0.125rem',
      's': '0.25rem',
      'm': '0.5rem',
      'l': '0.375rem',
      'xl': '0.75rem',
      'xxl': '1.25rem',
      'pill': '99999px'
    },
    borderWidth: {
      DEFAULT: '0.0625rem',
      '1': '0.0625rem',
      '2': '0.125rem',
    },
    extend: {
      height: {
        'checkbox-inner': 'calc(100% - 0.125em)',
        'coalition-map': 'calc(100vh - 17rem)',
        'map-thumbnail': '5.25rem',
        'touch-map': 'var(--touch-map-h)',
        'unset': 'unset'
      },
      aspectRatio: {
        map: '1224/660',
        none: 'unset',
        square: '1/1',
        'hero-sm': '360/520',
        'hero-lg': '836/690',
        'intro-sm': '360/325',
        'intro-lg': '830/544',
        'coalition-map': '1124/706',
        'category-thumb': '264/174',
        'blog-thumb': '413/316',
        'map-window': '920/820',
        'map-focus-thumb-sm': '14/9',
        'map-focus-thumb-lg': '684/346',
        'project-thumb-sm': '14/9',
        'project-thumb-lg': '614/330'
      },
      gridTemplateColumns: {
        'map': '1fr 2fr'
      },
      width: {
        'checkbox-inner': 'calc(100% - 0.125em)',
        'partner-card-logo': '8.125rem',
        'map-thumbnail': '5.25rem',
        'member-focus-logo': '5.875rem',
        'count': '1.375rem'
      },
      maxWidth: {
        'text': 'var(--w-text)',
        'content': 'var(--w-content)',
        'map-thumbnail': '5.25rem',
        'fellow-thumbnail': 'var(--fellow-thumbnail)',
        'unset': 'unset',
        'none': 'unset',
        'screen': '100vw',
        'intro-stats': '29.625rem',
        'popup': 'calc(100vw - 2.5rem)'
      },
      minHeight: {
        'map-thumbnail': '5.25rem'
      },
      maxHeight: {
        'map-thumbnail': '5.25rem',
        'tabpanel': 'var(--tabpanel-h)',
        'touch-map': 'var(--touch-map-h)',
      },
    },
  },
  plugins: [],
}

