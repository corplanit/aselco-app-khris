import {
  IonBackButton,
  IonBadge,
  IonButtons,
  IonHeader,
  IonIcon,
  IonTitle,
  IonToolbar,
} from '@ionic/react';
import { chatbubbleEllipsesOutline, notificationsOutline } from 'ionicons/icons';
import logoTag from '../assets/logo_tag.png';

interface AppHeaderProps {
  /** Page title shown when showLogo is false */
  title?: string;
  /** Ionicon for the page (shown beside the title) */
  icon?: string;
  /** Show ASELCO logo instead of title (Home) */
  showLogo?: boolean;
  /** Show back button (stack pages) */
  backHref?: string;
  /** AI Assistant button (before notifications) */
  assistant?: boolean;
  onAssistantClick?: () => void;
  /** Bell with unread badge (Home) */
  bell?: boolean;
  unreadCount?: number;
  onBellClick?: () => void;
}

/**
 * Shared white navbar. Home shows the brand logo;
 * other pages show an icon + title; stack pages get a back button.
 * Home can show AI Assistant then Notifications on the right.
 */
const AppHeader: React.FC<AppHeaderProps> = ({
  title,
  icon,
  showLogo = false,
  backHref,
  assistant = false,
  onAssistantClick,
  bell = false,
  unreadCount = 0,
  onBellClick,
}) => (
  <IonHeader>
    <IonToolbar className="aselco-toolbar">
      {backHref && (
        <IonButtons slot="start">
          <IonBackButton defaultHref={backHref} />
        </IonButtons>
      )}

      {showLogo ? (
        <IonTitle>
          <img src={logoTag} alt="ASELCO" className="aselco-header-logo" />
        </IonTitle>
      ) : (
        <IonTitle>
          <span className="aselco-header-title-row">
            {icon && <IonIcon icon={icon} className="aselco-header-icon" aria-hidden />}
            <span className="aselco-header-title">{title}</span>
          </span>
        </IonTitle>
      )}

      {(assistant || bell) && (
        <IonButtons slot="end">
          {assistant && (
            <button
              type="button"
              className="header-icon-btn"
              aria-label="AI Assistant"
              onClick={onAssistantClick}
            >
              <IonIcon icon={chatbubbleEllipsesOutline} />
            </button>
          )}
          {bell && (
            <button
              type="button"
              className="header-icon-btn"
              aria-label="Notifications"
              onClick={onBellClick}
            >
              <IonIcon icon={notificationsOutline} />
              {unreadCount > 0 && (
                <IonBadge
                  color="danger"
                  style={{ position: 'absolute', top: 2, right: 2, fontSize: 10 }}
                >
                  {unreadCount}
                </IonBadge>
              )}
            </button>
          )}
        </IonButtons>
      )}
    </IonToolbar>
  </IonHeader>
);

export default AppHeader;
