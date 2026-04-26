import type { CapacitorConfig } from '@capacitor/cli'

const config: CapacitorConfig = {
  appId: 'com.yourco.callcenter',
  appName: 'CallCenter Operator',
  webDir: 'dist',
  android: { allowMixedContent: true },
  server: {
    url: 'http://172.20.10.3:5173',
    cleartext: true,
    androidScheme: 'http',
    allowNavigation: ['172.20.10.3', 'localhost'],
  },
  plugins: {
    SplashScreen: { launchShowDuration: 800, backgroundColor: '#0f172a' },
  },
}
export default config
