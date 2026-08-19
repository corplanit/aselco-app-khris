import {
  IonButton,
  IonCard,
  IonCardContent,
  IonCardHeader,
  IonCardSubtitle,
  IonCardTitle,
  IonContent,
  IonPage,
  IonRefresher,
  IonRefresherContent,
  IonSpinner,
  useIonViewWillEnter,
} from '@ionic/react';
import { notificationsOutline } from 'ionicons/icons';
import { useCallback, useEffect, useState } from 'react';
import { useHistory } from 'react-router-dom';
import {
  listNotifications,
  markAllNotificationsRead,
  markNotificationRead,
} from '../api/notifications';
import type { AppNotification } from '../api/types';
import { useAuth } from '../auth/AuthContext';
import AppHeader from '../components/AppHeader';
import EmptyState from '../components/EmptyState';
import { addPushRefreshHandler } from '../push/pushRegistration';

function categoryLabel(category: string): string {
  if (category === 'billing') {
    return 'Billing';
  }
  if (category === 'service') {
    return 'Service';
  }
  return 'Alert';
}

function formatWhen(iso: string | null | undefined): string {
  if (!iso) {
    return '';
  }
  const date = new Date(iso);
  if (Number.isNaN(date.getTime())) {
    return iso;
  }
  return date.toLocaleString(undefined, {
    month: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  });
}

const Notifications: React.FC = () => {
  const { token } = useAuth();
  const history = useHistory();
  const [items, setItems] = useState<AppNotification[]>([]);
  const [unread, setUnread] = useState(0);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    if (!token) {
      setItems([]);
      setUnread(0);
      setLoading(false);
      return;
    }
    setError(null);
    try {
      const result = await listNotifications(token);
      setItems(result.data);
      setUnread(result.unread_count);
    } catch {
      setError('Could not load notifications.');
    } finally {
      setLoading(false);
    }
  }, [token]);

  useIonViewWillEnter(() => {
    setLoading(true);
    void load();
  });

  useEffect(() => {
    return addPushRefreshHandler(() => {
      void load();
    });
  }, [load]);

  const markAllRead = async () => {
    if (!token || unread === 0) {
      return;
    }
    try {
      await markAllNotificationsRead(token);
      setItems((prev) => prev.map((n) => ({ ...n, unread: false, read_at: n.read_at ?? new Date().toISOString() })));
      setUnread(0);
    } catch {
      setError('Could not mark notifications as read.');
    }
  };

  const markRead = async (item: AppNotification) => {
    if (!token) {
      return;
    }
    if (item.unread) {
      try {
        await markNotificationRead(token, item.id);
        setItems((prev) =>
          prev.map((n) =>
            n.id === item.id
              ? { ...n, unread: false, read_at: n.read_at ?? new Date().toISOString() }
              : n,
          ),
        );
        setUnread((count) => Math.max(0, count - 1));
      } catch {
        // Still allow navigation.
      }
    }
    const deepLink = item.data?.deep_link;
    if (typeof deepLink === 'string' && deepLink.startsWith('/')) {
      history.push(deepLink);
    }
  };

  return (
    <IonPage>
      <AppHeader title="Notifications" icon={notificationsOutline} backHref="/tabs/home" />
      <IonContent>
        <IonRefresher
          slot="fixed"
          onIonRefresh={async (event) => {
            await load();
            event.detail.complete();
          }}
        >
          <IonRefresherContent />
        </IonRefresher>

        <div className="page-pad">
          <div
            style={{
              display: 'flex',
              justifyContent: 'space-between',
              alignItems: 'center',
              marginBottom: 12,
            }}
          >
            <p style={{ margin: 0, color: 'var(--aselco-ink-500)', fontSize: 14 }}>
              {unread} unread
            </p>
            <IonButton size="small" fill="clear" disabled={unread === 0} onClick={() => void markAllRead()}>
              Mark all read
            </IonButton>
          </div>

          {error ? (
            <p style={{ margin: '0 0 12px', color: 'var(--ion-color-danger)', fontSize: 13 }}>{error}</p>
          ) : null}

          {loading ? (
            <div style={{ display: 'grid', placeItems: 'center', padding: 40 }}>
              <IonSpinner name="crescent" color="primary" />
            </div>
          ) : items.length === 0 ? (
            <EmptyState title="No notifications" />
          ) : (
            items.map((n) => (
              <IonCard
                key={n.id}
                button
                className={n.unread ? 'notif-unread' : undefined}
                onClick={() => void markRead(n)}
              >
                <IonCardHeader>
                  <IonCardSubtitle>
                    {categoryLabel(n.category)} · {formatWhen(n.created_at)}
                  </IonCardSubtitle>
                  <IonCardTitle>{n.title}</IonCardTitle>
                </IonCardHeader>
                <IonCardContent>{n.body}</IonCardContent>
              </IonCard>
            ))
          )}
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Notifications;
