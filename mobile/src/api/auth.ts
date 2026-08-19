import { apiRequest } from './client';
import type {
  AuthUser,
  LoginPayload,
  LoginResponse,
  RegisterPayload,
  RegisterResponse,
} from './types';

export function register(payload: RegisterPayload): Promise<RegisterResponse> {
  return apiRequest<RegisterResponse>('/auth/register', {
    method: 'POST',
    body: JSON.stringify(payload),
  });
}

export function login(payload: LoginPayload): Promise<LoginResponse> {
  return apiRequest<LoginResponse>('/auth/login', {
    method: 'POST',
    body: JSON.stringify({
      ...payload,
      device_name: payload.device_name ?? 'aselco-mobile',
    }),
  });
}

export function logout(token: string): Promise<{ message: string }> {
  return apiRequest<{ message: string }>('/auth/logout', {
    method: 'POST',
    token,
  });
}

export function logoutAll(token: string): Promise<{ message: string }> {
  return apiRequest<{ message: string }>('/auth/logout-all', {
    method: 'POST',
    token,
  });
}

export function me(token: string): Promise<AuthUser> {
  return apiRequest<AuthUser>('/auth/user', {
    method: 'GET',
    token,
  });
}

export function resendVerification(email: string): Promise<{ message: string }> {
  return apiRequest<{ message: string }>('/auth/email/resend', {
    method: 'POST',
    body: JSON.stringify({ email }),
  });
}
