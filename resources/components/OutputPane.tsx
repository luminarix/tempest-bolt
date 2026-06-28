import type { ExecuteResponse } from '../lib/types'

export interface RunState {
  response: ExecuteResponse | null
  pending: boolean
  failed?: string
}

interface OutputPaneProps {
  run: RunState | null
}

export function OutputPane({ run }: OutputPaneProps) {
  if (run === null) {
    return (
      <div className="flex h-full items-center justify-center text-sm text-faint">
        <div className="text-center">
          <p className="font-medium text-accent">Bolt</p>
          <p className="mt-1">
            Write PHP, then press{' '}
            <kbd className="rounded bg-black/5 px-1.5 py-0.5 text-xs dark:bg-white/10">⌘/Ctrl</kbd>{' '}
            +{' '}
            <kbd className="rounded bg-black/5 px-1.5 py-0.5 text-xs dark:bg-white/10">Enter</kbd>{' '}
            to run.
          </p>
        </div>
      </div>
    )
  }

  if (run.pending) {
    return (
      <div className="flex h-full items-center justify-center text-sm text-faint italic">
        Running…
      </div>
    )
  }

  return (
    <div className="h-full overflow-y-auto p-4 font-mono text-sm">
      {run.failed && (
        <pre className="whitespace-pre-wrap break-words text-rose-600 dark:text-rose-400">
          {run.failed}
        </pre>
      )}

      {run.response && (
        <div className="space-y-1">
          {run.response.output && (
            <pre className="whitespace-pre-wrap break-words text-ink">{run.response.output}</pre>
          )}
          {run.response.error ? (
            <pre className="whitespace-pre-wrap break-words text-rose-600 dark:text-rose-400">
              {run.response.error}
            </pre>
          ) : (
            run.response.result !== null && (
              <pre className="whitespace-pre-wrap break-words text-accent">
                = {run.response.result}
              </pre>
            )
          )}
          <p className="text-xs text-faint">{run.response.durationMs} ms</p>
        </div>
      )}
    </div>
  )
}
