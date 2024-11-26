#!/usr/bin/env tsx
// Reference Smart CDN (https://transloadit.com/services/content-delivery/) Signature implementation
// And CLI tester to see if PHP implementation
// matches Node's

/// <reference types="node" />

import { createHash, createHmac } from 'crypto'

interface SmartCDNParams {
  workspace: string
  template: string
  input: string
  expire_at_ms?: number
  expire_in_ms?: number
  auth_key?: string
  auth_secret?: string
  url_params?: Record<string, any>
}

function signSmartCDNUrl(params: SmartCDNParams): string {
  const {
    workspace,
    template,
    input,
    expire_at_ms,
    expire_in_ms,
    auth_key,
    auth_secret,
    url_params = {},
  } = params

  if (!workspace) throw new Error('workspace is required')
  if (!template) throw new Error('template is required')
  if (input === null || input === undefined)
    throw new Error('input must be a string')
  if (!auth_key) throw new Error('auth_key is required')
  if (!auth_secret) throw new Error('auth_secret is required')

  const workspaceSlug = encodeURIComponent(workspace)
  const templateSlug = encodeURIComponent(template)
  const inputField = encodeURIComponent(input)

  const expireAt =
    expire_at_ms ??
    (expire_in_ms ? Date.now() + expire_in_ms : Date.now() + 60 * 60 * 1000) // 1 hour default

  const queryParams: Record<string, string[]> = {}

  // Handle url_params
  Object.entries(url_params).forEach(([key, value]) => {
    if (value === null || value === undefined) return
    if (Array.isArray(value)) {
      value.forEach((val) => {
        if (val === null || val === undefined) return
        ;(queryParams[key] ||= []).push(String(val))
      })
    } else {
      queryParams[key] = [String(value)]
    }
  })

  queryParams.auth_key = [auth_key]
  queryParams.exp = [String(expireAt)]

  // Sort parameters to ensure consistent ordering
  const sortedParams = Object.entries(queryParams)
    .sort()
    .map(([key, values]) =>
      values
        .filter(Boolean)
        .map((v) => `${encodeURIComponent(key)}=${encodeURIComponent(v)}`)
    )
    .flat()
    .filter(Boolean)
    .join('&')

  const stringToSign = `${workspaceSlug}/${templateSlug}/${inputField}?${sortedParams}`
  const signature = createHmac('sha256', auth_secret)
    .update(stringToSign)
    .digest('hex')

  const finalParams = `${sortedParams}&sig=${encodeURIComponent(
    `sha256:${signature}`
  )}`
  return `https://${workspaceSlug}.tlcdn.com/${templateSlug}/${inputField}?${finalParams}`
}

// Read JSON from stdin
let jsonInput = ''
process.stdin.on('data', (chunk) => {
  jsonInput += chunk
})

process.stdin.on('end', () => {
  const params = JSON.parse(jsonInput)
  console.log(signSmartCDNUrl(params))
})
