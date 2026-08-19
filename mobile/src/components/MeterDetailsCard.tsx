import { IonIcon } from '@ionic/react';
import { speedometerOutline } from 'ionicons/icons';
import { displayOrDash } from '../utils/serviceAccount';

export interface MeterReadingSummary {
  meterNo?: string | null;
  previousReading?: number | null;
  presentReading?: number | null;
  kwh?: number | null;
  billMonthLabel?: string | null;
  demandKw?: number | null;
}

interface MeterDetailsCardProps {
  meter: MeterReadingSummary | null;
  loading?: boolean;
}

const MeterDetailsCard: React.FC<MeterDetailsCardProps> = ({ meter, loading }) => {
  return (
    <section className="service-card" aria-label="Meter details">
      <div className="service-card__top">
        <span className="dash-card__badge dash-card__badge--muted">
          <IonIcon icon={speedometerOutline} />
          Meter
        </span>
      </div>

      {loading && !meter ? (
        <p className="service-card__empty">Loading meter details…</p>
      ) : !meter ? (
        <p className="service-card__empty">
          Meter readings appear after your ledger loads for a linked account.
        </p>
      ) : (
        <>
          <div className="kv">
            <span className="kv__k">Meter no.</span>
            <span className="kv__v">{displayOrDash(meter.meterNo)}</span>
          </div>
          <div className="kv">
            <span className="kv__k">Previous reading</span>
            <span className="kv__v">
              {displayOrDash(meter.previousReading != null ? String(meter.previousReading) : null)}
            </span>
          </div>
          <div className="kv">
            <span className="kv__k">Present reading</span>
            <span className="kv__v">
              {displayOrDash(meter.presentReading != null ? String(meter.presentReading) : null)}
            </span>
          </div>
          <div className="kv">
            <span className="kv__k">kWh used</span>
            <span className="kv__v">{displayOrDash(meter.kwh != null ? String(meter.kwh) : null)}</span>
          </div>
          <div className="kv">
            <span className="kv__k">Demand (kW)</span>
            <span className="kv__v">
              {displayOrDash(meter.demandKw != null ? String(meter.demandKw) : null)}
            </span>
          </div>
          {meter.billMonthLabel ? (
            <p className="service-card__note">Latest bill period: {meter.billMonthLabel}</p>
          ) : null}
        </>
      )}
    </section>
  );
};

export default MeterDetailsCard;
