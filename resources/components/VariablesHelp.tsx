import type { BoltVariable } from '../lib/types'

interface VariablesHelpProps {
  variables: BoltVariable[]
}

export function VariablesHelp({ variables }: VariablesHelpProps) {
  if (variables.length === 0) {
    return null
  }

  return (
    <div className="group relative">
      <button
        type="button"
        aria-label="Built-in variables"
        className="flex size-5 cursor-help items-center justify-center rounded-full border border-line text-xs text-faint transition-colors hover:border-accent hover:text-accent"
      >
        ?
      </button>
      <div
        role="tooltip"
        className="invisible absolute left-0 top-7 z-10 w-72 rounded-md border border-line bg-raised p-3 text-left opacity-0 shadow-lg transition-opacity group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100"
      >
        <p className="mb-2 text-xs font-medium text-ink">Variables in scope</p>
        <dl className="space-y-1.5">
          {variables.map((variable) => (
            <div key={variable.name}>
              <dt className="font-mono text-xs text-accent">{variable.name}</dt>
              {variable.description && (
                <dd className="text-xs text-faint">{variable.description}</dd>
              )}
            </div>
          ))}
        </dl>
      </div>
    </div>
  )
}
