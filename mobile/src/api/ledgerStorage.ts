import type { LedgerEntry, LedgerPagination, LedgerResponse } from './types';

const STORAGE_KEY = 'aselco_ledger_cache';
const PAGE_SIZE = 10;

export interface CachedLedger {
  savedAt: string;
  data: LedgerResponse;
}

interface LedgerCacheFile {
  userId: number | null;
  byAccount: Record<string, CachedLedger>;
}

function emptyFile(): LedgerCacheFile {
  return { userId: null, byAccount: {} };
}

function readFile(): LedgerCacheFile {
  try {
    const raw = window.localStorage.getItem(STORAGE_KEY);
    if (!raw) {
      return emptyFile();
    }
    const parsed = JSON.parse(raw) as LedgerCacheFile;
    if (!parsed || typeof parsed !== 'object') {
      return emptyFile();
    }
    return {
      userId: parsed.userId ?? null,
      byAccount: parsed.byAccount ?? {},
    };
  } catch {
    return emptyFile();
  }
}

function writeFile(file: LedgerCacheFile): void {
  try {
    window.localStorage.setItem(STORAGE_KEY, JSON.stringify(file));
  } catch {
    // Quota / private mode — ignore.
  }
}

export function normalizeAccountNumber(value?: string | null): string {
  return (value ?? '').trim();
}

function accountDigits(value?: string | null): string {
  return normalizeAccountNumber(value).replace(/\D/g, '');
}

function sameUser(file: LedgerCacheFile, userId: number): boolean {
  return Number(file.userId) === Number(userId);
}

function snapshotKeys(snapshot: CachedLedger, extra?: string | null): string[] {
  const raw = [extra, snapshot.data.summary?.account_number, snapshot.data.account?.account_number];
  const keys = raw.map((value) => normalizeAccountNumber(value)).filter(Boolean);
  const digits = raw.map((value) => accountDigits(value)).filter(Boolean);
  return Array.from(new Set([...keys, ...digits]));
}

function indexByAccount(byAccount: Record<string, CachedLedger>): Record<string, CachedLedger> {
  const indexed: Record<string, CachedLedger> = {};
  for (const [key, snapshot] of Object.entries(byAccount)) {
    for (const alias of snapshotKeys(snapshot, key)) {
      indexed[alias] = snapshot;
    }
  }
  return indexed;
}

export function listCachedLedgers(userId: number): Record<string, CachedLedger> {
  const file = readFile();
  if (!sameUser(file, userId)) {
    return {};
  }
  return indexByAccount(file.byAccount);
}

export function getCachedLedger(userId: number, accountNumber?: string): CachedLedger | null {
  const indexed = listCachedLedgers(userId);
  if (accountNumber) {
    return indexed[normalizeAccountNumber(accountNumber)] ?? null;
  }
  const first = Object.values(indexed)[0];
  return first ?? null;
}

export function saveLedgerCache(userId: number, data: LedgerResponse, accountNumber?: string): void {
  const snapshot: CachedLedger = {
    savedAt: new Date().toISOString(),
    data: {
      ...data,
      entries: data.entries ?? [],
    },
  };
  const keys = snapshotKeys(snapshot, accountNumber);
  if (keys.length === 0) {
    return;
  }
  const file = readFile();
  const next: LedgerCacheFile = sameUser(file, userId) ? file : { userId, byAccount: {} };
  next.userId = userId;
  for (const key of keys) {
    next.byAccount[key] = snapshot;
  }
  writeFile(next);
}

export function clearLedgerCache(): void {
  try {
    window.localStorage.removeItem(STORAGE_KEY);
  } catch {
    // ignore
  }
}

export function formatSavedAt(iso: string): string {
  const date = new Date(iso);
  if (Number.isNaN(date.getTime())) {
    return '';
  }
  return date.toLocaleString('en-PH', {
    month: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  });
}

export function sliceLedger(
  snapshot: LedgerResponse,
  filter: 'all' | 'bill' | 'payment',
  page: number,
  perPage = PAGE_SIZE,
): { entries: LedgerEntry[]; pagination: LedgerPagination } {
  let entries = [...(snapshot.entries ?? [])];
  if (filter === 'bill' || filter === 'payment') {
    entries = entries.filter((row) => row.type === filter);
  }
  const total = entries.length;
  const lastPage = Math.max(1, Math.ceil(total / perPage) || 1);
  const safePage = Math.min(Math.max(1, page), lastPage);
  const sliced = entries.slice((safePage - 1) * perPage, safePage * perPage);

  return {
    entries: sliced,
    pagination: {
      page: safePage,
      per_page: perPage,
      total,
      last_page: lastPage,
      from: total === 0 ? 0 : (safePage - 1) * perPage + 1,
      to: Math.min(total, safePage * perPage),
    },
  };
}

export const LEDGER_PAGE_SIZE = PAGE_SIZE;
