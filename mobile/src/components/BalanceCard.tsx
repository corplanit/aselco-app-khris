import { IonIcon } from '@ionic/react';
import { documentTextOutline, flashOutline, walletOutline } from 'ionicons/icons';
import { TokenWallet } from '../data/mockData';
import { peso } from '../utils/format';

export interface AccountBalanceRow {
  accountNumber: string;
  ownerName: string | null;
  currentBalance: number | null;
  dueDate: string | null;
}

interface BalanceCardProps {
  wallet: TokenWallet;
  walletIsDemo?: boolean;
  accountBalances?: AccountBalanceRow[];
}

function tokens(amount: number): string {
  return `${amount.toLocaleString('en-PH', { minimumFractionDigits: 2 })} AST`;
}

/**
 * Home dashboard hero cards:
 * 1) Remaining AST (primary)
 * 2) One ledger card — total current balance + per-account breakdown
 */
const BalanceCard: React.FC<BalanceCardProps> = ({
  wallet,
  walletIsDemo = true,
  accountBalances = [],
}) => {
  const known = accountBalances.filter((row) => row.currentBalance != null);
  const total = known.reduce((sum, row) => sum + (row.currentBalance ?? 0), 0);
  const hasTotal = known.length > 0;

  return (
    <div className="dash-cards">
      <section className="dash-card dash-card--ast" aria-label="Remaining ASELCO Tokens">
        <div className="dash-card__top">
          <span className="dash-card__badge">
            <IonIcon icon={walletOutline} />
            Wallet
          </span>
          {walletIsDemo ? <span className="dash-card__chip">Demo</span> : null}
        </div>
        <p className="dash-card__label">Remaining AST</p>
        <p className="dash-card__amount">{tokens(wallet.balance)}</p>
        <p className="dash-card__meta">
          ASELCO Token · 1 AST ≈ ₱1 · only payment method
          {walletIsDemo ? ' · demo wallet' : ''}
        </p>
      </section>

      <section className="dash-card dash-card--bill" aria-label="Ledger current balance">
        <div className="dash-card__top">
          <span className="dash-card__badge dash-card__badge--muted">
            <IonIcon icon={documentTextOutline} />
            Ledger
          </span>
          <span className="dash-card__chip dash-card__chip--muted">
            {accountBalances.length > 0
              ? `${accountBalances.length} account${accountBalances.length === 1 ? '' : 's'}`
              : 'No account'}
          </span>
        </div>
        <p className="dash-card__label dash-card__label--dark">Total current balance</p>
        <p className="dash-card__amount dash-card__amount--dark">{hasTotal ? peso(total) : '—'}</p>

        {accountBalances.length === 0 ? (
          <p className="dash-card__meta dash-card__meta--dark">No linked account yet.</p>
        ) : (
          <ul className="dash-account-totals">
            {accountBalances.map((row) => (
              <li key={row.accountNumber} className="dash-account-totals__row">
                <span className="dash-account-totals__icon" aria-hidden>
                  <IonIcon icon={flashOutline} />
                </span>
                <span className="dash-account-totals__copy">
                  <span className="dash-account-totals__acct">{row.accountNumber}</span>
                  <span className="dash-account-totals__meta">
                    {row.ownerName || 'Electric account'}
                  </span>
                </span>
                <span className="dash-account-totals__amt">
                  {row.currentBalance != null ? peso(row.currentBalance) : '—'}
                </span>
              </li>
            ))}
          </ul>
        )}
      </section>
    </div>
  );
};

export default BalanceCard;
