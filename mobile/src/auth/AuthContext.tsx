import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
  type ReactNode,
} from 'react';
import * as authApi from '../api/auth';
import { ApiError, type AuthUser, type LoginPayload, type RegisterPayload } from '../api/types';
import { clearLedgerCache } from '../api/ledgerStorage';
import { clearToken, getToken, setToken } from '../api/tokenStorage';
import { startPushRegistration, stopPushRegistration } from '../push/pushRegistration';

interface AuthContextValue {
  user: AuthUser | null;
  token: string | null;
  isLoading: boolean;
  isAuthenticated: boolean;
  signIn: (payload: LoginPayload) => Promise<void>;
  signUp: (payload: RegisterPayload) => Promise<{ message: string }>;
  signOut: () => Promise<void>;
  resendVerification: (email: string) => Promise<string>;
}

const AuthContext = createContext<AuthContextValue | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [token, setTokenState] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;

    (async () => {
      try {
        const stored = await getToken();
        if (!stored) {
          return;
        }

        const profile = await authApi.me(stored);
        if (cancelled) {
          return;
        }

        setTokenState(stored);
        setUser(profile);
        void startPushRegistration(stored);
      } catch {
        await clearToken();
        if (!cancelled) {
          setTokenState(null);
          setUser(null);
        }
      } finally {
        if (!cancelled) {
          setIsLoading(false);
        }
      }
    })();

    return () => {
      cancelled = true;
    };
  }, []);

  const signIn = useCallback(async (payload: LoginPayload) => {
    try {
      const result = await authApi.login(payload);
      await setToken(result.token);
      setTokenState(result.token);
      setUser(result.user);
      void startPushRegistration(result.token);
    } catch (err) {
      if (err instanceof ApiError) {
        throw err;
      }
      throw new ApiError('Unable to sign in. Check your connection.', 0);
    }
  }, []);

  const signUp = useCallback(async (payload: RegisterPayload) => {
    const result = await authApi.register(payload);
    return { message: result.message };
  }, []);

  const signOut = useCallback(async () => {
    const current = token ?? (await getToken());
    try {
      if (current) {
        await stopPushRegistration(current);
        await authApi.logout(current);
      }
    } catch {
      // Still clear local session if the API call fails (e.g. already revoked).
    } finally {
      await clearToken();
      clearLedgerCache();
      setTokenState(null);
      setUser(null);
    }
  }, [token]);

  const resendVerification = useCallback(async (email: string) => {
    const result = await authApi.resendVerification(email);
    return result.message;
  }, []);

  const value = useMemo<AuthContextValue>(
    () => ({
      user,
      token,
      isLoading,
      isAuthenticated: Boolean(token && user),
      signIn,
      signUp,
      signOut,
      resendVerification,
    }),
    [user, token, isLoading, signIn, signUp, signOut, resendVerification],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth(): AuthContextValue {
  const ctx = useContext(AuthContext);
  if (!ctx) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return ctx;
}
