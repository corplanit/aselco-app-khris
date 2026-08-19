import {
  IonButton,
  IonCheckbox,
  IonContent,
  IonIcon,
  IonInput,
  IonItem,
  IonLabel,
  IonList,
  IonPage,
  IonSpinner,
  IonText,
} from '@ionic/react';
import { mailOutline, personAddOutline } from 'ionicons/icons';
import { FormEvent, useState } from 'react';
import { Link, useHistory } from 'react-router-dom';
import { useAuth } from '../auth/AuthContext';
import { ApiError } from '../api/types';
import logoTag from '../assets/logo_tag.png';
import './Auth.css';

const Register: React.FC = () => {
  const { signUp } = useAuth();
  const history = useHistory();

  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [terms, setTerms] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({});
  const [successMessage, setSuccessMessage] = useState<string | null>(null);

  const onSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError(null);
    setFieldErrors({});
    setSuccessMessage(null);
    setSubmitting(true);

    try {
      const result = await signUp({
        name: name.trim(),
        email: email.trim(),
        password,
        password_confirmation: passwordConfirmation,
        terms,
      });
      setSuccessMessage(result.message);
    } catch (err) {
      if (err instanceof ApiError) {
        if (err.data.errors) {
          setFieldErrors(err.data.errors);
        }
        setError(err.message || 'Registration failed.');
      } else {
        setError('Registration failed. Please try again.');
      }
    } finally {
      setSubmitting(false);
    }
  };

  if (successMessage) {
    return (
      <IonPage>
        <IonContent className="auth-page" fullscreen>
          <div className="auth-shell">
            <div className="auth-intro">
              <div className="auth-intro__icon" aria-hidden>
                <IonIcon icon={mailOutline} />
              </div>
              <h1 className="auth-brand__title font-display">Check your email</h1>
              <p className="auth-brand__sub">{successMessage}</p>
            </div>
            <div className="auth-logo-wrap">
              <img src={logoTag} alt="ASELCO — Serbisyong Mapahiyomon!" className="auth-logo" />
            </div>
            <div className="auth-card">
              <IonButton expand="block" onClick={() => history.replace('/login')}>
                Back to sign in
              </IonButton>
            </div>
          </div>
        </IonContent>
      </IonPage>
    );
  }

  return (
    <IonPage>
      <IonContent className="auth-page" fullscreen>
        <div className="auth-shell">
          <div className="auth-intro">
            <div className="auth-intro__icon" aria-hidden>
              <IonIcon icon={personAddOutline} />
            </div>
            <h1 className="auth-brand__title font-display">Create account</h1>
            <p className="auth-brand__sub">
              Member registration uses the same details as the website portal.
            </p>
          </div>

          <div className="auth-logo-wrap">
            <img src={logoTag} alt="ASELCO — Serbisyong Mapahiyomon!" className="auth-logo" />
          </div>

          <form className="auth-card" onSubmit={onSubmit}>
            <IonList lines="full" className="auth-list">
              <IonItem>
                <IonLabel position="stacked">Full name</IonLabel>
                <IonInput
                  type="text"
                  autocomplete="name"
                  placeholder="Juan Dela Cruz"
                  value={name}
                  required
                  onIonInput={(e) => setName(e.detail.value ?? '')}
                />
              </IonItem>
              {fieldErrors.name && (
                <IonText color="danger" className="auth-field-error">
                  <p>{fieldErrors.name[0]}</p>
                </IonText>
              )}

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
              {fieldErrors.email && (
                <IonText color="danger" className="auth-field-error">
                  <p>{fieldErrors.email[0]}</p>
                </IonText>
              )}

              <IonItem>
                <IonLabel position="stacked">Password</IonLabel>
                <IonInput
                  type="password"
                  autocomplete="new-password"
                  placeholder="Create a password"
                  value={password}
                  required
                  onIonInput={(e) => setPassword(e.detail.value ?? '')}
                />
              </IonItem>
              {fieldErrors.password && (
                <IonText color="danger" className="auth-field-error">
                  <p>{fieldErrors.password[0]}</p>
                </IonText>
              )}

              <IonItem>
                <IonLabel position="stacked">Confirm password</IonLabel>
                <IonInput
                  type="password"
                  autocomplete="new-password"
                  placeholder="Re-enter your password"
                  value={passwordConfirmation}
                  required
                  onIonInput={(e) => setPasswordConfirmation(e.detail.value ?? '')}
                />
              </IonItem>

              <IonItem lines="none" className="auth-terms">
                <IonCheckbox
                  slot="start"
                  checked={terms}
                  onIonChange={(e) => setTerms(e.detail.checked)}
                />
                <IonLabel className="ion-text-wrap">
                  I agree to the Terms of Service and Privacy Policy
                </IonLabel>
              </IonItem>
              {fieldErrors.terms && (
                <IonText color="danger" className="auth-field-error">
                  <p>{fieldErrors.terms[0]}</p>
                </IonText>
              )}
            </IonList>

            {error && (
              <IonText color="danger" className="auth-error">
                <p>{error}</p>
              </IonText>
            )}

            <IonButton
              expand="block"
              type="submit"
              className="auth-submit"
              disabled={submitting || !terms}
            >
              {submitting ? <IonSpinner name="crescent" /> : 'Create account'}
            </IonButton>

            <p className="auth-switch">
              Already registered?{' '}
              <Link to="/login">Sign in</Link>
            </p>
          </form>
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Register;
