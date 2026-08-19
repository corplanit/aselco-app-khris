import type { AccountLink, LinkedAccount, LedgerConsumer, LedgerEntry, ServiceInfo } from '../api/types';

export function displayOrDash(value?: string | null): string {
  const trimmed = value?.trim();
  return trimmed ? trimmed : '—';
}

export function formatLinkStatus(status?: string | null): string {
  if (!status) {
    return 'Unknown';
  }
  const normalized = status.toLowerCase();
  if (normalized === 'pending') {
    return 'Pending';
  }
  if (normalized === 'validated') {
    return 'Validated';
  }
  if (normalized === 'linked') {
    return 'Linked';
  }
  return status;
}

export interface MemberAccountOption {
  accountNumber: string;
  ownerName: string | null;
  status: string | null;
}

/** Unique linked / submitted accounts — same source for Home and Ledger. */
export function listMemberAccounts(
  linkedAccounts: LinkedAccount[],
  links: AccountLink[],
): MemberAccountOption[] {
  const byNumber = new Map<string, MemberAccountOption>();
  const accounts = Array.isArray(linkedAccounts) ? linkedAccounts : [];
  const accountLinks = Array.isArray(links) ? links : [];

  for (const item of accounts) {
    const accountNumber = item.account_no?.trim();
    if (!accountNumber) {
      continue;
    }
    byNumber.set(accountNumber, {
      accountNumber,
      ownerName: item.customer,
      status: item.status,
    });
  }

  for (const link of accountLinks) {
    const accountNumber = link.account_number?.trim();
    if (!accountNumber) {
      continue;
    }
    const existing = byNumber.get(accountNumber);
    if (!existing) {
      byNumber.set(accountNumber, {
        accountNumber,
        ownerName: link.owner_name,
        status: link.status,
      });
      continue;
    }
    if (!existing.ownerName && link.owner_name) {
      existing.ownerName = link.owner_name;
    }
    if (!existing.status && link.status) {
      existing.status = link.status;
    }
  }

  return Array.from(byNumber.values());
}

export function resolveServiceInfo(
  linkedAccounts: LinkedAccount[],
  links: AccountLink[],
  consumer?: LedgerConsumer | null,
): ServiceInfo | null {
  const linked = linkedAccounts[0];
  if (linked) {
    return enrichServiceInfo(
      {
        account_number: linked.account_no,
        owner_name: linked.customer,
        status: linked.status || 'linked',
        meter_no: linked.meter_no ?? null,
        address: linked.address ?? null,
        rate_class: linked.rate_class ?? null,
        source: 'linked_account',
      },
      consumer,
    );
  }

  const link = links.find((item) => item.status === 'validated') ?? links[0];
  if (!link) {
    return null;
  }

  return enrichServiceInfo(
    {
      account_number: link.account_number,
      owner_name: link.owner_name,
      status: link.status,
      meter_no: null,
      address: null,
      rate_class: null,
      source: 'account_link',
    },
    consumer,
  );
}

/** Fill null meter/address/rate from ledger consumer payload when available. */
export function enrichServiceInfo(
  service: ServiceInfo,
  consumer?: LedgerConsumer | null,
): ServiceInfo {
  if (!consumer) {
    return service;
  }
  const sameAccount =
    !consumer.account_number ||
    consumer.account_number.trim() === service.account_number.trim();
  if (!sameAccount) {
    return service;
  }

  return {
    ...service,
    owner_name: service.owner_name ?? consumer.name ?? null,
    address: service.address ?? consumer.address ?? null,
    meter_no: service.meter_no ?? consumer.meter_no ?? null,
    rate_class: service.rate_class ?? consumer.rate_class ?? null,
    status: service.status || consumer.status || service.status,
  };
}

export function latestBillReading(entries: LedgerEntry[]): LedgerEntry | null {
  for (const entry of entries) {
    if (entry.type !== 'bill') {
      continue;
    }
    if (
      entry.previous_reading != null ||
      entry.present_reading != null ||
      entry.kwh != null
    ) {
      return entry;
    }
  }
  return entries.find((entry) => entry.type === 'bill') ?? null;
}
