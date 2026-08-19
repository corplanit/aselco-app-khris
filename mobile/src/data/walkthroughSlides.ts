export interface WalkthroughSlide {
  id: string;
  icon: string;
  title: string;
  body: string;
}

export const walkthroughSlides: WalkthroughSlide[] = [
  {
    id: 'pay',
    icon: 'wallet',
    title: 'Pay with AST',
    body: 'Settle your electric bill using ASELCO Tokens — the only payment method in the member app.',
  },
  {
    id: 'ledger',
    icon: 'documentText',
    title: 'Your ledger, in one place',
    body: 'Review billing statements, payments, and kWh usage without visiting the office.',
  },
  {
    id: 'complaints',
    icon: 'create',
    title: 'File concerns faster',
    body: 'Report outages, power quality, or billing questions and track tickets from your phone.',
  },
  {
    id: 'support',
    icon: 'headset',
    title: 'Help when you need it',
    body: 'Open Support, FAQs, or the AI assistant from Home — and get outage alerts on the bell.',
  },
];
