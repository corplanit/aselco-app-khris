import { apiRequest } from './client';
import type { LedgerResponse } from './types';

export interface GetLedgerParams {
  accountNumber?: string;
  page?: number;
  perPage?: number;
  sort?: 'latest' | 'oldest';
  type?: 'all' | 'bill' | 'payment';
  snapshot?: boolean;
}

export function getLedger(token: string, params: GetLedgerParams = {}): Promise<LedgerResponse> {
  const query = new URLSearchParams();
  if (params.accountNumber) {
    query.set('account_number', params.accountNumber);
  }
  if (params.snapshot) {
    query.set('snapshot', '1');
  } else {
    if (params.page) {
      query.set('page', String(params.page));
    }
    if (params.perPage) {
      query.set('per_page', String(params.perPage));
    }
    if (params.type && params.type !== 'all') {
      query.set('type', params.type);
    }
  }
  query.set('sort', params.sort ?? 'latest');
  const suffix = query.toString() ? `?${query.toString()}` : '';

  return apiRequest<LedgerResponse>(`/ledger${suffix}`, { token });
}
