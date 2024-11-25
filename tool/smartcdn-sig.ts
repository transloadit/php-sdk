#!/usr/bin/env tsx
import { createHmac, randomUUID } from 'crypto'

export interface SmartCDNUrlOptions {
  /**
   * Workspace slug
   */
  workspace: string
  /**
   * Template slug or template ID
   */
  template: string
  /**
   * Input value that is provided as `${fields.input}` in the template
   */
  input: string
  /**
   * Additional parameters for the URL query string
   */
  urlParams?: Record<
    string,
    boolean | number | string | (boolean | number | string)[]
  >
  /**
   * Expiration time of the signature in milliseconds. Defaults to 1 hour.
   */
  signProps: {
    authKey: string
    authSecret: string
    expireInMs?: number
    expireAtMs?: number
  }
}

/**
 * Construct a signed Smart CDN URL. See https://transloadit.com/docs/topics/signature-authentication/#smart-cdn.
 */
function getSignedSmartCDNUrl(opts: SmartCDNUrlOptions): string {
  if (opts.workspace == null || opts.workspace === '')
    throw new TypeError('workspace is required')
  if (opts.template == null || opts.template === '')
    throw new TypeError('template is required')
  if (opts.input == null) throw new TypeError('input is required') // `input` can be an empty string.

  const workspaceSlug = encodeURIComponent(opts.workspace)
  const templateSlug = encodeURIComponent(opts.template)
  const inputField = encodeURIComponent(opts.input)

  let expireAt = Date.now() + 1 * 60 * 60 * 1000 // 1 hour
  if (opts.signProps.expireAtMs) {
    expireAt = opts.signProps.expireAtMs
  } else if (opts.signProps.expireInMs) {
    expireAt = Date.now() + opts.signProps.expireInMs
  }

  const queryParams = new URLSearchParams()
  for (const [key, value] of Object.entries(opts.urlParams || {})) {
    if (Array.isArray(value)) {
      for (const val of value) {
        queryParams.append(key, `${val}`)
      }
    } else {
      queryParams.append(key, `${value}`)
    }
  }

  queryParams.set('auth_key', opts.signProps.authKey)
  queryParams.set('exp', String(expireAt))
  // The signature changes depending on the order of the query parameters. We therefore sort them on the client-
  // and server-side to ensure that we do not get mismatching signatures if a proxy changes the order of query
  // parameters or implementations handle query parameters ordering differently.
  queryParams.sort()

  const stringToSign = `${workspaceSlug}/${templateSlug}/${inputField}?${queryParams}`
  const algorithm = 'sha256'
  const signature = createHmac(algorithm, opts.signProps.authSecret)
    .update(stringToSign)
    .digest('hex')

  queryParams.set('sig', `sha256:${signature}`)
  const signedUrl = `https://${workspaceSlug}.tlcdn.com/${templateSlug}/${inputField}?${queryParams}`
  return signedUrl
}

// console.log('expiryAt Proposal: ', Date.now() + 60 * 60 * 1000)

console.log(
  getSignedSmartCDNUrl({
    workspace: process.argv[3],
    template: process.argv[4],
    input: process.argv[5],
    signProps: {
      authKey: process.env.TRANSLOADIT_KEY || '',
      authSecret: process.env.TRANSLOADIT_SECRET || '',
      expireAtMs: Number(process.argv[2]),
    },
  })
)
