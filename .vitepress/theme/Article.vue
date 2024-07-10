<script setup lang="ts">
import Date from './Date.vue'
import Author from './Author.vue'
import { computed } from 'vue'
import { useData, useRoute } from 'vitepress'
import { data as posts } from './posts.data.js'

const { frontmatter: data } = useData()

const route = useRoute()

function findCurrentIndex() {
  return posts.findIndex((p) => p.url === route.path)
}

// use the customData date which contains pre-resolved date info
const date = computed(() => posts[findCurrentIndex()].date)
const nextPost = computed(() => posts[findCurrentIndex() - 1])
const prevPost = computed(() => posts[findCurrentIndex() + 1])
</script>

<template>
  <article class="xl:divide-y xl:divide-gray-200 dark:xl:divide-slate-200/5">
    <header class="pt-6 xl:pb-10 space-y-1 text-center">
      <Date :date="date" />
      <h1
        class="text-3xl leading-9 font-extrabold text-gray-900 dark:text-white tracking-tight sm:text-4xl sm:leading-10 md:text-5xl md:leading-14"
      >
        {{ data.title }}
      </h1>
    </header>

    <div
      class="divide-y xl:divide-y-0 divide-gray-200 dark:divide-slate-200/5 pb-16 xl:pb-20"
      style="grid-template-rows: auto 1fr"
    >
      <div
        class="divide-y divide-gray-200 dark:divide-slate-200/5 xl:pb-0"
      >
        <Content class="prose dark:prose-invert max-w-none pt-10 pb-8" />
      </div>
    </div>
  </article>
</template>