import { IonIcon, IonLabel, IonRouterOutlet, IonTabBar, IonTabButton, IonTabs } from '@ionic/react';
import { documentTextOutline, homeOutline, personOutline, ticketOutline, walletOutline } from 'ionicons/icons';
import { Redirect, Route } from 'react-router-dom';
import Home from './Home';
import Ledger from './Ledger';
import Pay from './Pay';
import Profile from './Profile';
import Tickets from './Tickets';

/**
 * Bottom-tab shell. Tab routes live under /tabs/*.
 * Stack screens (notifications, complaints, support) are registered in App.tsx
 * so the tab bar is hidden on those pages.
 */
const MainTabs: React.FC = () => (
  <IonTabs>
    <IonRouterOutlet>
      <Route exact path="/tabs/home" component={Home} />
      <Route exact path="/tabs/ledger" component={Ledger} />
      <Route exact path="/tabs/pay" component={Pay} />
      <Route exact path="/tabs/tickets" component={Tickets} />
      <Route exact path="/tabs/profile" component={Profile} />
      <Route exact path="/tabs">
        <Redirect to="/tabs/home" />
      </Route>
    </IonRouterOutlet>

    <IonTabBar slot="bottom">
      <IonTabButton tab="home" href="/tabs/home">
        <IonIcon icon={homeOutline} />
        <IonLabel>Home</IonLabel>
      </IonTabButton>
      <IonTabButton tab="ledger" href="/tabs/ledger">
        <IonIcon icon={documentTextOutline} />
        <IonLabel>Ledger</IonLabel>
      </IonTabButton>
      <IonTabButton tab="pay" href="/tabs/pay" className="tab-pay" aria-label="Pay">
        <IonIcon icon={walletOutline} />
      </IonTabButton>
      <IonTabButton tab="tickets" href="/tabs/tickets">
        <IonIcon icon={ticketOutline} />
        <IonLabel>Tickets</IonLabel>
      </IonTabButton>
      <IonTabButton tab="profile" href="/tabs/profile">
        <IonIcon icon={personOutline} />
        <IonLabel>Profile</IonLabel>
      </IonTabButton>
    </IonTabBar>
  </IonTabs>
);

export default MainTabs;
