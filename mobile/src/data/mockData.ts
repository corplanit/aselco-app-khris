/**
 * Mock data for the ASELCO UI lesson.
 * Pages read from this module only — swap for API responses later.
 */

export interface Member {
  name: string;
  initials: string;
  accountNo: string;
  meterNo: string;
  address: string;
  mobile: string;
  email: string;
  rateClass: string;
  memberSince: string;
}

export interface Billing {
  amountDue: number;
  dueDate: string;
  billingPeriod: string;
  statementNo: string;
  previousReading: number;
  presentReading: number;
  kwhUsed: number;
  breakdown: { label: string; amount: number }[];
}

export interface ActivityItem {
  id: string;
  icon: string;
  title: string;
  date: string;
  value: string;
  tone: 'in' | 'due' | 'pending';
  href: string;
}

export interface QuickAction {
  icon: string;
  label: string;
  href: string;
}

export interface LedgerItem {
  id: string;
  type: 'payment' | 'bill';
  title: string;
  date: string;
  ref: string;
  amount: number;
  kwh?: number;
}

export interface Ticket {
  id: string;
  subject: string;
  category: string;
  status: 'In Progress' | 'Resolved' | 'Open';
  filed: string;
  updated: string;
  note: string;
}

/** ASELCO token wallet (1 token ≈ ₱1 for this UI lesson) */
export interface TokenWallet {
  balance: number;
  unit: string;
}

export interface NotificationItem {
  id: string;
  title: string;
  body: string;
  date: string;
  unread: boolean;
}

export interface SupportInfo {
  hotline: string;
  mobile: string;
  email: string;
  office: string;
  hours: string;
  faqs: { q: string; a: string }[];
}

export const member: Member = {
  name: 'Juan Dela Cruz',
  initials: 'JD',
  accountNo: '0102-334455',
  meterNo: 'MTR-88213',
  address: 'Purok 3, Brgy. San Roque, Jala-Jala, Rizal',
  mobile: '+63 917 555 0134',
  email: 'juan.delacruz@email.com',
  rateClass: 'Residential',
  memberSince: '2016',
};

export const billing: Billing = {
  amountDue: 2345.67,
  dueDate: 'May 31, 2025',
  billingPeriod: 'Apr 01 – Apr 30, 2025',
  statementNo: 'SB-2025-04-77120',
  previousReading: 4821,
  presentReading: 5093,
  kwhUsed: 272,
  breakdown: [
    { label: 'Generation charge', amount: 1412.4 },
    { label: 'Transmission charge', amount: 246.15 },
    { label: 'Distribution charge', amount: 388.72 },
    { label: 'Government taxes', amount: 198.4 },
    { label: 'Universal charges', amount: 100.0 },
  ],
};

export const activity: ActivityItem[] = [
  {
    id: 'a1',
    icon: 'checkmarkCircle',
    title: 'Payment Received',
    date: 'May 10, 2025',
    value: '2,300.00 AST',
    tone: 'in',
    href: '/tabs/ledger',
  },
  {
    id: 'a2',
    icon: 'documentText',
    title: 'Billing Statement',
    date: 'May 01, 2025',
    value: '₱2,345.67',
    tone: 'due',
    href: '/tabs/ledger',
  },
  {
    id: 'a3',
    icon: 'alertCircle',
    title: 'Complaint Update',
    date: 'Apr 28, 2025',
    value: 'In Progress',
    tone: 'pending',
    href: '/tabs/tickets',
  },
];

export const quickActions: QuickAction[] = [
  { icon: 'wallet', label: 'Pay Now', href: '/tabs/pay' },
  { icon: 'documentText', label: 'My Ledger', href: '/tabs/ledger' },
  { icon: 'create', label: 'Complaints', href: '/complaints' },
  { icon: 'headset', label: 'Support', href: '/support' },
];

export const ledger: LedgerItem[] = [
  {
    id: 'L-0510',
    type: 'payment',
    title: 'Payment — ASELCO Tokens',
    date: 'May 10, 2025',
    ref: 'AST-88231774',
    amount: 2300.0,
  },
  {
    id: 'L-0501',
    type: 'bill',
    title: 'Billing Statement — April',
    date: 'May 01, 2025',
    ref: 'SB-2025-04-77120',
    amount: 2345.67,
    kwh: 272,
  },
  {
    id: 'L-0409',
    type: 'payment',
    title: 'Token load — Office',
    date: 'Apr 09, 2025',
    ref: 'AST-4471900',
    amount: 2118.4,
  },
  {
    id: 'L-0401',
    type: 'bill',
    title: 'Billing Statement — March',
    date: 'Apr 01, 2025',
    ref: 'SB-2025-03-70981',
    amount: 2118.4,
    kwh: 244,
  },
  {
    id: 'L-0312',
    type: 'payment',
    title: 'Token load — GCash',
    date: 'Mar 12, 2025',
    ref: 'AST-2210034',
    amount: 1987.25,
  },
  {
    id: 'L-0301',
    type: 'bill',
    title: 'Billing Statement — February',
    date: 'Mar 01, 2025',
    ref: 'SB-2025-02-64510',
    amount: 1987.25,
    kwh: 231,
  },
];

export const tickets: Ticket[] = [
  {
    id: 'TCK-1042',
    subject: 'Flickering lights on our line',
    category: 'Power Quality',
    status: 'In Progress',
    filed: 'Apr 28, 2025',
    updated: 'May 09, 2025',
    note: 'Line crew scheduled for inspection on May 12.',
  },
  {
    id: 'TCK-1027',
    subject: 'Billing amount looks higher than usual',
    category: 'Billing',
    status: 'Resolved',
    filed: 'Mar 18, 2025',
    updated: 'Mar 22, 2025',
    note: 'Re-reading confirmed. Adjustment applied to April bill.',
  },
  {
    id: 'TCK-0998',
    subject: 'Streetlight out at Purok 3 corner',
    category: 'Streetlight',
    status: 'Resolved',
    filed: 'Feb 02, 2025',
    updated: 'Feb 07, 2025',
    note: 'Lamp replaced by maintenance team.',
  },
];

export const complaintCategories = [
  'Power Interruption',
  'Power Quality',
  'Billing Dispute',
  'Meter Concern',
  'Streetlight',
  'Others',
];

/** Demo AST wallet — no live token API yet, default remaining is 0 */
export const tokenWallet: TokenWallet = {
  balance: 0,
  unit: 'AST', // ASELCO Token — only payment method
};

export const notifications: NotificationItem[] = [
  {
    id: 'n1',
    title: 'Scheduled interruption',
    body: 'Brgy. San Roque, May 18, 8:00 AM – 12:00 NN for line maintenance.',
    date: 'May 12, 2025',
    unread: true,
  },
  {
    id: 'n2',
    title: 'New bill available',
    body: 'Your April statement of ₱2,345.67 is ready. Due May 31.',
    date: 'May 01, 2025',
    unread: true,
  },
  {
    id: 'n3',
    title: 'Complaint TCK-1042 updated',
    body: 'A crew has been assigned to inspect your line.',
    date: 'Apr 30, 2025',
    unread: true,
  },
  {
    id: 'n4',
    title: 'Payment posted',
    body: '₱2,300.00 received. Thank you!',
    date: 'Apr 09, 2025',
    unread: false,
  },
];

export const support: SupportInfo = {
  hotline: '(085) 343-1234',
  mobile: '+63 918 900 4455',
  email: 'support@aselco.com.ph',
  office: 'ASELCO Main Office, Bayugan City',
  hours: 'Mon – Fri, 8:00 AM – 5:00 PM',
  faqs: [
    {
      q: 'How do I read my billing statement?',
      a: 'Your statement shows the previous and present meter readings, total kWh used, and each charge that makes up the amount due.',
    },
    {
      q: 'When is my due date?',
      a: 'Bills are issued on the 1st and are due on the last day of the same month. Late payments may incur a surcharge.',
    },
    {
      q: 'What do I do during an outage?',
      a: 'Check the notifications tab first for scheduled interruptions, then file a Power Interruption complaint if your area is not listed.',
    },
    {
      q: 'How long does a complaint take?',
      a: 'Most concerns are acted on within 3 working days. You can follow the progress under Tickets.',
    },
  ],
};

/** Totals derived for the ledger summary strip */
export const ledgerSummary = {
  currentDue: billing.amountDue,
  totalPaid: ledger.filter((l) => l.type === 'payment').reduce((s, l) => s + l.amount, 0),
  kwhUsed: billing.kwhUsed,
};
