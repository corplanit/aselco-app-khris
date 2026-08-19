import { apiRequest } from './client';
import type {
  AccountLink,
  LinkedAccount,
  MembershipPrivacy,
  MembershipStatus,
  StoreAccountLinkPayload,
  StoreAccountLinkResponse,
} from './types';

export function getMembershipStatus(token: string): Promise<MembershipStatus> {
  return apiRequest<MembershipStatus>('/membership/status', { token });
}

export function getMembershipPrivacy(token: string): Promise<MembershipPrivacy> {
  return apiRequest<MembershipPrivacy>('/membership/privacy', { token });
}

export function listAccountLinks(token: string): Promise<{ data: AccountLink[] }> {
  return apiRequest<{ data: AccountLink[] }>('/membership/account-links', { token });
}

export function submitAccountLink(
  token: string,
  payload: StoreAccountLinkPayload,
): Promise<StoreAccountLinkResponse> {
  return apiRequest<StoreAccountLinkResponse>('/membership/account-links', {
    method: 'POST',
    token,
    body: JSON.stringify(payload),
  });
}

export function listLinkedAccounts(token: string): Promise<{ data: LinkedAccount[] }> {
  return apiRequest<{ data: LinkedAccount[] }>('/membership/linked-accounts', { token });
}
