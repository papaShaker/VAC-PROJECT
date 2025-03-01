<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const sections = ref([
  { title: "Has iniciado sesión.", 
    text: "¡Bienvenido a SGVH, el nuevo sistema integral de gestión de tu tiempo en la empresa! Diseñado para simplificar y optimizar la planificación de vacaciones y horarios, ahora nuestra plataforma te ofrece una experiencia intuitiva y eficiente. Con nuestro nuevo sistema, visualizarás tus vacaciones y horarios en un instante, permitiéndote planificar con claridad y sin errores. Selecciona días libres con un simple clic, ajusta tus planes y recibe alertas para mantenerte siempre informado.", 
    image: "image1.jpg" },
  { title: "Novedades...", 
    text: "Este sistema fomenta la transparencia y la colaboración, facilitando la coordinación de equipos y mejorando la comunicación entre empleados y superiores. Integrando diferentes tipos de contrato y normativas laborales, nos aseguramos que cada empleado tenga acceso a información relevante y precisa sobre su situación particular. Comparte tus planes con tu equipo y experimenta la libertad de una planificación sin complicaciones.", 
    image: "image2.jpg" },
  { title: "Calendario interactivo...", 
    text: "This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.",
    image: "image3.jpg" },
    { title: "Transparencia...", 
    text: "This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.",
    image: "image3.jpg" },
    { title: "Horarios...", 
    text: "This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.",
    image: "image3.jpg" },
    { title: "Gestión del sistema...", 
    text: "This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.This is some text on the left.",
    image: "image3.jpg" }
]);

const isVisible = ref([]);
const sectionRefs = ref([]);

onMounted(() => {
  const observerConfig = {
    root: null,
    rootMargin: '0px',
    threshold: 0.2
  };

  const observerCallback = (entries) => {
    entries.forEach((entry) => {
      const sectionIndex = sectionRefs.value.indexOf(entry.target);
      isVisible.value[sectionIndex] = entry.isIntersecting;
    });
  };

  const observer = new IntersectionObserver(observerCallback, observerConfig);

  sectionRefs.value.forEach((section) => observer.observe(section));
});

</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl text-gray-800 dark:text-gray-200 leading-tight" style="font-family: 'Abel', sans-serif;">Bienvenido</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">

                    <div class="w-4/5 mx-auto text-gray-900 dark:text-gray-100">
                        <section 
                            v-for="(section, index) in sections" 
                            :key="index"
                            class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center py-12 "
                            :class="{ 'opacity-100 translate-y-0': isVisible[index] }"
                            ref="sectionRefs"
                            >
                            <!-- ... -->
                        <div v-if="index % 2 === 0" class="text p-4">
                            <h1 class="text-2xl font-bold text-gray-200 italic">{{ section.title }}</h1>
                            <p class="mt-2 text-green-200 text-justify italic">{{ section.text }}</p>
                        </div>

                        <div class="image">
                            <img :src="section.image" alt="Image" class="w-full rounded-lg shadow-md">
                        </div>

                        <div v-if="index % 2 !== 0" class="text p-4">
                            <h2 class="text-2xl font-bold text-gray-200 italic">{{ section.title }}</h2>
                            <p class="mt-2 text-green-200  text-justify italic">{{ section.text }}</p>
                        </div>
                        </section>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>