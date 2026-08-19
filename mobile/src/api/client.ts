import { ApiError, type ApiErrorBody } from './types';

const baseUrl = (import.meta.env.VITE_API_BASE_URL ?? 'http://127.0.0.1:8000/api/v1').replace(/\/$/, '');

type RequestOptions = RequestInit & {
  token?: string | null;
};

export async function apiRequest<T>(path: string, options: RequestOptions = {}): Promise<T> {
  const { token, headers, ...rest } = options;
  const url = `${baseUrl}${path.startsWith('/') ? path : `/${path}`}`;

  const res = await fetch(url, {
    ...rest,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...headers,
    },
  });

  const data = (await res.json().catch(() => ({}))) as ApiErrorBody;

  if (!res.ok) {
    throw new ApiError(data.message ?? res.statusText, res.status, data);
  }

  return data as T;
}

export function getApiBaseUrl(): string {
  return baseUrl;
}
