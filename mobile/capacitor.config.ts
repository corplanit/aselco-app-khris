import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  // Must match google-services.json → client.android_client_info.package_name
  appId: 'aselco.demo.starter',
  appName: 'ASELCO Member',
  webDir: 'dist',
  server: {
    // WebView origin becomes https://localhost — allow that in Laravel CORS.
    androidScheme: 'https',
    // Allow HTTP API calls during local device/emulator testing (LAN IP).
    cleartext: true,
  },
  plugins: {
    PushNotifications: {
      presentationOptions: ['badge', 'sound', 'alert'],
    },
  },
};

export default config;
