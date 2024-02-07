import {defineConfig} from 'vite'

export default defineConfig(({ command, mode, isSsrBuild, isPreview }) => {
    if (command === 'serve') {
      return {
        // dev specific config
      }
    } else {
      // command === 'build'
      return {
        build: {
          // build specific config
          sourcemap: true
        }
      }
    }
  })