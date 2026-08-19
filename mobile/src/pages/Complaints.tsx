import {
  IonButton,
  IonContent,
  IonIcon,
  IonInput,
  IonItem,
  IonLabel,
  IonList,
  IonPage,
  IonSelect,
  IonSelectOption,
  IonTextarea,
  useIonRouter,
  useIonToast,
} from '@ionic/react';
import { checkmarkCircle, createOutline } from 'ionicons/icons';
import { useState } from 'react';
import AppHeader from '../components/AppHeader';
import SectionHeader from '../components/SectionHeader';
import { complaintCategories, member } from '../data/mockData';

type Step = 'form' | 'done';

const Complaints: React.FC = () => {
  const router = useIonRouter();
  const [present] = useIonToast();
  const [step, setStep] = useState<Step>('form');
  const [category, setCategory] = useState('');
  const [subject, setSubject] = useState('');
  const [details, setDetails] = useState('');
  const [ticketId, setTicketId] = useState('');

  const submit = () => {
    if (!category || !subject.trim() || !details.trim()) {
      present({
        message: 'Please fill category, subject, and details.',
        duration: 2200,
        color: 'warning',
      });
      return;
    }
    setTicketId(`TCK-${Math.floor(1000 + Math.random() * 9000)}`);
    setStep('done');
  };

  return (
    <IonPage>
      <AppHeader title="File Complaint" icon={createOutline} backHref="/tabs/tickets" />
      <IonContent>
        <div className="page-pad">
          {step === 'form' ? (
            <>
              <SectionHeader title="Complaint details" />
              <IonList className="soft-list">
                <IonItem>
                  <IonLabel position="stacked">Category</IonLabel>
                  <IonSelect
                    placeholder="Select category"
                    value={category}
                    onIonChange={(e) => setCategory(e.detail.value)}
                  >
                    {complaintCategories.map((c) => (
                      <IonSelectOption key={c} value={c}>
                        {c}
                      </IonSelectOption>
                    ))}
                  </IonSelect>
                </IonItem>
                <IonItem>
                  <IonLabel position="stacked">Subject</IonLabel>
                  <IonInput
                    value={subject}
                    placeholder="Short summary"
                    onIonInput={(e) => setSubject(String(e.detail.value ?? ''))}
                  />
                </IonItem>
                <IonItem>
                  <IonLabel position="stacked">Details</IonLabel>
                  <IonTextarea
                    autoGrow
                    rows={4}
                    value={details}
                    placeholder="Describe what happened"
                    onIonInput={(e) => setDetails(String(e.detail.value ?? ''))}
                  />
                </IonItem>
              </IonList>

              <IonButton
                expand="block"
                fill="outline"
                className="soft-btn ion-margin-top"
                onClick={() =>
                  present({
                    message: 'Photo upload is a UI demo only.',
                    duration: 2000,
                    color: 'primary',
                  })
                }
              >
                Attach photo
              </IonButton>

              <SectionHeader title="Service address" />
              <div className="soft-card">
                <div className="kv">
                  <span className="kv__k">Account</span>
                  <span className="kv__v">{member.accountNo}</span>
                </div>
                <div className="kv">
                  <span className="kv__k">Address</span>
                  <span className="kv__v">{member.address}</span>
                </div>
              </div>

              <IonButton expand="block" className="soft-btn ion-margin-top" onClick={submit}>
                Submit complaint
              </IonButton>
            </>
          ) : (
            <>
              <section className="success-hero soft-card">
                <IonIcon icon={checkmarkCircle} />
                <h2 className="font-display" style={{ margin: '8px 0 4px' }}>Complaint filed</h2>
                <p style={{ margin: 0, color: 'var(--aselco-ink-500)' }}>
                  Your ticket ID is <strong>{ticketId}</strong>
                </p>
              </section>
              <IonButton
                expand="block"
                className="soft-btn ion-margin-top"
                onClick={() => router.push('/tabs/tickets')}
              >
                View tickets
              </IonButton>
              <IonButton
                expand="block"
                fill="outline"
                className="soft-btn"
                onClick={() => {
                  setCategory('');
                  setSubject('');
                  setDetails('');
                  setStep('form');
                }}
              >
                File another
              </IonButton>
            </>
          )}
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Complaints;
