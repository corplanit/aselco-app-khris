import { Capacitor } from '@capacitor/core';
import { PushNotifications } from '@capacitor/push-notifications';
import { registerDeviceToken, unregisterDeviceToken } from '../api/notifications';

let registeredToken: string | null = null;
let pendingAuthToken: string | null = null;
let listenersAttached = false;
const refreshHandlers = new Set<() => void>();
let navigateHandler: ((path: string) => void) | null = null;

export function addPushRefreshHandler(handler: () => void): () => void {
  refreshHandlers.add(handler);
  return () => {
    refreshHandlers.delete(handler);
  };
}

export function setPushNavigateHandler(handler: ((path: string) => void) | null): void {
  navigateHandler = handler;
}

function notifyRefresh(): void {
  refreshHandlers.forEach((handler) => {
    try {
      handler();
    } catch {
      // Ignore handler errors.
    }
  });
}

function deepLinkFromNotification(data: Record<string, unknown> | undefined): string | null {
  if (!data) {
    return null;
  }
  const link = data.deep_link ?? data.deepLink ?? data.path;
  return typeof link === 'string' && link.startsWith('/') ? link : null;
}

async function syncTokenWithApi(deviceToken: string): Promise<void> {
  if (!pendingAuthToken) {
    return;
  }
  const platform = Capacitor.getPlatform();
  await registerDeviceToken(pendingAuthToken, deviceToken, platform);
}

async function ensureListeners(): Promise<void> {
  if (listenersAttached || !Capacitor.isNativePlatform()) {
    return;
  }
  listenersAttached = true;

  await PushNotifications.addListener('registration', async (event) => {
    registeredToken = event.value;
    try {
      await syncTokenWithApi(event.value);
    } catch (err) {
      console.warn('Failed to register device token with API', err);
    }
  });

  await PushNotifications.addListener('registrationError', (event) => {
    console.warn('Push registration error', event.error);
  });

  await PushNotifications.addListener('pushNotificationReceived', () => {
    notifyRefresh();
  });

  await PushNotifications.addListener('pushNotificationActionPerformed', (event) => {
    notifyRefresh();
    const path = deepLinkFromNotification(
      (event.notification.data ?? undefined) as Record<string, unknown> | undefined,
    );
    if (path) {
      navigateHandler?.(path);
    } else {
      navigateHandler?.('/notifications');
    }
  });
}

/**
 * Request permission and register the FCM/APNs token with the Laravel API.
 * No-ops on web (browser) — inbox API still works without push delivery.
 */
export async function startPushRegistration(authToken: string): Promise<void> {
  pendingAuthToken = authToken;

  if (!Capacitor.isNativePlatform()) {
    return;
  }

  await ensureListeners();

  const permission = await PushNotifications.requestPermissions();
  if (permission.receive !== 'granted') {
    return;
  }

  await PushNotifications.register();

  if (registeredToken) {
    try {
      await syncTokenWithApi(registeredToken);
    } catch (err) {
      console.warn('Failed to register device token with API', err);
    }
  }
}

export async function stopPushRegistration(authToken: string | null): Promise<void> {
  const tokenToRemove = registeredToken;
  pendingAuthToken = null;
  if (!tokenToRemove || !authToken) {
    registeredToken = null;
    return;
  }
  try {
    await unregisterDeviceToken(authToken, tokenToRemove);
  } catch {
    // Best-effort cleanup on sign-out.
  } finally {
    registeredToken = null;
  }
}

export function getRegisteredPushToken(): string | null {
  return registeredToken;
}
