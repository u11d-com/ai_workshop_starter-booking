export interface Resource {
  id: number
  name: string
}

export async function fetchResources(): Promise<Resource[]> {
  const response = await fetch('/api/resources')

  if (!response.ok) {
    throw new Error(`Failed to fetch resources: ${response.status}`)
  }

  return (await response.json()) as Resource[]
}
