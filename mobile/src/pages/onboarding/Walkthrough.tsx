import { IonButton, IonContent, IonIcon, IonPage } from '@ionic/react';
import {
  createOutline,
  documentTextOutline,
  headsetOutline,
  walletOutline,
} from 'ionicons/icons';
import { useRef, useState } from 'react';
import { useHistory } from 'react-router-dom';
import { walkthroughSlides } from '../../data/walkthroughSlides';
import { lightHaptic } from '../../onboarding/haptics';
import { useOnboarding } from '../../onboarding/OnboardingContext';
import './Onboarding.css';

const iconMap: Record<string, string> = {
  wallet: walletOutline,
  documentText: documentTextOutline,
  create: createOutline,
  headset: headsetOutline,
};

const SWIPE_THRESHOLD = 48;

const Walkthrough: React.FC = () => {
  const history = useHistory();
  const { completeOnboarding } = useOnboarding();
  const [index, setIndex] = useState(0);
  const startX = useRef<number | null>(null);
  const last = index === walkthroughSlides.length - 1;
  const slide = walkthroughSlides[index];

  const finish = async () => {
    await lightHaptic();
    await completeOnboarding();
    history.replace('/login');
  };

  const goNext = async () => {
    if (last) {
      await finish();
      return;
    }
    await lightHaptic();
    setIndex((i) => Math.min(i + 1, walkthroughSlides.length - 1));
  };

  const goPrev = async () => {
    if (index === 0) {
      return;
    }
    await lightHaptic();
    setIndex((i) => Math.max(i - 1, 0));
  };

  const onTouchStart = (event: React.TouchEvent) => {
    startX.current = event.changedTouches[0]?.clientX ?? null;
  };

  const onTouchEnd = (event: React.TouchEvent) => {
    if (startX.current === null) {
      return;
    }
    const endX = event.changedTouches[0]?.clientX ?? startX.current;
    const delta = endX - startX.current;
    startX.current = null;
    if (delta <= -SWIPE_THRESHOLD) {
      void goNext();
    } else if (delta >= SWIPE_THRESHOLD) {
      void goPrev();
    }
  };

  return (
    <IonPage>
      <IonContent className="onboard-page" fullscreen>
        <div className="onboard-shell">
          <button type="button" className="onboard-skip" onClick={() => void finish()}>
            Skip
          </button>

          <div
            className="onboard-track"
            onTouchStart={onTouchStart}
            onTouchEnd={onTouchEnd}
          >
            <div className="onboard-slide" key={slide.id}>
              <span className="onboard-icon" aria-hidden>
                <IonIcon icon={iconMap[slide.icon] || walletOutline} />
              </span>
              <h1 className="onboard-title font-display">{slide.title}</h1>
              <p className="onboard-copy">{slide.body}</p>
            </div>
          </div>

          <div className="onboard-dots" role="tablist" aria-label="Walkthrough progress">
            {walkthroughSlides.map((item, i) => (
              <button
                key={item.id}
                type="button"
                className={`onboard-dot${i === index ? ' is-active' : ''}`}
                aria-label={`Slide ${i + 1}`}
                aria-current={i === index ? 'step' : undefined}
                onClick={() => setIndex(i)}
              />
            ))}
          </div>

          <div className="onboard-actions">
            <div className="onboard-row">
              {index > 0 && (
                <IonButton expand="block" fill="outline" className="soft-btn" onClick={() => void goPrev()}>
                  Back
                </IonButton>
              )}
              <IonButton expand="block" className="soft-btn" onClick={() => void goNext()}>
                {last ? 'Sign in' : 'Next'}
              </IonButton>
            </div>
          </div>
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Walkthrough;
