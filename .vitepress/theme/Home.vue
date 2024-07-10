<script setup lang="ts">
import Date from './Date.vue'
import Profile from './Profile.vue'
import About from './About.vue'
import { data as posts } from './posts.data.js'
import { useData } from 'vitepress'

const { frontmatter } = useData()
</script>

<template>
  <div class="max-w-2xl mx-auto">
    <div class="flex flex-col items-center justify-center mb-12">
      <Profile />
      <About />
    </div>
    <ul class="">
      <li class="" v-for="{ title, url, date, excerpt } of posts">
        <a aria-label="read more" :href="url">
          <article
            class="h-[287px] text-center w-full mx-auto mb-6 cursor-pointer border border-gray-200 rounded-lg px-8 py-6 shadow-md"
          >
            <p class="text-gray-400 text-sm my-2">
              <Date :date="date" />
            </p>
            <h3 class="text-3xl mb-6 text-gray-700 font-bold">
              {{ title }}
            </h3>
            <p v-if="excerpt" class="mt-0 text-gray-500 mb-4 text-justify prose dark:prose-invert max-w-none text-gray-500 dark:text-gray-300" v-html="excerpt"></p>

            <span class="text-gray-500 text-xs font-semibold text-right">
                Ler mais →
            </span>
          </article>
        </a>
      </li>
    </ul>
  </div>
</template>