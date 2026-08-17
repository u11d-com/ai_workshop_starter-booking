<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { fetchResources, type Resource } from '@/lib/api'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'

const resources = ref<Resource[]>([])
const error = ref<string | null>(null)

onMounted(async () => {
  try {
    resources.value = await fetchResources()
  } catch (cause) {
    error.value = cause instanceof Error ? cause.message : 'Unknown error'
  }
})
</script>

<template>
  <Card class="max-w-xl mx-auto mt-10">
    <CardHeader>
      <CardTitle>Resources</CardTitle>
    </CardHeader>
    <CardContent>
      <p v-if="error" class="text-destructive">{{ error }}</p>
      <Table v-else>
        <TableHeader>
          <TableRow>
            <TableHead>ID</TableHead>
            <TableHead>Name</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="resource in resources" :key="resource.id">
            <TableCell>{{ resource.id }}</TableCell>
            <TableCell>{{ resource.name }}</TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </CardContent>
  </Card>
</template>
