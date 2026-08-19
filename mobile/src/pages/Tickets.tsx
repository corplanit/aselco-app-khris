import {
  IonButton,
  IonCard,
  IonCardContent,
  IonCardHeader,
  IonCardSubtitle,
  IonCardTitle,
  IonContent,
  IonPage,
  useIonRouter,
} from '@ionic/react';
import { ticketOutline } from 'ionicons/icons';
import { useMemo, useState } from 'react';
import AppHeader from '../components/AppHeader';
import EmptyState from '../components/EmptyState';
import FilterChips from '../components/FilterChips';
import StatusPill from '../components/StatusPill';
import { tickets } from '../data/mockData';

const FILTERS = ['Open', 'Resolved', 'All'];

const Tickets: React.FC = () => {
  const router = useIonRouter();
  const [filter, setFilter] = useState('Open');
  const [expanded, setExpanded] = useState<string | null>(null);

  const items = useMemo(() => {
    if (filter === 'Open') return tickets.filter((t) => t.status !== 'Resolved');
    if (filter === 'Resolved') return tickets.filter((t) => t.status === 'Resolved');
    return tickets;
  }, [filter]);

  return (
    <IonPage>
      <AppHeader title="Tickets" icon={ticketOutline} />
      <IonContent>
        <div className="page-pad">
          <FilterChips options={FILTERS} value={filter} onChange={setFilter} />

          {items.length === 0 ? (
            <EmptyState title="No tickets" message="File a complaint to create a ticket." />
          ) : (
            items.map((t) => {
              const open = expanded === t.id;
              return (
                <IonCard key={t.id} button onClick={() => setExpanded(open ? null : t.id)}>
                  <IonCardHeader>
                    <div style={{ display: 'flex', justifyContent: 'space-between', gap: 8 }}>
                      <IonCardSubtitle>{t.id} · {t.category}</IonCardSubtitle>
                      <StatusPill status={t.status} />
                    </div>
                    <IonCardTitle style={{ fontSize: 16 }}>{t.subject}</IonCardTitle>
                  </IonCardHeader>
                  {open && (
                    <IonCardContent>
                      <p style={{ marginTop: 0, color: 'var(--aselco-ink-500)', fontSize: 13 }}>
                        Filed {t.filed} · Updated {t.updated}
                      </p>
                      <p>{t.note}</p>
                      <IonButton
                        size="small"
                        fill="outline"
                        className="soft-btn"
                        onClick={(e) => {
                          e.stopPropagation();
                          router.push('/support');
                        }}
                      >
                        Contact support
                      </IonButton>
                    </IonCardContent>
                  )}
                </IonCard>
              );
            })
          )}

          <IonButton
            expand="block"
            className="soft-btn ion-margin-top"
            onClick={() => router.push('/complaints')}
          >
            File new complaint
          </IonButton>
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Tickets;
