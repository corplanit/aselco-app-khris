import {
  IonButton,
  IonContent,
  IonIcon,
  IonList,
  IonModal,
  IonPage,
  IonRefresher,
  IonRefresherContent,
  IonSpinner,
  useIonToast,
  useIonViewWillEnter,
} from '@ionic/react';
import {
  cashOutline,
  chevronBackOutline,
  chevronForwardOutline,
  documentTextOutline,
  flashOutline,
  refreshOutline,
} from 'ionicons/icons';
import { useCallback, useEffect, useMemo, useState } from 'react';
import { getLedger } from '../api/ledger';
import {
  formatSavedAt,
  getCachedLedger,
  LEDGER_PAGE_SIZE,
  saveLedgerCache,
  sliceLedger,
} from '../api/ledgerStorage';
import { ApiError, type LedgerEntry, type LedgerResponse } from '../api/types';
import { useAuth } from '../auth/AuthContext';
import AppHeader from '../components/AppHeader';
import EmptyState from '../components/EmptyState';
import FilterChips from '../components/FilterChips';
import ListRow from '../components/ListRow';
import { billing as mockBilling, ledger as mockLedger, ledgerSummary as mockSummary } from '../data/mockData';
import { useMembership } from '../membership/MembershipContext';
import { peso } from '../utils/format';
import { displayOrDash, formatLinkStatus, listMemberAccounts } from '../utils/serviceAccount';

const FILTERS = ['All', 'Bills', 'Payments'];

function filterType(filter: string): 'all' | 'bill' | 'payment' {
  if (filter === 'Bills') {
    return 'bill';
  }
  if (filter === 'Payments') {
    return 'payment';
  }
  return 'all';
}

function mockSnapshot(): LedgerResponse {
  return {
    account: {
      account_number: mockLedger[0]?.ref ?? '—',
      consumer_name: null,
      consumer_address: null,
      consumer_status: null,
    },
    accounts: [],
    summary: {
      account_number: '',
      current_balance: mockSummary.currentDue,
      current_due: mockSummary.currentDue,
      total_paid: mockSummary.totalPaid,
      kwh_used: mockBilling.kwhUsed,
      billing_period: mockBilling.billingPeriod,
      due_date: mockBilling.dueDate,
      pending_count: 1,
    },
    history: [],
    entries: mockLedger.map((item) => ({
      id: item.id,
      type: item.type,
      title: item.title,
      date: item.date,
      posted_at: item.date,
      ref: item.ref,
      amount: item.amount,
      debit: item.type === 'bill' ? item.amount : 0,
      credit: item.type === 'payment' ? item.amount : 0,
      kwh: item.kwh ?? null,
      due_date: item.type === 'bill' ? mockBilling.dueDate : null,
    })),
    sort: 'latest',
  };
}

function readingLabel(item: LedgerEntry): string {
  if (item.previous_reading == null && item.present_reading == null) {
    return '—';
  }
  return `${displayOrDash(item.previous_reading != null ? String(item.previous_reading) : null)} → ${displayOrDash(item.present_reading != null ? String(item.present_reading) : null)}`;
}

const Ledger: React.FC = () => {
  const { token, user } = useAuth();
  const { linkedAccounts, links } = useMembership();
  const [filter, setFilter] = useState('All');
  const [page, setPage] = useState(1);
  const [accountNumber, setAccountNumber] = useState<string | undefined>(undefined);
  const [snapshot, setSnapshot] = useState<LedgerResponse | null>(null);
  const [savedAt, setSavedAt] = useState<string | null>(null);
  const [refreshing, setRefreshing] = useState(false);
  const [usingDemo, setUsingDemo] = useState(false);
  const [selected, setSelected] = useState<LedgerEntry | null>(null);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [present] = useIonToast();

  const accountOptions = useMemo(
    () => listMemberAccounts(linkedAccounts, links),
    [linkedAccounts, links],
  );

  const hydrateFromCache = useCallback(
    (acct?: string) => {
      if (!user) {
        return false;
      }
      const cached = getCachedLedger(user.id, acct);
      if (!cached) {
        return false;
      }
      setSnapshot(cached.data);
      setSavedAt(cached.savedAt);
      setUsingDemo(false);
      return true;
    },
    [user],
  );

  const refreshFromApi = useCallback(async (acct?: string) => {
    if (!token || !user) {
      setSnapshot(mockSnapshot());
      setUsingDemo(true);
      return;
    }
    const target = acct ?? accountNumber;
    setRefreshing(true);
    setLoadError(null);
    try {
      const next = await getLedger(token, {
        accountNumber: target,
        snapshot: true,
        sort: 'latest',
      });
      saveLedgerCache(user.id, next, target);
      setSnapshot(next);
      setSavedAt(new Date().toISOString());
      setUsingDemo(false);
      setPage(1);
    } catch (err) {
      const message =
        err instanceof ApiError
          ? err.message
          : 'Could not refresh the ledger. Check your connection.';
      const usedCache = hydrateFromCache(target);
      if (usedCache) {
        setLoadError(message);
        void present({ message: `${message} Showing saved ledger.`, duration: 2400, color: 'warning' });
      } else if (err instanceof ApiError && err.status > 0) {
        setSnapshot(null);
        setUsingDemo(false);
        setLoadError(message);
        void present({ message, duration: 2800, color: 'danger' });
      } else {
        // Network / offline with no cache — keep demo only when completely unavailable.
        setSnapshot(mockSnapshot());
        setUsingDemo(true);
        setLoadError(message);
        void present({ message: `${message} Showing demo data.`, duration: 2400, color: 'warning' });
      }
    } finally {
      setRefreshing(false);
    }
  }, [token, user, accountNumber, hydrateFromCache, present]);

  useEffect(() => {
    if (!user || !token) {
      return;
    }
    if (hydrateFromCache(accountNumber)) {
      return;
    }
    void refreshFromApi(accountNumber);
    // First cache miss only — later visits use localStorage until Refresh.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [user?.id, token]);

  useIonViewWillEnter(() => {
    hydrateFromCache(accountNumber);
  }, [hydrateFromCache, accountNumber]);

  const data = snapshot;
  const selectedAccount =
    accountNumber ?? data?.account.account_number ?? accountOptions[0]?.accountNumber;
  const sliced = data
    ? sliceLedger(data, filterType(filter), page, LEDGER_PAGE_SIZE)
    : { entries: [], pagination: { page: 1, per_page: LEDGER_PAGE_SIZE, total: 0, last_page: 1, from: 0, to: 0 } };
  const pagination = sliced.pagination;

  const rowMeta = (item: LedgerEntry): string => {
    const bits = [item.date, item.ref];
    if (item.type === 'bill' && item.due_date) {
      bits.push(`Due ${item.due_date}`);
    }
    if (item.kwh) {
      bits.push(`${item.kwh} kWh`);
    }
    return bits.filter(Boolean).join(' · ');
  };

  const onFilterChange = (value: string) => {
    setFilter(value);
    setPage(1);
  };

  const onAccountChange = (acct: string) => {
    setAccountNumber(acct);
    setPage(1);
    const hit = hydrateFromCache(acct);
    if (!hit) {
      setSnapshot(null);
      setSavedAt(null);
      void refreshFromApi(acct);
    }
  };

  return (
    <IonPage>
      <AppHeader title="My Ledger" icon={documentTextOutline} />
      <IonContent>
        <IonRefresher
          slot="fixed"
          onIonRefresh={async (event) => {
            await refreshFromApi();
            event.detail.complete();
          }}
        >
          <IonRefresherContent />
        </IonRefresher>

        <div className="page-pad">
          {loadError ? (
            <p className="ledger-load-error" role="status">
              {loadError}
            </p>
          ) : null}
          <div className="ledger-due-banner">
            <div>
              <span className="summary-strip__label">Current balance</span>
              <span className="summary-strip__value">{data ? peso(data.summary.current_balance) : '—'}</span>
            </div>
            <div>
              <span className="summary-strip__label">Due date</span>
              <span className="summary-strip__value">{data?.summary.due_date ?? '—'}</span>
            </div>
          </div>

          <div className="summary-strip">
            <div className="summary-strip__item">
              <span className="summary-strip__label">Current due</span>
              <span className="summary-strip__value">{data ? peso(data.summary.current_due) : '—'}</span>
            </div>
            <div className="summary-strip__item">
              <span className="summary-strip__label">Paid YTD</span>
              <span className="summary-strip__value">{data ? peso(data.summary.total_paid) : '—'}</span>
            </div>
            <div className="summary-strip__item">
              <span className="summary-strip__label">kWh used</span>
              <span className="summary-strip__value">{data?.summary.kwh_used || '—'}</span>
            </div>
          </div>

          {accountOptions.length > 0 ? (
            <section className="ledger-account-picker" aria-label="Select account">
              <p className="ledger-account-picker__label">
                {accountOptions.length > 1 ? 'Select account' : 'Linked account'}
              </p>
              <div className="ledger-account-picker__list" role="listbox">
                {accountOptions.map((option) => {
                  const isActive = option.accountNumber === selectedAccount;
                  const meta = [
                    option.ownerName,
                    option.status ? formatLinkStatus(option.status) : null,
                  ]
                    .filter(Boolean)
                    .join(' · ');

                  return (
                    <button
                      key={option.accountNumber}
                      type="button"
                      role="option"
                      aria-selected={isActive}
                      className={`soft-card-btn ledger-account-card${isActive ? ' is-selected' : ''}`}
                      onClick={() => onAccountChange(option.accountNumber)}
                    >
                      <span className="soft-card-btn__icon">
                        <IonIcon icon={flashOutline} />
                      </span>
                      <span className="soft-card-btn__body">
                        <span className="soft-card-btn__title">{option.accountNumber}</span>
                        <span className="soft-card-btn__meta">{meta || 'Electric account'}</span>
                      </span>
                      <span className="soft-card-btn__check" aria-hidden />
                    </button>
                  );
                })}
              </div>
            </section>
          ) : null}

          <div className="ledger-toolbar">
            <p className="ledger-period">
              {usingDemo
                ? 'Showing demo ledger'
                : savedAt
                  ? `Saved locally · ${formatSavedAt(savedAt)}`
                  : 'Tap Refresh to load the latest ledger'}
            </p>
            <IonButton
              fill="outline"
              className="soft-btn ledger-icon-btn"
              disabled={refreshing}
              aria-label="Refresh ledger"
              onClick={() => void refreshFromApi()}
            >
              {refreshing ? (
                <IonSpinner name="crescent" />
              ) : (
                <IonIcon slot="icon-only" icon={refreshOutline} />
              )}
            </IonButton>
          </div>

          <FilterChips options={FILTERS} value={filter} onChange={onFilterChange} />

          {refreshing && !snapshot ? (
            <div className="ledger-loading">
              <IonSpinner name="crescent" color="primary" />
            </div>
          ) : sliced.entries.length === 0 ? (
            <EmptyState
              title={snapshot ? 'No transactions' : 'Ledger not loaded'}
              message={
                snapshot
                  ? 'Try another filter, or pull down to refresh.'
                  : 'Tap Refresh ledger (or pull down) to download and save it on this device.'
              }
            />
          ) : (
            <IonList className="soft-list">
              {sliced.entries.map((item) => (
                <ListRow
                  key={item.id}
                  icon={item.type === 'payment' ? cashOutline : documentTextOutline}
                  title={item.title}
                  meta={rowMeta(item)}
                  value={peso(item.amount)}
                  tone={item.type === 'payment' ? 'in' : 'due'}
                  detail
                  onClick={() => setSelected(item)}
                />
              ))}
            </IonList>
          )}

          {pagination.total > 0 ? (
            <div className="ledger-pager">
              <p className="ledger-pager__meta">
                {pagination.from}–{pagination.to} of {pagination.total}
              </p>
              <div className="ledger-pager__btns">
                <IonButton
                  fill="outline"
                  className="soft-btn ledger-icon-btn"
                  disabled={pagination.page <= 1}
                  aria-label="Previous page"
                  onClick={() => setPage((current) => Math.max(1, current - 1))}
                >
                  <IonIcon slot="icon-only" icon={chevronBackOutline} />
                </IonButton>
                <IonButton
                  fill="outline"
                  className="soft-btn ledger-icon-btn"
                  disabled={pagination.page >= pagination.last_page}
                  aria-label="Next page"
                  onClick={() => setPage((current) => current + 1)}
                >
                  <IonIcon slot="icon-only" icon={chevronForwardOutline} />
                </IonButton>
              </div>
            </div>
          ) : null}
        </div>
      </IonContent>

      <IonModal
        isOpen={selected !== null}
        onDidDismiss={() => setSelected(null)}
        className="ledger-detail-modal"
      >
        {selected ? (
          <div className="ledger-modal">
            <p className="ledger-modal__kicker">{selected.type === 'payment' ? 'Payment' : 'Billing'}</p>
            <h2 className="ledger-modal__title font-display">{selected.title}</h2>
            <p className="ledger-modal__sub">{selected.date}</p>

            <div className="kv">
              <span className="kv__k">Reference</span>
              <span className="kv__v">{displayOrDash(selected.ref)}</span>
            </div>
            <div className="kv">
              <span className="kv__k">Debit</span>
              <span className="kv__v">{peso(selected.debit ?? (selected.type === 'bill' ? selected.amount : 0))}</span>
            </div>
            <div className="kv">
              <span className="kv__k">Credit</span>
              <span className="kv__v">{peso(selected.credit ?? (selected.type === 'payment' ? selected.amount : 0))}</span>
            </div>
            <div className="kv">
              <span className="kv__k">Running balance</span>
              <span className="kv__v">{selected.balance != null ? peso(selected.balance) : '—'}</span>
            </div>
            <div className="kv">
              <span className="kv__k">Prev / present</span>
              <span className="kv__v">{readingLabel(selected)}</span>
            </div>
            <div className="kv">
              <span className="kv__k">kWh used</span>
              <span className="kv__v">{selected.kwh != null ? String(selected.kwh) : '—'}</span>
            </div>
            <div className="kv">
              <span className="kv__k">Demand kW</span>
              <span className="kv__v">{selected.demand_kw != null ? String(selected.demand_kw) : '—'}</span>
            </div>
            <div className="kv">
              <span className="kv__k">Bill month</span>
              <span className="kv__v">{displayOrDash(selected.bill_month)}</span>
            </div>
            <div className="kv">
              <span className="kv__k">Due date</span>
              <span className="kv__v">{displayOrDash(selected.due_date)}</span>
            </div>

            <IonButton expand="block" className="soft-btn ion-margin-top" onClick={() => setSelected(null)}>
              Close
            </IonButton>
          </div>
        ) : null}
      </IonModal>
    </IonPage>
  );
};

export default Ledger;
