export type ThemeMode = 'light' | 'dark' | 'system'

const STORAGE_KEY = 'bolt:theme'
const MODES: ThemeMode[] = ['light', 'dark', 'system']

export function getStoredMode(): ThemeMode {
  const value = localStorage.getItem(STORAGE_KEY)
  return MODES.includes(value as ThemeMode) ? (value as ThemeMode) : 'system'
}

export function storeMode(mode: ThemeMode): void {
  localStorage.setItem(STORAGE_KEY, mode)
}

export function prefersDark(): boolean {
  return window.matchMedia('(prefers-color-scheme: dark)').matches
}

export function isDark(mode: ThemeMode): boolean {
  return mode === 'dark' || (mode === 'system' && prefersDark())
}

export function applyTheme(mode: ThemeMode): void {
  document.documentElement.classList.toggle('dark', isDark(mode))
}

export function watchSystem(callback: () => void): () => void {
  const query = window.matchMedia('(prefers-color-scheme: dark)')
  query.addEventListener('change', callback)
  return () => query.removeEventListener('change', callback)
}
