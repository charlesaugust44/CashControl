<template>
    <div class="min-h-screen bg-[#FDFDFC] dark:bg-[#0a0a0a]">
        <!-- Navigation -->
        <nav class="bg-white dark:bg-[#161615] border-b border-gray-200 dark:border-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <router-link
                            to="/"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-[#f53003] dark:hover:text-[#FF4433] border-b-2 border-transparent hover:border-[#f53003] dark:hover:border-[#FF4433] transition"
                        >
                            Dashboard
                        </router-link>
                        <router-link
                            to="/assets"
                            class="ml-4 inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-[#f53003] dark:hover:text-[#FF4433] border-b-2 border-transparent hover:border-[#f53003] dark:hover:border-[#FF4433] transition"
                        >
                            Assets
                        </router-link>
                    </div>

                    <div class="flex items-center">
                        <router-link
                            to="/entries/create"
                            class="inline-flex items-center px-4 py-2 bg-[#f53003] text-white text-sm font-medium rounded-md hover:bg-[#d42600] transition"
                        >
                            + New Entry
                        </router-link>
                    </div>
                </div>
            </div>
        </nav>

        <main>
            <router-view/>
        </main>
    </div>
</template>

<script setup>
import {RouterView, RouterLink} from 'vue-router'
</script>
