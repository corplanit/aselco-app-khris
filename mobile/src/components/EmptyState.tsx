import { IonIcon } from '@ionic/react';
import { fileTrayOutline } from 'ionicons/icons';

interface EmptyStateProps {
  title?: string;
  message?: string;
}

const EmptyState: React.FC<EmptyStateProps> = ({
  title = 'Nothing here yet',
  message = 'When there is activity, it will show up in this list.',
}) => (
  <div className="empty-state">
    <IonIcon icon={fileTrayOutline} style={{ fontSize: 40, color: 'var(--aselco-ink-400)' }} />
    <p className="empty-state__title">{title}</p>
    <p>{message}</p>
  </div>
);

export default EmptyState;
