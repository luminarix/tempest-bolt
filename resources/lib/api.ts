import type { BoltBootstrap, ExecuteResponse } from './types'

export function bootstrap(): BoltBootstrap {
  const config = window.__BOLT__
  if (!config) {
    throw new Error('Bolt was not bootstrapped: window.__BOLT__ is missing.')
  }
  return config
}

export async function execute(code: string, config: BoltBootstrap): Promise<ExecuteResponse> {
  const response = await fetch(config.executeUrl, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
    body: JSON.stringify({ code }),
  })

  if (!response.ok) {
    const text = await response.text().catch(() => '')
    throw new Error(`Bolt request failed (${response.status}). ${text}`.trim())
  }

  return (await response.json()) as ExecuteResponse
}
