import { apiRequest } from './client';
import type { DashboardSummary } from './types';

export function getDashboardSummary(token: string): Promise<DashboardSummary> {
  return apiRequest<DashboardSummary>('/dashboard/summary', { token });
}
