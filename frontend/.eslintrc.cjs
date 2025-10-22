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
    'no-console': ['warn', { allow: ['error', 'warn'] }], // Warn about console (not error) for now
    'no-debugger': 'error',
    'no-unused-vars': 'warn',
    'prefer-const': 'error',
    'no-var': 'error',
    'no-warning-comments': 'off', // Disable TODO/FIXME warnings

    // Code style
    semi: ['error', 'never'],
    quotes: ['warn', 'single'], // Downgrade to warning
    'comma-dangle': ['error', 'never'],
    'object-curly-spacing': ['error', 'always'],
    'array-bracket-spacing': ['error', 'never'],
    'space-before-function-paren': 'off', // Disable - conflicts with Prettier
    indent: ['error', 2],
    'max-len': 'off', // Disable line length checks

    // Vue specific indentation
    'vue/script-indent': [
      'error',
      2,
      {
        baseIndent: 1,
        switchCase: 1 // Allow proper switch/case indentation
      }
    ],
    'vue/html-indent': ['error', 2]
  },
  env: {
    'vue/setup-compiler-macros': true,
    node: true,
    browser: true
  },
  overrides: [
    {
      files: ['*.vue'],
      rules: {
        // Disable base indent rule for .vue files in favor of vue/script-indent
        indent: 'off'
      }
    },
    {
      // Allow console statements in development utility files
      files: ['**/utils/debug.js', '**/utils/storageCleanup.js'],
      rules: {
        'no-console': 'off'
      }
    },
    {
      // Disable indent rule for files with complex switch statements
      files: [
        '**/composables/useToast.js',
        '**/services/optimizedApiService.js',
        '**/utils/formatters.js'
      ],
      rules: {
        indent: 'off'
      }
    }
  ]
}
