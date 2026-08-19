import { IonButton, IonContent, IonPage } from '@ionic/react';
import { useHistory } from 'react-router-dom';
import logoTag from '../../assets/logo_tag.png';
import { lightHaptic } from '../../onboarding/haptics';
import { useOnboarding } from '../../onboarding/OnboardingContext';
import './Onboarding.css';

const Welcome: React.FC = () => {
  const history = useHistory();
  const { completeOnboarding } = useOnboarding();

  const skipToLogin = async () => {
    await lightHaptic();
    await completeOnboarding();
    history.replace('/login');
  };

  const startWalkthrough = async () => {
    await lightHaptic();
    history.push('/walkthrough');
  };

  return (
    <IonPage>
      <IonContent className="onboard-page" fullscreen>
        <div className="onboard-shell">
          <button type="button" className="onboard-skip" onClick={() => void skipToLogin()}>
            Skip
          </button>

          <div className="onboard-body">
            <img src={logoTag} alt="ASELCO — Serbisyong Mapahiyomon!" className="onboard-logo" />
            <h1 className="onboard-title font-display">Welcome to ASELCO</h1>
            <p className="onboard-copy">
              Pay with ASELCO Tokens, check your ledger, and file concerns — all from your phone.
            </p>
          </div>

          <div className="onboard-actions">
            <IonButton expand="block" className="soft-btn" onClick={() => void startWalkthrough()}>
              Get started
            </IonButton>
            <IonButton
              expand="block"
              fill="outline"
              className="soft-btn"
              onClick={() => void skipToLogin()}
            >
              Skip to sign in
            </IonButton>
          </div>
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Welcome;
