import {
  IonButton,
  IonContent,
  IonIcon,
  IonItem,
  IonLabel,
  IonList,
  IonPage,
  IonSpinner,
  IonToggle,
  useIonToast,
  useIonViewWillEnter,
} from '@ionic/react';
import {
  addCircleOutline,
  chevronForwardOutline,
  helpCircleOutline,
  linkOutline,
  lockClosedOutline,
  logOutOutline,
  personOutline,
} from 'ionicons/icons';
import { useCallback, useState } from 'react';
import { useHistory } from 'react-router-dom';
import { getLedger } from '../api/ledger';
import {
  getNotificationPreferences,
  updateNotificationPreferences,
} from '../api/notifications';
import type { LedgerConsumer } from '../api/types';
import { getCachedLedger, saveLedgerCache } from '../api/ledgerStorage';
import { useAuth } from '../auth/AuthContext';
import AppHeader from '../components/AppHeader';
import MeterDetailsCard, { type MeterReadingSummary } from '../components/MeterDetailsCard';
import SectionHeader from '../components/SectionHeader';
import ServiceInfoCard from '../components/ServiceInfoCard';
import StatusPill from '../components/StatusPill';
import { useMembership } from '../membership/MembershipContext';
import {
  displayOrDash,
  latestBillReading,
  listMemberAccounts,
  resolveServiceInfo,
} from '../utils/serviceAccount';

function formatStatusLabel(status: string): string {
  if (status === 'pending') {
    return 'Pending';
  }
  if (status === 'validated') {
    return 'Validated';
  }
  return status;
}

const Profile: React.FC = () => {
  const { user, token, signOut } = useAuth();
  const {
    links,
    linkedAccounts,
    linksLoading,
    canAddAnotherLink,
    linkCount,
    status,
    refreshLinks,
    refreshStatus,
    refreshLinkedAccounts,
  } = useMembership();
  const history = useHistory();
  const [billReminders, setBillReminders] = useState(true);
  const [outageAlerts, setOutageAlerts] = useState(true);
  const [prefsLoading, setPrefsLoading] = useState(false);
  const [signingOut, setSigningOut] = useState(false);
  const [ledgerConsumer, setLedgerConsumer] = useState<LedgerConsumer | null>(null);
  const [meter, setMeter] = useState<MeterReadingSummary | null>(null);
  const [meterLoading, setMeterLoading] = useState(false);
  const [present] = useIonToast();
  const linkLimit = status?.max_links ?? 2;

  const loadPrefs = useCallback(async () => {
    if (!token) {
      return;
    }
    setPrefsLoading(true);
    try {
      const prefs = await getNotificationPreferences(token);
      setBillReminders(prefs.billing);
      setOutageAlerts(prefs.service);
    } catch {
      // Keep local defaults.
    } finally {
      setPrefsLoading(false);
    }
  }, [token]);

  const loadMeter = useCallback(async () => {
    if (!token || !user) {
      setMeter(null);
      setLedgerConsumer(null);
      return;
    }

    const primary = listMemberAccounts(linkedAccounts, links)[0]?.accountNumber;
    if (!primary) {
      setMeter(null);
      setLedgerConsumer(null);
      return;
    }

    setMeterLoading(true);
    try {
      const cached = getCachedLedger(user.id, primary);
      let snapshot = cached?.data ?? null;
      if (!snapshot) {
        snapshot = await getLedger(token, {
          accountNumber: primary,
          snapshot: true,
          sort: 'latest',
        });
        saveLedgerCache(user.id, snapshot, primary);
      }

      const consumer = snapshot.consumer ?? {
        account_number: snapshot.account.account_number,
        name: snapshot.account.consumer_name,
        address: snapshot.account.consumer_address,
        status: snapshot.account.consumer_status,
        meter_no: null,
        rate_class: null,
      };
      setLedgerConsumer(consumer);

      const bill = latestBillReading(snapshot.entries);
      setMeter({
        meterNo: consumer.meter_no ?? linkedAccounts[0]?.meter_no ?? null,
        previousReading: bill?.previous_reading ?? null,
        presentReading: bill?.present_reading ?? null,
        kwh: bill?.kwh ?? snapshot.summary.kwh_used ?? null,
        demandKw: bill?.demand_kw ?? null,
        billMonthLabel: bill?.bill_month
          ? snapshot.summary.billing_period
          : snapshot.summary.billing_period,
      });
    } catch {
      setMeter(null);
    } finally {
      setMeterLoading(false);
    }
  }, [token, user, linkedAccounts, links]);

  useIonViewWillEnter(() => {
    void refreshLinks();
    void refreshStatus();
    void refreshLinkedAccounts();
    void loadPrefs();
    void loadMeter();
  });

  const toast = (msg: string) =>
    present({ message: msg, duration: 1800, color: 'primary' });

  const displayName = user?.name ?? 'Member';
  const displayEmail = user?.email ?? '—';
  const service = resolveServiceInfo(linkedAccounts, links, ledgerConsumer);
  const displayMobile = user?.contact_no;
  const initials =
    displayName
      .split(/\s+/)
      .filter(Boolean)
      .slice(0, 2)
      .map((part) => part[0]?.toUpperCase() ?? '')
      .join('') || 'M';

  const onSignOut = async () => {
    if (signingOut) {
      return;
    }
    setSigningOut(true);
    try {
      await signOut();
      history.replace('/login');
    } catch {
      present({ message: 'Could not sign out. Try again.', duration: 2000, color: 'danger' });
    } finally {
      setSigningOut(false);
    }
  };

  const onLinkAnother = () => {
    if (!canAddAnotherLink) {
      present({
        message: `You can link up to ${linkLimit} electric accounts.`,
        duration: 2200,
        color: 'warning',
      });
      return;
    }
    history.push('/membership/setup?add=1');
  };

  const onToggleBilling = async (checked: boolean) => {
    setBillReminders(checked);
    if (!token) {
      return;
    }
    try {
      await updateNotificationPreferences(token, { billing: checked });
    } catch {
      setBillReminders(!checked);
      present({ message: 'Could not save bill reminder preference.', duration: 2000, color: 'danger' });
    }
  };

  const onToggleService = async (checked: boolean) => {
    setOutageAlerts(checked);
    if (!token) {
      return;
    }
    try {
      await updateNotificationPreferences(token, { service: checked });
    } catch {
      setOutageAlerts(!checked);
      present({ message: 'Could not save outage alert preference.', duration: 2000, color: 'danger' });
    }
  };

  return (
    <IonPage>
      <AppHeader title="Profile" icon={personOutline} />
      <IonContent>
        <div className="page-pad">
          <div style={{ display: 'flex', alignItems: 'center', gap: 14, marginBottom: 8 }}>
            <div className="avatar-circle">{initials}</div>
            <div>
              <h2 className="font-display" style={{ margin: 0, fontSize: 20 }}>
                {displayName}
              </h2>
              <p style={{ margin: '4px 0 0', color: 'var(--aselco-ink-500)', fontSize: 13 }}>
                {displayEmail}
              </p>
              <p style={{ margin: '2px 0 0', color: 'var(--aselco-ink-400)', fontSize: 12 }}>
                Linked requests {linkCount}/{linkLimit}
              </p>
            </div>
          </div>

          <SectionHeader title="Consumer Information" />
          <div className="soft-card">
            <div className="kv">
              <span className="kv__k">Full name</span>
              <span className="kv__v">{displayOrDash(displayName)}</span>
            </div>
            <div className="kv">
              <span className="kv__k">Email</span>
              <span className="kv__v">{displayOrDash(displayEmail)}</span>
            </div>
            <div className="kv">
              <span className="kv__k">Mobile</span>
              <span className="kv__v">{displayOrDash(displayMobile)}</span>
            </div>
          </div>

          <SectionHeader title="Service Information" />
          <ServiceInfoCard service={service} loading={linksLoading} />

          <SectionHeader title="Meter Details" />
          <MeterDetailsCard meter={meter} loading={meterLoading} />

          <SectionHeader title="Account links" />
          <div className="soft-card">
            {linksLoading && links.length === 0 ? (
              <div style={{ display: 'grid', placeItems: 'center', padding: 20 }}>
                <IonSpinner name="crescent" color="primary" />
              </div>
            ) : links.length === 0 ? (
              <p style={{ margin: 0, fontSize: 13, color: 'var(--aselco-ink-500)' }}>
                No account link requests yet.
              </p>
            ) : (
              <>
                {linksLoading && (
                  <div style={{ display: 'flex', justifyContent: 'flex-end', marginBottom: 6 }}>
                    <IonSpinner name="crescent" color="primary" style={{ width: 18, height: 18 }} />
                  </div>
                )}
                <IonList className="soft-list" style={{ margin: 0, background: 'transparent' }}>
                  {links.map((link) => (
                    <IonItem key={link.id} lines="full" detail={false}>
                      <IonIcon slot="start" icon={linkOutline} color="primary" />
                      <IonLabel>
                        <h3>Account {link.account_number}</h3>
                        <p>{link.owner_name}</p>
                      </IonLabel>
                      <StatusPill status={formatStatusLabel(link.status)} />
                    </IonItem>
                  ))}
                </IonList>
              </>
            )}

            <IonButton
              expand="block"
              fill="outline"
              className="profile-link-another"
              disabled={!canAddAnotherLink}
              onClick={onLinkAnother}
            >
              <IonIcon slot="start" icon={addCircleOutline} />
              {canAddAnotherLink
                ? 'Link another account'
                : `Account link limit reached (${linkCount}/${linkLimit})`}
            </IonButton>
          </div>

          <SectionHeader title="Notifications" />
          <IonList className="soft-list">
            <IonItem>
              <IonLabel>
                <h3>Bill reminders</h3>
                <p>Due date and new statement alerts</p>
              </IonLabel>
              <IonToggle
                checked={billReminders}
                disabled={prefsLoading || !token}
                onIonChange={(e) => void onToggleBilling(e.detail.checked)}
              />
            </IonItem>
            <IonItem>
              <IonLabel>
                <h3>Outage alerts</h3>
                <p>Scheduled interruptions in your area</p>
              </IonLabel>
              <IonToggle
                checked={outageAlerts}
                disabled={prefsLoading || !token}
                onIonChange={(e) => void onToggleService(e.detail.checked)}
              />
            </IonItem>
          </IonList>

          <SectionHeader title="Settings" />
          <IonList className="soft-list">
            <IonItem button detail={false} onClick={() => toast('Password change is a UI demo.')}>
              <IonIcon slot="start" icon={lockClosedOutline} color="primary" />
              <IonLabel>Change password</IonLabel>
              <IonIcon slot="end" icon={chevronForwardOutline} color="medium" />
            </IonItem>
            <IonItem button detail={false} routerLink="/support">
              <IonIcon slot="start" icon={helpCircleOutline} color="primary" />
              <IonLabel>Help & support</IonLabel>
              <IonIcon slot="end" icon={chevronForwardOutline} color="medium" />
            </IonItem>
            <IonItem button detail={false} disabled={signingOut} onClick={onSignOut}>
              <IonIcon slot="start" icon={logOutOutline} color="danger" />
              <IonLabel color="danger">{signingOut ? 'Signing out…' : 'Sign out'}</IonLabel>
            </IonItem>
          </IonList>
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Profile;
