import { useCallback, useEffect, useRef, useState } from 'react'
import { Editor } from './components/Editor'
import { OutputPane, type RunState } from './components/OutputPane'
import { VariablesHelp } from './components/VariablesHelp'
import { ThemeSwitcher } from './components/ThemeSwitcher'
import { bootstrap, execute } from './lib/api'
import { applyTheme, getStoredMode, isDark, prefersDark, storeMode, watchSystem, type ThemeMode } from './lib/theme'

const STORAGE_KEY = 'bolt:last-code'

export function App() {
  const config = useRef(bootstrap()).current
  const code = useRef<string>(localStorage.getItem(STORAGE_KEY) ?? '')
  const [run, setRun] = useState<RunState | null>(null)
  const [running, setRunning] = useState(false)

  const [mode, setMode] = useState<ThemeMode>(getStoredMode)
  const [dark, setDark] = useState<boolean>(() => isDark(getStoredMode()))

  useEffect(() => {
    storeMode(mode)
    applyTheme(mode)
    setDark(isDark(mode))

    if (mode !== 'system') {
      return
    }

    return watchSystem(() => {
      applyTheme('system')
      setDark(prefersDark())
    })
  }, [mode])

  const handleChange = useCallback((value: string) => {
    code.current = value
    localStorage.setItem(STORAGE_KEY, value)
  }, [])

  const runCode = useCallback(async () => {
    const source = code.current.trim()
    if (source === '' || running) {
      return
    }

    setRunning(true)
    setRun({ response: null, pending: true })

    try {
      const response = await execute(source, config)
      setRun({ response, pending: false })
    } catch (error) {
      setRun({
        response: null,
        pending: false,
        failed: error instanceof Error ? error.message : String(error),
      })
    } finally {
      setRunning(false)
    }
  }, [config, running])

  return (
    <div className="flex h-screen flex-col bg-surface text-ink">
      <header className="flex items-center justify-between border-b border-line px-4 py-2.5">
        <div className="flex items-center gap-2">
          <span className="font-semibold tracking-tight">Bolt</span>
          <span className="text-xs text-faint">Tempest {config.tempestVersion}</span>
          <VariablesHelp variables={config.variables} />
        </div>
        <div className="flex items-center gap-3">
          <ThemeSwitcher mode={mode} onChange={setMode} />
          {run !== null && !running && (
            <button
              onClick={() => setRun(null)}
              className="text-xs text-faint transition-colors hover:text-ink"
            >
              Clear
            </button>
          )}
          <button
            onClick={runCode}
            disabled={running}
            className="rounded-md bg-accent px-3 py-1.5 text-sm font-medium text-white transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50 dark:text-brand-surface"
          >
            {running ? 'Running…' : 'Run'}
          </button>
        </div>
      </header>

      <main className="grid min-h-0 flex-1 grid-cols-1 md:grid-cols-2">
        <section className="min-h-0 border-line md:border-r">
          <Editor initialValue={code.current} dark={dark} onChange={handleChange} onSubmit={runCode} />
        </section>
        <section className="min-h-0 bg-raised">
          <OutputPane run={run} />
        </section>
      </main>
    </div>
  )
}
