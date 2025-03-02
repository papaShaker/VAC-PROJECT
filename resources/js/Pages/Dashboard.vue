<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, nextTick } from 'vue';

const sections = ref([
  { title: "Has iniciado sesión.", 
    text: "¡Bienvenido a SGVH, el nuevo sistema integral de gestión de tu tiempo en la empresa! \n\nDiseñado para simplificar y optimizar la planificación de vacaciones y horarios, ahora nuestra plataforma te ofrece una experiencia intuitiva y eficiente. \n\nCon nuestro nuevo sistema, visualizarás tus vacaciones y horarios en un instante, permitiéndote planificar con claridad y sin errores. Selecciona días libres con un simple clic, ajusta tus planes y recibe alertas para mantenerte siempre informado.", 
    image: "/img/SGVH-dashboard2.png" },
  { title: "Novedades...", 
    text: "Este sistema fomenta la transparencia y la colaboración, facilitando la coordinación de equipos y mejorando la comunicación entre empleados y superiores. Integrando diferentes tipos de contrato y normativas laborales, nos aseguramos que cada empleado tenga acceso a información relevante y precisa sobre su situación particular. Comparte tus planes con tu equipo y experimenta la libertad de una planificación sin complicaciones.", 
    image: "/img/Templates-dashboard.png" },
  { title: "Calendario interactivo...", 
    text: "Nuestro calendario interactivo redefine la planificación de tus días libres. Diseñado para ofrecerte una visión completa, este calendario te muestra no solo los días festivos y los límites de personal por vacaciones, sino también el estado de tus propias solicitudes. \n\n¿Ya solicitaste un día? Lo verás marcado. ¿Fue aprobado? También lo sabrás al instante. Y si tu solicitud viene con un comentario, lo podrás leer directamente desde el calendario. \n\nLa interactividad es clave: un simple clic te permite solicitar o cancelar días, adaptándose a tus cambios de planes. Olvídate de las confusiones y los errores; con nuestro calendario, la planificación es clara, precisa y totalmente bajo tu control.",
    image: "/img/InteractiveCalendar-dashboard.png" },
    { title: "Transparencia...", 
    text: "La transparencia en el equipo es fundamental para un ambiente laboral armonioso y productivo. Con el nuevo sistema SVGH, fomentamos esta transparencia al ofrecerte una visión clara del estado de las vacaciones y los horarios de tu departamento. Ahora, puedes saber quién ha solicitado vacaciones, si han sido confirmadas y qué turnos están programados. Esta visibilidad promueve la confianza y la comunicación abierta entre compañeros, permitiendo una mejor coordinación y planificación. \n\nAl tener acceso a esta información, se reducen las confusiones y se facilita la organización del trabajo en equipo. La transparencia en la gestión de vacaciones y horarios no solo mejora la eficiencia, sino que también fortalece el sentido de pertenencia y colaboración dentro del departamento. Con SVGH, construimos un entorno de trabajo donde la información fluye libremente, beneficiando a todos.",
    image: "/img/Transparency-dashboard.png" },
    { title: "Horarios...", 
    text: "El nuevo sistema de Horarios SVGH revoluciona la gestión de turnos, ofreciendo flexibilidad y eficiencia sin precedentes. Con plantillas departamentales personalizables, se adapta a cada situación, asegurando una cobertura óptima del personal. La rotación automática de turnos garantiza equidad y variedad, evitando la monotonía y el agotamiento. \n\nLa generación automática de horarios, basada en las plantillas adecuadas, libera a los responsables de tareas manuales, ahorrando tiempo y reduciendo errores. Las tablas de horarios integran las vacaciones, bajas y guardias, proporcionando una visión completa y actualizada. Además, la posibilidad de intercambiar turnos y modificar horarios ofrece a los departamentos una mayor flexibilidad, adaptándose a las necesidades cambiantes y a las preferencias individuales de los empleados. \n\nSVGH Horarios es un sistema adaptable, diseñado para crecer y evolucionar con la empresa, con un gran potencial para futuras mejoras e integraciones.",
    image: "/img/Schedules-dashboard.png" },
    { title: "Gestión del sistema...", 
    text: "Con nuestro sistema de gestión integral, el control de tu empresa está al alcance de tu mano. Administra departamentos, características y asigna usuarios con facilidad, adaptando la plataforma a la estructura única de tu organización. Define días festivos y nuevos tipos de contratos, asegurando el cumplimiento normativo y la flexibilidad laboral. \n\nGestiona horarios con precisión, creando plantillas personalizadas y rotaciones equitativas. Visualiza y ajusta cada aspecto de la planificación, desde las vacaciones hasta los turnos, todo en un entorno centralizado. Simplifica la toma de decisiones y optimiza la gestión de recursos humanos con una herramienta diseñada para crecer con tu empresa.",
    image: "/img/Administrate-dashboard.png" }
]);

const isVisible = ref([]);
const sectionRefs = ref([]);

onMounted(() => {
  isVisible.value = new Array(sections.value.length).fill(false);
  //isVisible.value[0] = true; // El primer div siempre visible

  nextTick(() => {
    const observerConfig = {
      root: null,
      rootMargin: '0px',
      threshold: 0.2,
    };

    const observerCallback = (entries) => {
      entries.forEach((entry) => {
        const sectionIndex = sectionRefs.value.indexOf(entry.target);
        if (sectionIndex !== -1) { // Asegurarse de que el índice es válido
          isVisible.value[sectionIndex] = entry.isIntersecting;
        }
      });
    };

    const observer = new IntersectionObserver(observerCallback, observerConfig);

    // Esperar a que sectionRefs se llene completamente
    const observeSections = () => {
      if (sectionRefs.value.length === sections.value.length) {
        sectionRefs.value.forEach((section) => observer.observe(section));
      } else {
        requestAnimationFrame(observeSections); // Intenta de nuevo en el siguiente frame
      }
    };

    observeSections();
  });
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
                    <div 
                    v-for="(section, index) in sections" 
                    :key="index" 
                    ref="sectionRefs"
                    class="grid grid-cols-1 md:grid-cols-2 gap-6 opacity-0 transition-opacity duration-5000 p-12 items-center"
                    :class="{ 'opacity-100': isVisible[index] }"
                    >
                            <!-- ... -->
                        <div v-if="index % 2 === 0" class="text p-4">
                            <h1 class="text-2xl text-gray-100 italic" style="font-family: 'Abel', sans-serif;">{{ section.title }}</h1>
                            <p class="mt-2 text-blue-200 text-justify italic whitespace-pre-line" >{{ section.text }}</p>
                        </div>

                        <div class="image">
                            <img :src="section.image" alt="Image" class="w-full rounded-lg">
                        </div>

                        <div v-if="index % 2 !== 0" class="text px-4 py-2">
                            <h2 class="text-2xl text-gray-100 italic" style="font-family: 'Abel', sans-serif;">{{ section.title }}</h2>
                            <p class="mt-2 text-blue-200  text-justify italic whitespace-pre-line">{{ section.text }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>