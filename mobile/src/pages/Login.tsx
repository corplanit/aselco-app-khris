import {
  IonButton,
  IonContent,
  IonIcon,
  IonInput,
  IonItem,
  IonLabel,
  IonList,
  IonPage,
  IonSpinner,
  IonText,
  useIonToast,
} from '@ionic/react';
import { logInOutline } from 'ionicons/icons';
import { FormEvent, useState } from 'react';
import { Link, useHistory } from 'react-router-dom';
import { useAuth } from '../auth/AuthContext';
import { ApiError } from '../api/types';
import logoTag from '../assets/logo_tag.png';
import './Auth.css';

const Login: React.FC = () => {
  const { signIn, resendVerification } = useAuth();
  const history = useHistory();
  const [present] = useIonToast();

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [needsVerify, setNeedsVerify] = useState(false);

  const onSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError(null);
    setNeedsVerify(false);
    setSubmitting(true);

    try {
      await signIn({ email: email.trim(), password });
      history.replace('/');
    } catch (err) {
      if (err instanceof ApiError) {
        if (err.status === 403 && err.data.email_verified === false) {
          setNeedsVerify(true);
          setError(err.message);
        } else if (err.data.errors?.email?.[0]) {
          setError(err.data.errors.email[0]);
        } else {
          setError(err.message || 'Sign in failed.');
        }
      } else {
        setError('Sign in failed. Please try again.');
      }
    } finally {
      setSubmitting(false);
    }
  };

  const onResend = async () => {
    try {
      const message = await resendVerification(email.trim());
      present({ message, duration: 2500, color: 'primary' });
    } catch (err) {
      const msg = err instanceof ApiError ? err.message : 'Could not resend verification.';
      present({ message: msg, duration: 2500, color: 'danger' });
    }
  };

  return (
    <IonPage>
      <IonContent className="auth-page" fullscreen>
        <div className="auth-shell">
          <div className="auth-intro">
            <div className="auth-intro__icon" aria-hidden>
              <IonIcon icon={logInOutline} />
            </div>
            <h1 className="auth-brand__title font-display">Sign in</h1>
            <p className="auth-brand__sub">
              Use the same email and password as the ASELCO website member portal.
            </p>
          </div>

          <div className="auth-logo-wrap">
            <img src={logoTag} alt="ASELCO — Serbisyong Mapahiyomon!" className="auth-logo" />
          </div>

          <form className="auth-card" onSubmit={onSubmit}>
            <IonList lines="full" className="auth-list">
              <IonItem>
                <IonLabel position="stacked">Email</IonLabel>
                <IonInput
                  type="email"
                  autocomplete="email"
                  placeholder="name@example.com"
                  value={email}
                  required
                  onIonInput={(e) => setEmail(e.detail.value ?? '')}
                />
              </IonItem>
              <IonItem>
                <IonLabel position="stacked">Password</IonLabel>
                <IonInput
                  type="password"
                  autocomplete="current-password"
                  placeholder="Enter your password"
                  value={password}
                  required
                  onIonInput={(e) => setPassword(e.detail.value ?? '')}
                />
              </IonItem>
            </IonList>

            {error && (
              <IonText color="danger" className="auth-error">
                <p>{error}</p>
              </IonText>
            )}

            {needsVerify && (
              <div className="auth-verify">
                <p>Verify your email before signing in.</p>
                <IonButton fill="outline" size="small" onClick={onResend} disabled={!email.trim()}>
                  Resend verification email
                </IonButton>
              </div>
            )}

            <IonButton expand="block" type="submit" className="auth-submit" disabled={submitting}>
              {submitting ? <IonSpinner name="crescent" /> : 'Sign in'}
            </IonButton>

            <p className="auth-switch">
              New member?{' '}
              <Link to="/register">Create an account</Link>
            </p>
          </form>
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Login;
