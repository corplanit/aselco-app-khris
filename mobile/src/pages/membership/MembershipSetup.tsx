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
  IonProgressBar,
  IonSpinner,
  IonText,
} from '@ionic/react';
import {
  checkmarkCircleOutline,
  documentTextOutline,
  eyeOutline,
  linkOutline,
  logOutOutline,
  shieldCheckmarkOutline,
} from 'ionicons/icons';
import { FormEvent, useEffect, useMemo, useState } from 'react';
import { useHistory, useLocation } from 'react-router-dom';
import { getMembershipPrivacy } from '../../api/membership';
import { ApiError } from '../../api/types';
import { useAuth } from '../../auth/AuthContext';
import { useMembership } from '../../membership/MembershipContext';
import logoTag from '../../assets/logo_tag.png';
import '../Auth.css';
import './MembershipSetup.css';

const TOTAL_STEPS = 4;

const MembershipSetup: React.FC = () => {
  const { token, signOut } = useAuth();
  const {
    submitLink,
    needsMembershipStepper,
    linkCount,
    canAddAnotherLink,
    markStepperComplete,
  } = useMembership();
  const history = useHistory();
  const location = useLocation();
  const isAddMode = new URLSearchParams(location.search).get('add') === '1';

  const [step, setStep] = useState(isAddMode ? 1 : 0);
  const [privacyAccepted, setPrivacyAccepted] = useState(isAddMode);
  const [privacyBody, setPrivacyBody] = useState('');
  const [accountNumber, setAccountNumber] = useState('');
  const [ownerName, setOwnerName] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({});
  const [doneMessage, setDoneMessage] = useState<string | null>(null);
  const [submittedCount, setSubmittedCount] = useState(0);

  useEffect(() => {
    if (isAddMode) {
      if (!canAddAnotherLink && !doneMessage) {
        history.replace('/tabs/profile');
      }
      return;
    }

    // Only auto-skip when already unlocked and user is not mid success screen.
    if (!needsMembershipStepper && !doneMessage && submittedCount === 0) {
      history.replace('/tabs/home');
    }
  }, [
    isAddMode,
    canAddAnotherLink,
    needsMembershipStepper,
    doneMessage,
    submittedCount,
    history,
  ]);

  useEffect(() => {
    if (!token) {
      return;
    }
    getMembershipPrivacy(token)
      .then((p) => setPrivacyBody(`${p.summary}\n\n${p.body}`))
      .catch(() =>
        setPrivacyBody(
          'In compliance with the Data Privacy Act of 2012 (R.A. 10173). ASELCO processes your account number and owner name to link your electric service account.',
        ),
      );
  }, [token]);

  const progress = useMemo(() => (step + 1) / TOTAL_STEPS, [step]);
  const displayLinkCount = Math.max(linkCount, submittedCount);

  const goNextFromPrivacy = () => {
    if (!privacyAccepted) {
      setError('Please accept the Data Privacy Policy to continue.');
      return;
    }
    setError(null);
    setStep(1);
  };

  const goNextFromAccount = () => {
    const errors: Record<string, string[]> = {};
    if (!accountNumber.trim()) {
      errors.account_number = ['Account number is required.'];
    }
    if (!ownerName.trim()) {
      errors.owner_name = ['Owner name is required.'];
    }
    if (Object.keys(errors).length) {
      setFieldErrors(errors);
      setError('Please complete the required fields.');
      return;
    }
    setFieldErrors({});
    setError(null);
    setStep(2);
  };

  const onSubmit = async (e?: FormEvent) => {
    e?.preventDefault();
    setSubmitting(true);
    setError(null);
    setFieldErrors({});

    try {
      const result = await submitLink({
        account_number: accountNumber.trim(),
        owner_name: ownerName.trim().toUpperCase(),
        privacy_accepted: true,
      });
      const nextCount = result.link_count ?? submittedCount + 1;
      setSubmittedCount(nextCount);
      setDoneMessage(
        nextCount >= 2
          ? '2 account link requests submitted. You can open the dashboard now.'
          : 'Account link request submitted. You can open the dashboard, or add one more account (max 2).',
      );
      setStep(3);
    } catch (err) {
      if (err instanceof ApiError) {
        if (err.data.errors) {
          setFieldErrors(err.data.errors);
          setStep(1);
        }
        setError(err.message || 'Could not submit membership information.');
      } else {
        setError('Could not submit membership information. Please try again.');
      }
    } finally {
      setSubmitting(false);
    }
  };

  const onAddAnother = () => {
    setAccountNumber('');
    setOwnerName('');
    setError(null);
    setFieldErrors({});
    setDoneMessage(null);
    setStep(1);
  };

  const onFinish = () => {
    markStepperComplete();
    history.replace(isAddMode ? '/tabs/profile' : '/tabs/home');
  };

  const onSignOut = async () => {
    await signOut();
    history.replace('/login');
  };

  const stepIcon = [shieldCheckmarkOutline, linkOutline, eyeOutline, checkmarkCircleOutline][step];

  return (
    <IonPage>
      <IonContent className="auth-page membership-page" fullscreen>
        <div className="auth-shell membership-shell">
          <div className="auth-intro">
            <div className="auth-intro__icon" aria-hidden>
              <IonIcon icon={stepIcon} />
            </div>
            <h1 className="auth-brand__title font-display">
              {isAddMode ? 'Link another account' : 'Membership information'}
            </h1>
            <p className="auth-brand__sub">
              {isAddMode
                ? 'Add a second electric account (maximum 2).'
                : 'Link 1–2 electric accounts to unlock the member dashboard.'}
            </p>
          </div>

          <div className="auth-logo-wrap">
            <img src={logoTag} alt="ASELCO — Serbisyong Mapahiyomon!" className="auth-logo" />
          </div>

          <div className="membership-progress">
            <p className="membership-progress__label">
              Step {Math.min(step + 1, TOTAL_STEPS)} of {TOTAL_STEPS}
              {displayLinkCount > 0 ? ` · ${displayLinkCount}/2 accounts linked` : ''}
            </p>
            <IonProgressBar value={progress} color="primary" />
          </div>

          <div className="auth-card membership-card">
            {step === 0 && (
              <>
                <h2 className="membership-step-title">
                  <IonIcon icon={documentTextOutline} /> Data Privacy
                </h2>
                <p className="membership-privacy-body">{privacyBody}</p>
                <IonItem lines="none" className="auth-terms">
                  <IonCheckbox
                    slot="start"
                    checked={privacyAccepted}
                    onIonChange={(e) => setPrivacyAccepted(e.detail.checked)}
                  />
                  <IonLabel className="ion-text-wrap">
                    I have read and agree to the Data Privacy Policy (R.A. 10173).
                  </IonLabel>
                </IonItem>
                {error && (
                  <IonText color="danger" className="auth-error">
                    <p>{error}</p>
                  </IonText>
                )}
                <IonButton
                  expand="block"
                  className="auth-submit"
                  disabled={!privacyAccepted}
                  onClick={goNextFromPrivacy}
                >
                  I agree &amp; continue
                </IonButton>
              </>
            )}

            {step === 1 && (
              <>
                <h2 className="membership-step-title">
                  Account classification {displayLinkCount > 0 ? `(#${displayLinkCount + 1})` : ''}
                </h2>
                <IonList lines="full" className="auth-list">
                  <IonItem>
                    <IonLabel position="stacked">Account number *</IonLabel>
                    <IonInput
                      type="number"
                      inputmode="numeric"
                      placeholder="e.g. 550006052"
                      value={accountNumber}
                      onIonInput={(e) => setAccountNumber(e.detail.value ?? '')}
                    />
                  </IonItem>
                  {fieldErrors.account_number && (
                    <IonText color="danger" className="auth-field-error">
                      <p>{fieldErrors.account_number[0]}</p>
                    </IonText>
                  )}
                  <IonItem>
                    <IonLabel position="stacked">Establishment / Owner name *</IonLabel>
                    <IonInput
                      type="text"
                      placeholder="e.g. ABA-A, GEMMA C."
                      value={ownerName}
                      onIonInput={(e) => setOwnerName((e.detail.value ?? '').toUpperCase())}
                    />
                  </IonItem>
                  <p className="membership-hint">Format: Last Name, First Name, Middle Initial</p>
                  {fieldErrors.owner_name && (
                    <IonText color="danger" className="auth-field-error">
                      <p>{fieldErrors.owner_name[0]}</p>
                    </IonText>
                  )}
                </IonList>
                {error && (
                  <IonText color="danger" className="auth-error">
                    <p>{error}</p>
                  </IonText>
                )}
                <div className="membership-actions">
                  <IonButton
                    fill="outline"
                    onClick={() => {
                      if (isAddMode) {
                        history.replace('/tabs/profile');
                        return;
                      }
                      setStep(displayLinkCount > 0 ? 3 : 0);
                    }}
                  >
                    Back
                  </IonButton>
                  <IonButton onClick={goNextFromAccount}>Continue</IonButton>
                </div>
              </>
            )}

            {step === 2 && (
              <>
                <h2 className="membership-step-title">Preview details</h2>
                <div className="membership-preview">
                  <div className="kv">
                    <span className="kv__k">Account number</span>
                    <span className="kv__v">{accountNumber}</span>
                  </div>
                  <div className="kv">
                    <span className="kv__k">Owner name</span>
                    <span className="kv__v">{ownerName}</span>
                  </div>
                </div>
                {error && (
                  <IonText color="danger" className="auth-error">
                    <p>{error}</p>
                  </IonText>
                )}
                <div className="membership-actions">
                  <IonButton fill="outline" onClick={() => setStep(1)} disabled={submitting}>
                    Back
                  </IonButton>
                  <IonButton onClick={() => onSubmit()} disabled={submitting}>
                    {submitting ? <IonSpinner name="crescent" /> : 'Confirm & submit'}
                  </IonButton>
                </div>
              </>
            )}

            {step === 3 && (
              <>
                <h2 className="membership-step-title">Request submitted</h2>
                <p className="membership-done">{doneMessage}</p>
                <p className="membership-hint">
                  Linked accounts: {displayLinkCount}/2. Staff will validate against cooperative
                  records. Dashboard is available after your first submission.
                </p>
                {canAddAnotherLink && displayLinkCount < 2 && (
                  <IonButton
                    expand="block"
                    fill="outline"
                    className="auth-submit"
                    onClick={onAddAnother}
                  >
                    Add another account
                  </IonButton>
                )}
                <IonButton expand="block" className="auth-submit" onClick={onFinish}>
                  {isAddMode ? 'Back to profile' : 'Go to dashboard'}
                </IonButton>
              </>
            )}
          </div>

          <button type="button" className="membership-signout" onClick={onSignOut}>
            <IonIcon icon={logOutOutline} /> Sign out
          </button>
        </div>
      </IonContent>
    </IonPage>
  );
};

export default MembershipSetup;
