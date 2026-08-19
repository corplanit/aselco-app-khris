import {
  IonButton,
  IonContent,
  IonFooter,
  IonIcon,
  IonInput,
  IonPage,
  IonToolbar,
} from '@ionic/react';
import { chatbubbleEllipsesOutline, send } from 'ionicons/icons';
import { FormEvent, useRef, useState } from 'react';
import AppHeader from '../components/AppHeader';

interface ChatMessage {
  id: string;
  role: 'bot' | 'user';
  text: string;
}

const QUICK_PROMPTS = ['Billing Inquiry', 'Report Outage', 'Other Concerns'];

const INITIAL: ChatMessage[] = [
  { id: 'b1', role: 'bot', text: "Hello! I'm your AI Assistant." },
  { id: 'b2', role: 'bot', text: 'How can I help you today?' },
];

function botReply(prompt: string): string {
  const p = prompt.toLowerCase();
  if (p.includes('billing')) {
    return 'Your current amount due is on Home. You can pay with ASELCO Tokens (AST) from the Pay tab.';
  }
  if (p.includes('outage')) {
    return 'For outages, check Notifications for scheduled interruptions, or file a Power Interruption complaint under Tickets.';
  }
  if (p.includes('other') || p.includes('concern')) {
    return 'You can file a complaint from Tickets, or open Support for FAQ and contact channels.';
  }
  return 'Thanks for your message. This is a UI demo — connect an API later for real answers.';
}

/**
 * Soft chat UI matching the ASELCO AI Assistant mock.
 * Local replies only — no API yet.
 */
const Assistant: React.FC = () => {
  const [messages, setMessages] = useState<ChatMessage[]>(INITIAL);
  const [draft, setDraft] = useState('');
  const bottomRef = useRef<HTMLDivElement>(null);

  const scrollToEnd = () => {
    requestAnimationFrame(() => bottomRef.current?.scrollIntoView({ behavior: 'smooth' }));
  };

  const sendMessage = (text: string) => {
    const trimmed = text.trim();
    if (!trimmed) return;

    const userMsg: ChatMessage = {
      id: `u-${Date.now()}`,
      role: 'user',
      text: trimmed,
    };
    const reply: ChatMessage = {
      id: `b-${Date.now() + 1}`,
      role: 'bot',
      text: botReply(trimmed),
    };

    setMessages((prev) => [...prev, userMsg, reply]);
    setDraft('');
    scrollToEnd();
  };

  const onSubmit = (e: FormEvent) => {
    e.preventDefault();
    sendMessage(draft);
  };

  return (
    <IonPage>
      <AppHeader title="AI Assistant" icon={chatbubbleEllipsesOutline} backHref="/tabs/home" />
      <IonContent className="assistant-content">
        <div className="assistant-shell page-pad">
          <div className="assistant-card">
            <div className="assistant-card__head">
              <div className="assistant-avatar" aria-hidden>
                <span className="assistant-avatar__face">🤖</span>
                <span className="assistant-avatar__dot" />
              </div>
              <h1 className="assistant-card__title">AI ASSISTANT</h1>
            </div>

            <div className="assistant-messages">
              {messages.map((m) => (
                <div
                  key={m.id}
                  className={`chat-bubble ${m.role === 'user' ? 'chat-bubble--user' : 'chat-bubble--bot'}`}
                >
                  {m.text}
                </div>
              ))}
              <div ref={bottomRef} />
            </div>

            <div className="assistant-quick">
              {QUICK_PROMPTS.map((label) => (
                <button
                  key={label}
                  type="button"
                  className="assistant-chip"
                  onClick={() => sendMessage(label)}
                >
                  {label}
                </button>
              ))}
            </div>
          </div>
        </div>
      </IonContent>

      <IonFooter className="assistant-footer">
        <IonToolbar>
          <form className="assistant-composer" onSubmit={onSubmit}>
            <IonInput
              className="assistant-composer__input"
              value={draft}
              placeholder="Type your message..."
              onIonInput={(e) => setDraft(String(e.detail.value ?? ''))}
            />
            <IonButton
              type="submit"
              className="assistant-send"
              aria-label="Send"
              disabled={!draft.trim()}
            >
              <IonIcon slot="icon-only" icon={send} />
            </IonButton>
          </form>
        </IonToolbar>
      </IonFooter>
    </IonPage>
  );
};

export default Assistant;
