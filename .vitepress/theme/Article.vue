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
</script>

<template>
    <Content class="prose dark:prose-invert prose-a:no-underline prose-a:font-bold prose-a:border-b-[#7dd3fc] prose-a:border-b max-w-none" />
</template>