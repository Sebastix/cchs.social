module.exports = {
  content: [
    "**/*.twig",
  ],
  theme: {
    container: {
      center: true,
    },
    extend: {},
  },
  variants: {
    extend: {},
  },
  plugins: [
    require('daisyui')
  ],
  daisyui: {
    themes: ["lofi"]
  }
}
