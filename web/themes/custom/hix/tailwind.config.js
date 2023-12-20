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
  safelist: [
    {
      pattern: /rotate-.+/,
    },
  ],
  variants: {
    extend: {},
  },
  plugins: [
    require('daisyui')
  ],
  daisyui: {
    themes: ["corporate"]
  }
}
