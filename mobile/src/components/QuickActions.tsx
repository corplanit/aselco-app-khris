import { IonIcon } from '@ionic/react';
import { Link } from 'react-router-dom';
import {
  createOutline,
  documentTextOutline,
  headsetOutline,
  walletOutline,
} from 'ionicons/icons';
import { QuickAction } from '../data/mockData';

const iconMap: Record<string, string> = {
  wallet: walletOutline,
  documentText: documentTextOutline,
  create: createOutline,
  headset: headsetOutline,
};

interface QuickActionsProps {
  actions: QuickAction[];
}

const QuickActions: React.FC<QuickActionsProps> = ({ actions }) => (
  <div className="quick-grid">
    {actions.map((a) => (
      <Link key={a.label} className="quick-action" to={a.href}>
        <span className="quick-action__icon">
          <IonIcon icon={iconMap[a.icon] || documentTextOutline} />
        </span>
        <span className="quick-action__label">{a.label}</span>
      </Link>
    ))}
  </div>
);

export default QuickActions;
