import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
  type ReactNode,
} from 'react';
import { useAuth } from '../auth/AuthContext';
import { getOnboardingComplete, setOnboardingComplete } from './storage';

const SPLASH_MIN_MS = 1400;

interface OnboardingContextValue {
  isHydrated: boolean;
  splashMinElapsed: boolean;
  onboardingComplete: boolean;
  needsOnboarding: boolean;
  completeOnboarding: () => Promise<void>;
}

const OnboardingContext = createContext<OnboardingContextValue | undefined>(undefined);

export function OnboardingProvider({ children }: { children: ReactNode }) {
  const { isAuthenticated } = useAuth();
  const [isHydrated, setIsHydrated] = useState(false);
  const [splashMinElapsed, setSplashMinElapsed] = useState(false);
  const [onboardingComplete, setComplete] = useState(false);

  useEffect(() => {
    const timer = window.setTimeout(() => setSplashMinElapsed(true), SPLASH_MIN_MS);
    return () => window.clearTimeout(timer);
  }, []);

  useEffect(() => {
    let cancelled = false;

    (async () => {
      try {
        const done = await getOnboardingComplete();
        if (!cancelled) {
          setComplete(done);
        }
      } catch {
        if (!cancelled) {
          setComplete(false);
        }
      } finally {
        if (!cancelled) {
          setIsHydrated(true);
        }
      }
    })();

    return () => {
      cancelled = true;
    };
  }, []);

  const completeOnboarding = useCallback(async () => {
    await setOnboardingComplete();
    setComplete(true);
  }, []);

  useEffect(() => {
    if (!isAuthenticated || onboardingComplete || !isHydrated) {
      return;
    }
    // Existing / returning members skip walkthrough even after logout.
    void completeOnboarding();
  }, [isAuthenticated, onboardingComplete, isHydrated, completeOnboarding]);

  const value = useMemo<OnboardingContextValue>(
    () => ({
      isHydrated,
      splashMinElapsed,
      onboardingComplete,
      needsOnboarding: !isAuthenticated && !onboardingComplete,
      completeOnboarding,
    }),
    [isHydrated, splashMinElapsed, onboardingComplete, isAuthenticated, completeOnboarding],
  );

  return <OnboardingContext.Provider value={value}>{children}</OnboardingContext.Provider>;
}

export function useOnboarding(): OnboardingContextValue {
  const ctx = useContext(OnboardingContext);
  if (!ctx) {
    throw new Error('useOnboarding must be used within OnboardingProvider');
  }
  return ctx;
}
