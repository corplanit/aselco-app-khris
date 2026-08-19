import { IonContent, IonPage, IonSpinner } from '@ionic/react';
import { StatusBar, Style } from '@capacitor/status-bar';
import { useEffect } from 'react';
import logoTag from '../../assets/logo_tag.png';
import './Onboarding.css';

async function setSplashStatusBar(): Promise<void> {
  try {
    await StatusBar.setBackgroundColor({ color: '#0e7c3a' });
    await StatusBar.setStyle({ style: Style.Light });
  } catch {
    // Web preview has no native status bar.
  }
}

async function restoreStatusBar(): Promise<void> {
  try {
    await StatusBar.setBackgroundColor({ color: '#ffffff' });
    await StatusBar.setStyle({ style: Style.Dark });
  } catch {
    // Web preview has no native status bar.
  }
}

const Splash: React.FC = () => {
  useEffect(() => {
    void setSplashStatusBar();
    return () => {
      void restoreStatusBar();
    };
  }, []);

  return (
    <IonPage>
      <IonContent className="splash-page" fullscreen>
        <div className="splash-shell">
          <img src={logoTag} alt="ASELCO — Serbisyong Mapahiyomon!" className="splash-logo" />
          <p className="splash-tag">Serbisyong Mapahiyomon — your member app</p>
          <IonSpinner name="crescent" className="splash-spinner" />
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Splash;
