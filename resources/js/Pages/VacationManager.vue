<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import { ref, reactive, onBeforeMount, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import { toast } from 'vue3-toastify';
import ModalInfoHolidays from '@/Components/ModalInfoHolidays.vue';

const props = defineProps({
    departments: Array,
});

const admin_toggled = ref(false);

const toggleAdmin = () => {
    admin_toggled.value = !admin_toggled.value;
    console.log("TOGGLES");    
}

</script>

<template>

    <Head title="Departamentos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl text-gray-800 dark:text-gray-200 leading-tight"
                style="font-family: 'Abel', sans-serif;">Bienvenido a Administrar
            </h2>
        </template>

        <div class="w-full py-12"> <!-- Container-fluid -->
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 flex justify-center items-center space-x-5 text-gray-900 dark:text-gray-100">
                        <div class="w-full bg-white dark:bg-gray-800 rounded-md">
                            
<!-- CHECKBOX USERS -->
            
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg" :class="[(admin_toggled) ? 'admin_toggled' : '',]">
                    <div class="py-2 flex justify-between items-center space-x-5 text-gray-900 dark:text-gray-100 bg-gray-800 px-4">
                        <div class="text-2xl font-bold" style="font-family: 'Abel', sans-serif;"> EMPLEADOS</div>
                        <div class="flex items-center">
                        <!-- Toggle Switch -->
                        <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" :checked="admin_toggled" @change="toggleAdmin()">
                                <div
                                class="w-12 h-6 bg-gray-300 rounded-full border-2 ring-white peer-checked:bg-gray-800 
                                        peer-focus:ring-1 peer-focus:ring-blue-100 transition-all">
                                </div>
                                <div
                                class="absolute text-gray-700 bg-white left-1 top-1 w-4 h-4 rounded-full shadow 
                                        peer-checked:translate-x-5 transition-transform peer-checked:text-green-500 peer-checked:left-1.5">
                                        <i class="fa-solid fa-wrench text-xs absolute top-0.6 left-0.5"></i>
                                    </div>
                            </label>
                        </div>
                    </div>
                    <div v-if="admin_toggled" class="p-2 pb-8 bg-gray-800 grid justify-center items-center space-x-5 text-gray-900 dark:text-gray-100">

                        <!-- SLOT -->
                        <div class="sm:flex sm:justify-between items-center grid">
                            SLOT
                        </div>
                        <!-- END SLOT -->

                    </div>
                </div>
      

<!-- CHECKBOX USERS END -->


                        </div>
                    <!-- START TABLE -->
                    </div>
                
                    <!-- MODAL UPDATE VACATIONS END -->

                    <!-- END TO BE CONFIRMED VACATIONS -->

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>

.linkUsr {
    font-weight: bold;
}

.numeric_Container {
    display: flex;
    justify-content: center;
}

.numeric {
    width: 100% !important;
    padding: 5px;
    text-align: center;
    background-color: #303A49;
}

.to_be_confirmed {
    cursor: pointer;
}

.past_month {
    background-color: #25232c;
}

.past_month_text {
    color: #4e4e50;
    cursor: context-menu;
}

.free_day {
    background-color: #F87171 !important;
    color: #fff !important;
    cursor: context-menu;
}

.work_on_freedays {
    border: 1px solid #F87171;
    color: #ffffff !important;
}

.confirmed_holiday {
    background-color: #4ADE80;
    color: #4e4e50 !important;
}

.day_selected {
    background-color: #57ddff;
    color: #4e4e50 !important;
}

.locked_already_requested_day {
    cursor: context-menu;
    color: #4e4e50 !important;
    text-shadow: 0px 0px 2px #ffffff;
    font-weight: bolder;
    background: repeating-linear-gradient(-45deg, #f87171, #f87171 10px, white 10px, white 20px) !important;
}

.not_ranged_day,
.passed_day {
    color: rgb(83, 83, 83);
    cursor: context-menu;
}

.today {
    border: 2px solid rgb(175, 175, 175) !important;
    cursor: context-menu;
}

.text_color_success {
    color: #4ADE80;
}

.text_color_error {
    color: #e93f2c;
}

.text_color_warning {
    color: #F1C40F;
}
.tooltip-mvc {
    position: relative;
    cursor: pointer;
}

.disabled{
    color: #928f70 !important;
}

.admin_toggled {
    background-color: #30353d !important;
}

@media (max-width: 975px) {
    .responsive_table{font-size: 1.5vw;
    }
}

@media (max-width: 650px) {
  .grid_style {
    display: grid !important;
  }
  .grid_style_legend {
    display: grid !important;
    justify-items: start;
  }

  .items_spacing_y {
    margin: 2px;
  }
}

</style>