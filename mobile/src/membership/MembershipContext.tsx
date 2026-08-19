import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
  type ReactNode,
} from 'react';
import * as membershipApi from '../api/membership';
import type {
  AccountLink,
  LinkedAccount,
  MembershipStatus,
  StoreAccountLinkPayload,
  StoreAccountLinkResponse,
} from '../api/types';
import { useAuth } from '../auth/AuthContext';

interface MembershipContextValue {
  status: MembershipStatus | null;
  links: AccountLink[];
  linkedAccounts: LinkedAccount[];
  isLoading: boolean;
  linksLoading: boolean;
  needsMembershipStepper: boolean;
  linkCount: number;
  canAddAnotherLink: boolean;
  refreshStatus: () => Promise<MembershipStatus | null>;
  refreshLinks: () => Promise<AccountLink[]>;
  refreshLinkedAccounts: () => Promise<LinkedAccount[]>;
  submitLink: (payload: StoreAccountLinkPayload) => Promise<StoreAccountLinkResponse>;
  markStepperComplete: () => void;
}

const MembershipContext = createContext<MembershipContextValue | undefined>(undefined);

const emptyStatus = (): MembershipStatus => ({
  needs_membership_stepper: true,
  has_pending_link: false,
  has_validated_link: false,
  pending_count: 0,
  validated_count: 0,
  link_count: 0,
  max_links: 2,
  can_add_another_link: true,
});

export function MembershipProvider({ children }: { children: ReactNode }) {
  const { token, isAuthenticated } = useAuth();
  const [status, setStatus] = useState<MembershipStatus | null>(null);
  const [links, setLinks] = useState<AccountLink[]>([]);
  const [linkedAccounts, setLinkedAccounts] = useState<LinkedAccount[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [linksLoading, setLinksLoading] = useState(false);

  const refreshLinks = useCallback(async () => {
    if (!token) {
      setLinks([]);
      return [];
    }

    setLinksLoading(true);
    try {
      const result = await membershipApi.listAccountLinks(token);
      const next = Array.isArray(result.data) ? result.data : [];
      setLinks(next);
      return next;
    } catch {
      return [];
    } finally {
      setLinksLoading(false);
    }
  }, [token]);

  const refreshLinkedAccounts = useCallback(async () => {
    if (!token) {
      setLinkedAccounts([]);
      return [];
    }

    try {
      const result = await membershipApi.listLinkedAccounts(token);
      const next = Array.isArray(result.data) ? result.data : [];
      setLinkedAccounts(next);
      return next;
    } catch {
      return [];
    }
  }, [token]);

  const refreshStatus = useCallback(async () => {
    if (!token) {
      setStatus(null);
      setLinks([]);
      setLinkedAccounts([]);
      return null;
    }

    // Do not toggle boot `isLoading` when status already exists (avoids full-app spinner loop).
    const isInitial = status === null;
    if (isInitial) {
      setIsLoading(true);
    }

    try {
      const next = await membershipApi.getMembershipStatus(token);
      setStatus(next);
      return next;
    } catch {
      setStatus((prev) => {
        if (prev && prev.needs_membership_stepper === false) {
          return prev;
        }
        return emptyStatus();
      });
      return null;
    } finally {
      if (isInitial) {
        setIsLoading(false);
      }
    }
  }, [token, status]);

  useEffect(() => {
    if (!isAuthenticated || !token) {
      setStatus(null);
      setLinks([]);
      setLinkedAccounts([]);
      setIsLoading(false);
      setLinksLoading(false);
      return;
    }

    let cancelled = false;
    setIsLoading(true);
    setLinksLoading(true);

    (async () => {
      try {
        const [nextStatus, nextLinks, nextLinked] = await Promise.all([
          membershipApi.getMembershipStatus(token),
          membershipApi.listAccountLinks(token),
          membershipApi.listLinkedAccounts(token).catch(() => ({ data: [] as LinkedAccount[] })),
        ]);
        if (!cancelled) {
          setStatus(nextStatus);
          setLinks(Array.isArray(nextLinks.data) ? nextLinks.data : []);
          setLinkedAccounts(Array.isArray(nextLinked.data) ? nextLinked.data : []);
        }
      } catch {
        if (!cancelled) {
          setStatus(emptyStatus());
          setLinks([]);
          setLinkedAccounts([]);
        }
      } finally {
        if (!cancelled) {
          setIsLoading(false);
          setLinksLoading(false);
        }
      }
    })();

    return () => {
      cancelled = true;
    };
  }, [isAuthenticated, token]);

  const submitLink = useCallback(
    async (payload: StoreAccountLinkPayload) => {
      if (!token) {
        throw new Error('Not authenticated');
      }

      const result = await membershipApi.submitAccountLink(token, payload);

      setStatus((prev) => ({
        needs_membership_stepper: false,
        has_pending_link: true,
        has_validated_link: prev?.has_validated_link ?? false,
        pending_count: (prev?.pending_count ?? 0) + 1,
        validated_count: prev?.validated_count ?? 0,
        link_count: result.link_count ?? (prev?.link_count ?? 0) + 1,
        max_links: result.max_links ?? 2,
        can_add_another_link: result.can_add_another_link ?? false,
      }));

      if (result.account_link) {
        setLinks((prev) => [result.account_link, ...prev.filter((l) => l.id !== result.account_link.id)]);
      }

      void refreshStatus();
      void membershipApi.listAccountLinks(token).then((res) => setLinks(res.data ?? []));

      return result;
    },
    [token, refreshStatus],
  );

  const markStepperComplete = useCallback(() => {
    setStatus((prev) =>
      prev
        ? { ...prev, needs_membership_stepper: false }
        : {
            ...emptyStatus(),
            needs_membership_stepper: false,
            can_add_another_link: false,
          },
    );
  }, []);

  const linkCount = status?.link_count ?? links.length;
  const canAddAnotherLink = status?.can_add_another_link ?? linkCount < 2;

  const value = useMemo<MembershipContextValue>(
    () => ({
      status,
      links,
      linkedAccounts,
      isLoading,
      linksLoading,
      needsMembershipStepper: Boolean(isAuthenticated && status?.needs_membership_stepper),
      linkCount,
      canAddAnotherLink,
      refreshStatus,
      refreshLinks,
      refreshLinkedAccounts,
      submitLink,
      markStepperComplete,
    }),
    [
      status,
      links,
      linkedAccounts,
      isLoading,
      linksLoading,
      isAuthenticated,
      linkCount,
      canAddAnotherLink,
      refreshStatus,
      refreshLinks,
      refreshLinkedAccounts,
      submitLink,
      markStepperComplete,
    ],
  );

  return <MembershipContext.Provider value={value}>{children}</MembershipContext.Provider>;
}

export function useMembership(): MembershipContextValue {
  const ctx = useContext(MembershipContext);
  if (!ctx) {
    throw new Error('useMembership must be used within MembershipProvider');
  }
  return ctx;
}
