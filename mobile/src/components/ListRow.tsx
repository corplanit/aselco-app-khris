import { IonIcon, IonItem, IonLabel } from '@ionic/react';
import { chevronForward } from 'ionicons/icons';

interface ListRowProps {
  icon?: string;
  title: string;
  meta?: string;
  value?: string;
  tone?: 'in' | 'due' | 'pending';
  href?: string;
  detail?: boolean;
  onClick?: () => void;
}

const ListRow: React.FC<ListRowProps> = ({
  icon,
  title,
  meta,
  value,
  tone = 'due',
  href,
  detail = true,
  onClick,
}) => {
  const toneClass =
    tone === 'in' ? 'value--in' : tone === 'pending' ? 'value--pending' : 'value--due';

  return (
    <IonItem
      button={!!(href || onClick)}
      detail={false}
      routerLink={href}
      onClick={onClick}
      lines="full"
    >
      {icon && <IonIcon slot="start" icon={icon} color="primary" />}
      <IonLabel>
        <h3>{title}</h3>
        {meta && <p>{meta}</p>}
      </IonLabel>
      {value && <span className={toneClass} slot="end">{value}</span>}
      {detail && (href || onClick) && (
        <IonIcon slot="end" icon={chevronForward} color="medium" style={{ marginLeft: 6 }} />
      )}
    </IonItem>
  );
};

export default ListRow;
