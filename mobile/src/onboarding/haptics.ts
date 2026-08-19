import { Haptics, ImpactStyle } from '@capacitor/haptics';

export async function lightHaptic(): Promise<void> {
  try {
    await Haptics.impact({ style: ImpactStyle.Light });
  } catch {
    // Web and unsupported platforms ignore haptics.
  }
}
