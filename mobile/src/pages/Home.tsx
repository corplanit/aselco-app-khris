import { IonContent, IonList, IonPage, useIonRouter, useIonViewWillEnter } from '@ionic/react';
import {
  alertCircleOutline,
  checkmarkCircleOutline,
  documentTextOutline,
} from 'ionicons/icons';
import { useCallback, useEffect, useState } from 'react';
import { getDashboardSummary } from '../api/dashboard';
import { listCachedLedgers } from '../api/ledgerStorage';
import { listNotifications } from '../api/notifications';
import type { DashboardSummary } from '../api/types';
import { useAuth } from '../auth/AuthContext';
import AppHeader from '../components/AppHeader';
import BalanceCard, { type AccountBalanceRow } from '../components/BalanceCard';
import ListRow from '../components/ListRow';
import QuickActions from '../components/QuickActions';
import SectionHeader from '../components/SectionHeader';
import {
  activity,
  member,
  quickActions,
  tokenWallet,
} from '../data/mockData';
import { useMembership } from '../membership/MembershipContext';
import { addPushRefreshHandler, setPushNavigateHandler } from '../push/pushRegistration';
import { listMemberAccounts } from '../utils/serviceAccount';

const activityIcons: Record<string, string> = {
  checkmarkCircle: checkmarkCircleOutline,
  documentText: documentTextOutline,
  alertCircle: alertCircleOutline,
};

const Home: React.FC = () => {
  const router = useIonRouter();
  const { user, token } = useAuth();
  const { links, linkedAccounts, refreshLinks, refreshLinkedAccounts } = useMembership();
  const [unread, setUnread] = useState(0);
  const [summary, setSummary] = useState<DashboardSummary | null>(null);
  const [accountBalances, setAccountBalances] = useState<AccountBalanceRow[]>([]);

  const loadUnread = useCallback(async () => {
    if (!token) {
      setUnread(0);
      return;
    }
    try {
      const result = await listNotifications(token, 1);
      setUnread(result.unread_count);
    } catch {
      setUnread(0);
    }
  }, [token]);

  const loadSummary = useCallback(async () => {
    if (!token) {
      return;
    }
    try {
      const next = await getDashboardSummary(token);
      setSummary(next);
    } catch {
      setSummary(null);
    }
  }, [token]);

  const hydrateLedgerBalance = useCallback(() => {
    if (!user) {
      setAccountBalances([]);
      return;
    }

    const cached = listCachedLedgers(user.id);
    const rows: AccountBalanceRow[] = listMemberAccounts(linkedAccounts, links).map((account) => {
      const snapshot =
        cached[account.accountNumber] ?? cached[account.accountNumber.replace(/\D/g, '')];
      const summary = snapshot?.data.summary;
      return {
        accountNumber: account.accountNumber,
        ownerName: snapshot?.data.account.consumer_name ?? account.ownerName,
        currentBalance:
          summary?.current_balance != null
            ? summary.current_balance
            : summary?.current_due != null
              ? summary.current_due
              : null,
        dueDate: summary?.due_date ?? null,
      };
    });

    setAccountBalances(rows);
  }, [user, linkedAccounts, links]);

  useEffect(() => {
    hydrateLedgerBalance();
  }, [hydrateLedgerBalance]);

  useEffect(() => {
    const removeRefresh = addPushRefreshHandler(() => {
      void loadUnread();
    });
    setPushNavigateHandler((path) => {
      router.push(path);
    });
    return () => {
      removeRefresh();
      setPushNavigateHandler(null);
    };
  }, [loadUnread, router]);

  useIonViewWillEnter(() => {
    void loadSummary();
    void loadUnread();
    void refreshLinks();
    void refreshLinkedAccounts();
    hydrateLedgerBalance();
  }, [loadSummary, loadUnread, hydrateLedgerBalance, refreshLinks, refreshLinkedAccounts]);

  const displayName = user?.name ?? summary?.consumer.name ?? member.name;

  return (
    <IonPage>
      <AppHeader
        showLogo
        assistant
        onAssistantClick={() => router.push('/assistant')}
        bell
        unreadCount={unread}
        onBellClick={() => router.push('/notifications')}
      />
      <IonContent>
        <div className="page-pad">
          <p style={{ margin: '0 0 12px', color: 'var(--aselco-ink-500)', fontSize: 14 }}>
            Good day, <strong style={{ color: 'var(--aselco-ink-900)' }}>{displayName}</strong>
          </p>

          <BalanceCard wallet={tokenWallet} walletIsDemo accountBalances={accountBalances} />

          <SectionHeader title="Quick Actions" />
          <QuickActions actions={quickActions} />

          <SectionHeader title="Recent Activity" linkLabel="View All" linkHref="/tabs/ledger" />
          <IonList className="soft-list">
            {activity.map((a) => (
              <ListRow
                key={a.id}
                icon={activityIcons[a.icon]}
                title={a.title}
                meta={a.date}
                value={a.value}
                tone={a.tone}
                href={a.href}
              />
            ))}
          </IonList>
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Home;
