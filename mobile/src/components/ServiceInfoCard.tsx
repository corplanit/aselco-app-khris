import { IonIcon } from '@ionic/react';
import { flashOutline } from 'ionicons/icons';
import type { ServiceInfo } from '../api/types';
import StatusPill from './StatusPill';
import { displayOrDash, formatLinkStatus } from '../utils/serviceAccount';

interface ServiceInfoCardProps {
  service: ServiceInfo | null;
  loading?: boolean;
}

const ServiceInfoCard: React.FC<ServiceInfoCardProps> = ({ service, loading }) => {
  const pending = service?.status?.toLowerCase() === 'pending';

  return (
    <section className="service-card" aria-label="Service information">
      <div className="service-card__top">
        <span className="dash-card__badge dash-card__badge--muted">
          <IonIcon icon={flashOutline} />
          Service
        </span>
        {service ? <StatusPill status={formatLinkStatus(service.status)} /> : null}
      </div>

      {loading && !service ? (
        <p className="service-card__empty">Loading service account…</p>
      ) : !service ? (
        <p className="service-card__empty">
          No electric account linked yet. Complete membership setup to see service details.
        </p>
      ) : (
        <>
          <div className="kv">
            <span className="kv__k">Account no.</span>
            <span className="kv__v">{displayOrDash(service.account_number)}</span>
          </div>
          <div className="kv">
            <span className="kv__k">Registered owner</span>
            <span className="kv__v">{displayOrDash(service.owner_name)}</span>
          </div>
          <div className="kv">
            <span className="kv__k">Meter no.</span>
            <span className="kv__v">{displayOrDash(service.meter_no)}</span>
          </div>
          <div className="kv">
            <span className="kv__k">Rate class</span>
            <span className="kv__v">{displayOrDash(service.rate_class)}</span>
          </div>
          <div className="kv">
            <span className="kv__k">Service address</span>
            <span className="kv__v">{displayOrDash(service.address)}</span>
          </div>
          {pending ? (
            <p className="service-card__note">Pending staff validation against cooperative records.</p>
          ) : null}
        </>
      )}
    </section>
  );
};

export default ServiceInfoCard;
