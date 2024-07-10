import { defineConfig } from 'vitepress'

export default defineConfig({
  title: 'Guilherme Caraciolo',
  description: 'Reasoning about Laravel, PHP, Vue.js, JavaScript and sharing all along. Guilherme Caraciolo.',
  cleanUrls: true,
  head: [
    ['meta', { name: 'twitter:site', content: '@gcaraciolo' }],
    ['meta', { name: 'twitter:card', content: 'summary' }],
    [
      'meta',
      {
        name: 'twitter:image',
        content: 'https://vuejs.org/images/logo.png'
      }
    ],
    [
      'link',
      {
        rel: 'icon',
        type: 'image/x-icon',
        href: '/favicon.ico'
      }
    ],
    // [
    //   'script',
    //   {
    //     src: 'https://cdn.usefathom.com/script.js',
    //     'data-site': '',
    //     'data-spa': 'auto',
    //     defer: ''
    //   }
    // ]
  ],
})