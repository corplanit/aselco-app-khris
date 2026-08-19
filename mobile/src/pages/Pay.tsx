import {
  IonButton,
  IonContent,
  IonIcon,
  IonInput,
  IonItem,
  IonLabel,
  IonPage,
  useIonRouter,
  useIonToast,
} from '@ionic/react';
import { checkmarkCircle, walletOutline } from 'ionicons/icons';
import { useState } from 'react';
import AppHeader from '../components/AppHeader';
import SectionHeader from '../components/SectionHeader';
import { billing, member, tokenWallet as seedWallet } from '../data/mockData';
import { peso } from '../utils/format';

type Step = 'form' | 'receipt';

/** Format ASELCO tokens for display */
function tokens(amount: number): string {
  return `${amount.toLocaleString('en-PH', { minimumFractionDigits: 2 })} AST`;
}

/**
 * Pay bill using ASELCO Tokens (AST) only — no other payment channels.
 */
const Pay: React.FC = () => {
  const router = useIonRouter();
  const [present] = useIonToast();
  const [step, setStep] = useState<Step>('form');
  const [walletBalance, setWalletBalance] = useState(seedWallet.balance);
  const [amount, setAmount] = useState(billing.amountDue);
  const [ref, setRef] = useState('');
  const [paidWithTokens, setPaidWithTokens] = useState(0);

  const canPay = amount > 0 && walletBalance >= amount;

  const handlePay = () => {
    if (!canPay) {
      present({
        message: 'Not enough AST in your wallet for this amount.',
        duration: 2400,
        color: 'warning',
      });
      return;
    }
    setWalletBalance((b) => b - amount);
    setPaidWithTokens(amount);
    setRef(`AST-${Date.now().toString().slice(-8)}`);
    setStep('receipt');
  };

  const reset = () => {
    setAmount(billing.amountDue);
    setRef('');
    setPaidWithTokens(0);
    setStep('form');
  };

  return (
    <IonPage>
      <AppHeader title="Pay with AST" icon={walletOutline} />
      <IonContent>
        <div className="page-pad">
          {step === 'form' ? (
            <>
              <div className="token-balance">
                <p className="token-balance__label">ASELCO Token wallet</p>
                <p className="token-balance__amount">{tokens(walletBalance)}</p>
                <p className="token-balance__hint">
                  Bills are paid with <strong>AST only</strong> (1 AST ≈ ₱1). No other payment methods.
                </p>
              </div>

              <section className="balance-card" style={{ marginBottom: 8 }}>
                <p className="balance-card__label">Amount Due</p>
                <p className="balance-card__amount">{peso(billing.amountDue)}</p>
                <p className="balance-card__due" style={{ marginBottom: 0 }}>
                  Statement {billing.statementNo} · Due <strong>{billing.dueDate}</strong>
                </p>
              </section>

              <SectionHeader title="Pay with ASELCO Tokens" />
              <div className="soft-card" style={{ marginBottom: 8 }}>
                <div className="soft-card-btn is-selected" style={{ cursor: 'default', marginBottom: 12 }}>
                  <span className="soft-card-btn__icon">
                    <IonIcon icon={walletOutline} />
                  </span>
                  <span className="soft-card-btn__body">
                    <span className="soft-card-btn__title">ASELCO Token (AST)</span>
                    <span className="soft-card-btn__meta">Only payment method · wallet balance above</span>
                  </span>
                  <span className="soft-card-btn__check" aria-hidden />
                </div>

                <IonItem lines="none" style={{ '--background': 'transparent' } as React.CSSProperties}>
                  <IonLabel position="stacked">Tokens to spend (AST)</IonLabel>
                  <IonInput
                    type="number"
                    value={amount}
                    onIonInput={(e) => setAmount(Math.max(0, Number(e.detail.value) || 0))}
                  />
                </IonItem>
                <IonButton
                  expand="block"
                  fill="clear"
                  className="soft-btn"
                  onClick={() => setAmount(billing.amountDue)}
                >
                  Use full amount due
                </IonButton>
                <p style={{ fontSize: 12, color: 'var(--aselco-ink-500)', margin: '0 4px' }}>
                  {canPay
                    ? `After pay, wallet ≈ ${tokens(walletBalance - amount)}`
                    : 'Not enough AST — lower the amount or add tokens later (API).'}
                </p>
              </div>

              <IonButton
                expand="block"
                className="soft-btn ion-margin-top"
                disabled={!canPay}
                onClick={handlePay}
              >
                Pay bill with {tokens(amount)}
              </IonButton>
            </>
          ) : (
            <>
              <section className="success-hero soft-card">
                <IonIcon icon={checkmarkCircle} />
                <h2 className="font-display" style={{ margin: '8px 0 4px', fontSize: 20 }}>
                  Paid with ASELCO Tokens
                </h2>
                <p style={{ margin: 0, color: 'var(--aselco-ink-500)', fontSize: 13 }}>
                  AST deducted from your wallet. Posting takes up to 24 hours.
                </p>
                <p className="balance-card__amount" style={{ color: 'var(--aselco-ink-900)', marginTop: 14 }}>
                  {tokens(paidWithTokens)}
                </p>
              </section>

              <SectionHeader title="Details" />
              <div className="soft-card">
                <div className="kv">
                  <span className="kv__k">Reference no.</span>
                  <span className="kv__v">{ref}</span>
                </div>
                <div className="kv">
                  <span className="kv__k">Paid with</span>
                  <span className="kv__v">ASELCO Tokens (AST)</span>
                </div>
                <div className="kv">
                  <span className="kv__k">Account</span>
                  <span className="kv__v">{member.accountNo}</span>
                </div>
                <div className="kv">
                  <span className="kv__k">Wallet left</span>
                  <span className="kv__v">{tokens(walletBalance)}</span>
                </div>
                <div className="kv">
                  <span className="kv__k">Bill remaining</span>
                  <span className="kv__v">{peso(Math.max(0, billing.amountDue - paidWithTokens))}</span>
                </div>
              </div>

              <IonButton
                expand="block"
                className="soft-btn ion-margin-top"
                onClick={() => router.push('/tabs/ledger')}
              >
                View ledger
              </IonButton>
              <IonButton expand="block" fill="outline" className="soft-btn" onClick={reset}>
                Make another payment
              </IonButton>
            </>
          )}
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Pay;
