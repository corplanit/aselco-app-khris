import { apiRequest } from './client';
import type {
  NotificationPreferences,
  NotificationsListResponse,
} from './types';

export function listNotifications(
  token: string,
  perPage = 30,
): Promise<NotificationsListResponse> {
  return apiRequest<NotificationsListResponse>(`/notifications?per_page=${perPage}`, {
    token,
  });
}

export function markNotificationRead(token: string, id: number): Promise<unknown> {
  return apiRequest(`/notifications/${id}/read`, {
    method: 'POST',
    token,
  });
}

export function markAllNotificationsRead(token: string): Promise<unknown> {
  return apiRequest('/notifications/read-all', {
    method: 'POST',
    token,
  });
}

export function getNotificationPreferences(token: string): Promise<NotificationPreferences> {
  return apiRequest<NotificationPreferences>('/notification-preferences', { token });
}

export function updateNotificationPreferences(
  token: string,
  prefs: Partial<NotificationPreferences>,
): Promise<NotificationPreferences> {
  return apiRequest<NotificationPreferences>('/notification-preferences', {
    method: 'PUT',
    token,
    body: JSON.stringify(prefs),
  });
}

export function registerDeviceToken(
  token: string,
  deviceToken: string,
  platform?: string,
): Promise<unknown> {
  return apiRequest('/devices', {
    method: 'POST',
    token,
    body: JSON.stringify({
      token: deviceToken,
      platform: platform ?? null,
    }),
  });
}

export function unregisterDeviceToken(token: string, deviceToken: string): Promise<unknown> {
  return apiRequest('/devices', {
    method: 'DELETE',
    token,
    body: JSON.stringify({ token: deviceToken }),
  });
}
