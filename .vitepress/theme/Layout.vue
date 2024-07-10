<script setup lang="ts">
import { useData } from 'vitepress'
import Home from './Home.vue'
import Post from './Post.vue'
import NotFound from './NotFound.vue'

const { page, frontmatter } = useData()
</script>

<template>
  <div class="antialiased dark:bg-slate-900">
    <main class="flex-auto w-full container max-w-4xl mx-auto py-16 px-6">
      <Home v-if="frontmatter.index" />
      <NotFound v-else-if="page.isNotFound" />
      <Post v-else />
    </main>
  </div>
</template>