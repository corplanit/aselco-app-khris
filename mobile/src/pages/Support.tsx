import {
  IonAccordion,
  IonAccordionGroup,
  IonButton,
  IonContent,
  IonIcon,
  IonItem,
  IonLabel,
  IonList,
  IonPage,
  IonSearchbar,
  useIonRouter,
} from '@ionic/react';
import {
  callOutline,
  headsetOutline,
  mailOutline,
  navigateOutline,
  phonePortraitOutline,
} from 'ionicons/icons';
import { useMemo, useState } from 'react';
import AppHeader from '../components/AppHeader';
import SectionHeader from '../components/SectionHeader';
import { support } from '../data/mockData';

const Support: React.FC = () => {
  const router = useIonRouter();
  const [query, setQuery] = useState('');

  const faqs = useMemo(() => {
    const q = query.trim().toLowerCase();
    if (!q) return support.faqs;
    return support.faqs.filter(
      (f) => f.q.toLowerCase().includes(q) || f.a.toLowerCase().includes(q)
    );
  }, [query]);

  return (
    <IonPage>
      <AppHeader title="Support" icon={headsetOutline} backHref="/tabs/home" />
      <IonContent>
        <div className="page-pad">
          <section className="balance-card" style={{ marginBottom: 8 }}>
            <p className="balance-card__label">We&apos;re here to help</p>
            <p style={{ margin: '6px 0 0', fontSize: 14, opacity: 0.95 }}>
              Reach ASELCO through any channel below, or browse the FAQ.
            </p>
          </section>

          <SectionHeader title="Contact channels" />
          <IonList className="soft-list">
            <IonItem href={`tel:${support.hotline.replace(/[^\d+]/g, '')}`} detail>
              <IonIcon slot="start" icon={callOutline} color="primary" />
              <IonLabel>
                <h3>Hotline</h3>
                <p>{support.hotline}</p>
              </IonLabel>
            </IonItem>
            <IonItem href={`sms:${support.mobile.replace(/\s/g, '')}`} detail>
              <IonIcon slot="start" icon={phonePortraitOutline} color="primary" />
              <IonLabel>
                <h3>Mobile / SMS</h3>
                <p>{support.mobile}</p>
              </IonLabel>
            </IonItem>
            <IonItem href={`mailto:${support.email}`} detail>
              <IonIcon slot="start" icon={mailOutline} color="primary" />
              <IonLabel>
                <h3>Email</h3>
                <p>{support.email}</p>
              </IonLabel>
            </IonItem>
            <IonItem lines="none">
              <IonIcon slot="start" icon={navigateOutline} color="primary" />
              <IonLabel>
                <h3>{support.office}</h3>
                <p>{support.hours}</p>
              </IonLabel>
            </IonItem>
          </IonList>

          <SectionHeader title="Frequently asked" />
          <IonSearchbar
            value={query}
            placeholder="Search FAQ"
            onIonInput={(e) => setQuery(e.detail.value ?? '')}
          />
          <IonAccordionGroup>
            {faqs.map((f) => (
              <IonAccordion key={f.q} value={f.q}>
                <IonItem slot="header" color="light">
                  <IonLabel>{f.q}</IonLabel>
                </IonItem>
                <div className="ion-padding" slot="content">
                  {f.a}
                </div>
              </IonAccordion>
            ))}
          </IonAccordionGroup>

          <IonButton
            expand="block"
            className="soft-btn ion-margin-top"
            onClick={() => router.push('/complaints')}
          >
            File a complaint
          </IonButton>
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Support;
