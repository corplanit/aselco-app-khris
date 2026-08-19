import { Preferences } from '@capacitor/preferences';

export const ONBOARDING_COMPLETE_KEY = 'aselco_onboarding_complete';

export async function getOnboardingComplete(): Promise<boolean> {
  const { value } = await Preferences.get({ key: ONBOARDING_COMPLETE_KEY });
  return value === '1' || value === 'true';
}

export async function setOnboardingComplete(): Promise<void> {
  await Preferences.set({ key: ONBOARDING_COMPLETE_KEY, value: '1' });
}
