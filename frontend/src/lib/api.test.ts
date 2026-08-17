import { describe, expect, it, vi, afterEach } from 'vitest'
import { fetchResources } from '@/lib/api'

describe('fetchResources', () => {
  afterEach(() => {
    vi.unstubAllGlobals()
  })

  it('returns parsed resources on success', async () => {
    vi.stubGlobal(
      'fetch',
      vi.fn().mockResolvedValue({
        ok: true,
        json: async () => [{ id: 1, name: 'Room A' }],
      }),
    )

    const resources = await fetchResources()

    expect(resources).toEqual([{ id: 1, name: 'Room A' }])
  })

  it('throws when the response is not ok', async () => {
    vi.stubGlobal(
      'fetch',
      vi.fn().mockResolvedValue({ ok: false, status: 500, json: async () => ({}) }),
    )

    await expect(fetchResources()).rejects.toThrow('Failed to fetch resources: 500')
  })
})
