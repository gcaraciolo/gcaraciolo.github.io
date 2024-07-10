<script setup lang="ts">
import Date from './Date.vue'
import Profile from './Profile.vue'
import About from './About.vue'
import Article from './Article.vue'
import { useData, useRoute } from 'vitepress'
import { computed } from 'vue'
import { data as posts } from './posts.data.js'

const { frontmatter: data } = useData()
const route = useRoute()
function findCurrentIndex() {
  return posts.findIndex((p) => p.url === route.path)
}

const date = computed(() => posts[findCurrentIndex()].date)
const title = computed(() => posts[findCurrentIndex()].title)
</script>

<template>
    <div class="max-w-2xl mx-auto">
        <Profile />
        <div class="text-center mt-12">
            <time class="text-gray-400 text-sm my-2" :date="date.time">{{ date.string }}</time>
            <h1 class="leading-none mb-8 text-4xl font-semibold">{{ data.title }}</h1>
        </div>

        <Content class="prose dark:prose-invert prose-a:no-underline prose-a:font-bold prose-a:border-b-[#7dd3fc] prose-a:border-b max-w-none" />

        <div class="border-b border-b-gray-200 mb-8"></div>

        <p class="text-lg text-center text-gray-700">Guilherme Caraciolo</p>
        <About />
    </div>
</template>