export interface BoltVariable {
  name: string
  description: string
}

export interface BoltBootstrap {
  executeUrl: string
  tempestVersion: string
  variables: BoltVariable[]
}

export interface ExecuteResponse {
  output: string
  result: string | null
  error: string | null
  durationMs: number
}

declare global {
  interface Window {
    __BOLT__?: BoltBootstrap
  }
}
