/* eslint-env node */
require('@rushstack/eslint-patch/modern-module-resolution')

module.exports = {
  root: true,
  extends: ['plugin:vue/vue3-essential', 'eslint:recommended', '@vue/eslint-config-prettier'],
  parserOptions: {
    ecmaVersion: 'latest'
  },
  rules: {
    // Vue specific rules
    'vue/no-unused-vars': 'error',
    'vue/no-unused-components': 'warn',
    'vue/component-name-in-template-casing': ['error', 'PascalCase'],
    'vue/require-default-prop': 'off',
    'vue/multi-word-component-names': 'warn',

    // JavaScript rules
    'no-console': ['error', { allow: ['error', 'warn'] }], // Only allow console.error and console.warn
    'no-debugger': 'error',
    'no-unused-vars': 'warn',
    'prefer-const': 'error',
    'no-var': 'error',
    'no-warning-comments': [
      'warn',
      {
        // Warn about TODO/FIXME comments
        terms: ['TODO', 'FIXME', 'XXX', 'HACK', 'BUG'],
        location: 'anywhere'
      }
    ],

    // Code style
    semi: ['error', 'never'],
    quotes: ['error', 'single'],
    'comma-dangle': ['error', 'never'],
    'object-curly-spacing': ['error', 'always'],
    'array-bracket-spacing': ['error', 'never'],
    'space-before-function-paren': ['error', 'never'],
    indent: ['error', 2],
    'max-len': ['warn', { code: 120 }]
  },
  env: {
    'vue/setup-compiler-macros': true,
    node: true,
    browser: true
  }
}
