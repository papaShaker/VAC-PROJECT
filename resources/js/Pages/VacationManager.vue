<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import { ref, reactive, onBeforeMount, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import { toast } from 'vue3-toastify';
import ModalInfoHolidays from '@/Components/ModalInfoHolidays.vue';
import { useUserStore } from '@/stores/userStore';

const props = defineProps({
    departments: Array,
});
const userStore = useUserStore();

const users_toggled = ref(false);
const departments_toggled = ref(false);
const contracts_toggled = ref(false);
const contract_types_toggled = ref(false);
const zones_toggled = ref(false);
const non_working_days_toggled = ref(false);
const extra_days_toggled = ref(false);
const extra_day_types_toggled = ref(false);
const users = ref([]);
const currentPage = ref(1);
const pageSize = 10; // Number of users per page
const job_ranges = ref([]);
const currentPageContracts = ref(1);
const pageSizeContracts = 5; // Number of users per page
const departments = ref([]); // Store fetched departments
const zones = ref([]); // Store fetched zones
const is_editing = ref(false);
const edit_state = reactive({
    table: null,
    row: null,
    selected_department: null,
    selected_zone: null,
    selected_department_name: null,
    selected_department_users_same_day_holidays: null,
    selected_department_holidays_per_month: null,
    add_dep_button_is_active: false,
    add_dep_name: null,
    add_dep_max_vac_per_day: null,
    add_dep_max_vac_per_month: null,

});
const searchQuery = ref('');
const searchQueryContracts = ref('')
const searchQueryDepartments = ref('');

const startEditing = (row_index, table_index) => {
    //console.log("StartEditing --> Table index: " + table_index);
    //console.log("StartEditing --> Row index: " + row_index);
    is_editing.value = true;

    edit_state.row = row_index;
    edit_state.table = table_index;

    if(table_index === 0){
        edit_state.selected_department = users.value[row_index]?.department?.id || null;
        edit_state.selected_zone = users.value[row_index]?.nonworkingdayzone?.id || null;
    }

    else if(table_index === 1){
    edit_state.selected_department_name = departments.value[row_index]?.name || null;
    //console.log("selected_department_name: " + edit_state.selected_department_name);
    edit_state.selected_department_users_same_day_holidays = departments.value[row_index]?.selected_department_users_same_day_holidays || null;
    edit_state.selected_department_holidays_per_month = departments.value[row_index]?.selected_department_holidays_per_month || null;
    }
};

const closeEditing = (row_index, table_index) => {
    if (edit_state.row === row_index && edit_state.table === table_index) {
        is_editing.value = false;
        edit_state.row = null;
        edit_state.table = null;
    }
};

const isRowBeingEdited = (row_index, table_index) => {
    return is_editing.value && 
           edit_state.row === row_index && 
           edit_state.table === table_index;
};

/* USERS FUNCTIONS */
const fetchUsers = () => {
    let url;
    if(!userStore.hasRole('super_user') || !userStore.hasRole('RRHH')){
        url = '/fetch/users';
    } else {
        url = '/fetch/users/department';
    }
    axios.get(url).then((response) => {
        users.value = response.data;
    }).catch(error => {
        console.error('Error', error);
    });
}

const fetchDepartmentsAndZones = async () => {
    try {
        const [departmentsData, zonesData] = await Promise.all([
            axios.get('/fetch/departments'),
            axios.get('/fetch/zones'),
        ]);
        departments.value = departmentsData.data;
        zones.value = zonesData.data;
        //console.log(departments.value);
        //console.log(zones.value);
    } catch (error) {
        console.error("Ha surgido un error:", error);
    }
};

const saveEmployeesChanges = async (user_id) => {
    try {
        const updatedUser = {
            department_id: edit_state.selected_department,
            nonworkingdayzone_id: edit_state.selected_zone,
        };
        //console.log(updatedUser);
        await axios.put(`/update/user/${user_id}`, updatedUser); // Replace with your API endpoint

        // Update the user in the table (Optimistic UI update)
        const userIndex = users.value.findIndex(user => user.id === user_id);

        if (userIndex !== -1) {
            users.value[userIndex].department_id = edit_state.selected_department;
            users.value[userIndex].nonworkingdayzone_id = edit_state.selected_zone;
        }

        closeEditing(edit_state.row, edit_state.table);
        // Fetch the updated users list
        fetchUsers();
    } catch (error) {
        console.error("Error al guardar los cambios:", error);
    }
};

const paginatedUsers = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    const end = start + pageSize;
    return filterUsersByName().slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(users.value.length / pageSize);
});

/* DEPARTMENT FUNCTIONS */
const saveDepartmentChanges = async (dep_id) => {
    if (
        !edit_state.selected_department_name?.trim() || 
        edit_state.selected_department_users_same_day_holidays === null || 
        edit_state.selected_department_holidays_per_month === null
    ) {
        console.error("Error: Todos los campos deben estar llenos.");
        return;
    }

    try {
        const updatedDepartment = {
            name: edit_state.selected_department_name,
            users_same_day_holidays: edit_state.selected_department_users_same_day_holidays,
            holidays_per_month: edit_state.selected_department_holidays_per_month
        };
        
        await axios.put(`/update/department/${dep_id}`, updatedDepartment);
        
        closeEditing(edit_state.row, edit_state.table);
        fetchDepartmentsAndZones();
    } catch (error) {
        console.error("Error al guardar los cambios:", error);
    }
};

const insertNewDepartment = async () => {
    if (
        !edit_state.add_dep_name?.trim() || 
        edit_state.add_dep_max_vac_per_day === null || 
        edit_state.add_dep_max_vac_per_month === null
    ) {
        console.error("Error: Todos los campos deben estar llenos.");
        return;
    }

    try {
        const newDepartment = {
            name: edit_state.add_dep_name.trim(),
            users_same_day_holidays: edit_state.add_dep_max_vac_per_day,
            holidays_per_month: edit_state.add_dep_max_vac_per_month
        };

        console.log(newDepartment);

        await axios.post("/insert/department", newDepartment);

        reset_add_dep_state();// Reset fields and hide input fields after successful insert

        fetchDepartmentsAndZones(); // Refresh department list
    } catch (error) {
        console.error("Error al insertar el departamento:", error);
    }
};

const reset_add_dep_state = () => {
        edit_state.add_dep_button_is_active = false;
        edit_state.add_dep_name = null;
        edit_state.add_dep_max_vac_per_day = null;
        edit_state.add_dep_max_vac_per_month = null;
}

/* CONTRACT FUNCTIONS */

const fetchJobRanges = () => {
    let url;
    if(!userStore.hasRole('super_user') || !userStore.hasRole('RRHH')){
        url = 'getUsersJobRanges';
    } else {
        url = 'getUsersJobRanges/department';
    }
    axios.get('getUsersJobRanges').then((response) => {
        console.log(response.data);
        job_ranges.value = response.data;
    }).catch(error => {
        console.error('Error', error);
    });
}

const paginatedContracts = computed(() => {
    const startContracts = (currentPageContracts.value - 1) * pageSizeContracts;
    const endContracts = startContracts + pageSizeContracts;
    return filteredJobRanges.value.slice(startContracts, endContracts);
});

const totalPagesContracts = computed(() => {
    return Math.ceil(filteredJobRanges.value.length / pageSizeContracts);
});

/* CONTRACT TYPE FUNCTIONS */

/* FESTIVOS FUNCTIONS */

/* NON WORKING DAY ZONES FUNCTIONS */

/* EXTRA DAY FUNCTIONS */ 

/* EXTRA DAY TYPE FUNCTIONS */ 


/* VALIDATIONS */

const validateInputMaxVacationsPerDay = (event) => {
    const target = event.target;
    const value = parseInt(target.value, 10) || 0;
    const validatedValue = Math.min(5, Math.max(0, value));

    edit_state.selected_department_users_same_day_holidays = validatedValue;
    target.value = validatedValue;
};

const validateAddInputMaxVacationsPerDay = (event) => {
    const target = event.target;
    const value = parseInt(target.value, 10) || 0;
    const validatedValue = Math.min(5, Math.max(0, value));

    edit_state.add_dep_max_vac_per_day = validatedValue;
    target.value = validatedValue;
};

const validateInputVacationsPerMonth = (event) => {
    const target = event.target;
    // Parse as float instead of integer, defaulting to 0 if NaN
    const value = parseFloat(target.value) || 0;
    
    // Round to nearest 0.1 to ensure proper increments
    const roundedValue = Math.round(value * 10) / 10;
    
    // Enforce min/max constraints (1.0 to 3.0)
    const validatedValue = Math.min(3.0, Math.max(1.0, roundedValue));
    
    // Update state with the validated float value
    edit_state.selected_department_holidays_per_month = validatedValue;
    
    // Display with exactly one decimal place
    target.value = validatedValue.toFixed(1);
};

const validateAddInputVacationsPerMonth = (event) => {
    const target = event.target;
    // Parse as float instead of integer, defaulting to 0 if NaN
    const value = parseFloat(target.value) || 0;
    
    // Round to nearest 0.1 to ensure proper increments
    const roundedValue = Math.round(value * 10) / 10;
    
    // Enforce min/max constraints (1.0 to 3.0)
    const validatedValue = Math.min(3.0, Math.max(1.0, roundedValue));
    
    // Update state with the validated float value
    edit_state.add_dep_max_vac_per_month = validatedValue;
    
    // Display with exactly one decimal place
    target.value = validatedValue.toFixed(1);
};

const validateTextInputDepName = (event) => {
    // Remove any non-letter characters
    const value = event.target.value;
    const lettersOnly = value.replace(/[^A-Za-z ]/g, '');
    
    // If the value was changed, update the model
    if (value !== lettersOnly) {
      edit_state.selected_department_name = lettersOnly;
    }
    
    // Ensure max length
    if (lettersOnly.length > 25) {
      edit_state.selected_department_name = lettersOnly.substring(0, 25);
    }
  }

  const validateAddTextInputDepName = (event) => {
    // Remove any non-letter characters
    const value = event.target.value;
    const lettersOnly = value.replace(/[^A-Za-z ]/g, '');
    
    // If the value was changed, update the model
    if (value !== lettersOnly) {
      edit_state.add_dep_name = lettersOnly;
    }
    
    // Ensure max length
    if (lettersOnly.length > 25) {
      edit_state.add_dep_name = lettersOnly.substring(0, 25);
    }
  }

  const validateNothingEmptyAddDepartment = () => {
    if (edit_state.add_dep_button_is_active && edit_state.add_dep_name && edit_state.add_dep_max_vac_per_day && edit_state.add_dep_max_vac_per_month){
        return true;
    } else {
        return false;
    }
  }

/* FILTERS */

const filterUsersByName = () => {
      return users.value.filter(user =>
        user.name.toLowerCase().includes(searchQuery.value.toLowerCase())// Filtra ignorando mayúsculas/minúsculas
      );
};

const filterDepartmentsByName = () => {
      return departments.value.filter(department =>
        department.name.toLowerCase().includes(searchQueryDepartments.value.toLowerCase())// Filtra ignorando mayúsculas/minúsculas
      );
};

const filteredJobRanges = computed(() => {
  if (!Array.isArray(job_ranges.value)) return [];

  return job_ranges.value.filter(job_range => 
    job_range.user_range &&
    job_range.user_range.name.toLowerCase().includes(searchQueryContracts.value.toLowerCase())
  );
});

/* ON MOUNTED */

onMounted(() => {
    fetchUsers();
    fetchDepartmentsAndZones();
    fetchJobRanges();
});

</script>

<template>

    <Head title="Departamentos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl text-gray-800 dark:text-gray-200 leading-tight"
                style="font-family: 'Abel', sans-serif;">Bienvenido a Administrar
            </h2>
        </template>

<!-- CHECKBOX USERS -->
        <div v-if="userStore.hasPermission('view employees')" class="w-full pt-12"> <!-- CHECKBOX USERS START -->
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="py-2 flex justify-center items-center space-x-5 text-gray-900 dark:text-gray-100">
                        <div class="w-full bg-white dark:bg-gray-800 rounded-md">
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg" :class="[(users_toggled) ? 'admin_toggled' : '',]">
                                <div class="py-2 flex justify-between items-center space-x-5 text-gray-900 dark:text-gray-100 bg-gray-800 px-4">
                                    <div class="text-xl font-bold" style="font-family: 'Abel', sans-serif;"> EMPLEADOS</div>
                                    <div class="flex items-center">
                                    <!-- Toggle Switch -->
                                    <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="sr-only peer" :checked="users_toggled" @change="users_toggled=!users_toggled, fetchUsers()">
                                            <div
                                            class="w-12 h-6 bg-gray-300 rounded-full border-2 ring-white peer-checked:bg-gray-700 
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
                                <div v-if="users_toggled" class="w-full p-4 px-6 pb-8 bg-gray-800 grid justify-center items-center space-x-5 text-gray-900 dark:text-gray-100">

                                    <!-- SLOT -->
<!--AÑADIR USUARIO                  <div class="py-2 flex justify-end items-center space-x-5 text-gray-900 dark:text-gray-100">
                                        <button>AÑADIR USUARIO</button>
                                    </div> -->
                                    <div class="bg-gray-900">
                                        <div class="bg-gray-800">
                                        <!-- Campo de búsqueda -->
                                        <input
                                        type="text"
                                        v-model="searchQuery"
                                        placeholder="Buscar por nombre..."
                                        class="w-full p-1 border text-gray-950 border-gray-300 rounded mb-4"
                                        />
                                        </div>
                                        <table class="sm:w-[32rem] w-[24rem] sm:text-md text-sm text-left text-gray-500 dark:text-gray-400 bg-slate-600">
                                            <thead>
                                                <tr class="justify-center item-center">
                                                    <th class="text-center text-gray-900 dark:text-gray-100">ID</th>
                                                    <th class="text-center text-gray-900 dark:text-gray-100">NOMBRE</th>
                                                    <th class="text-center text-gray-900 dark:text-gray-100">DPTO</th>
                                                    <th class="text-center text-gray-900 dark:text-gray-100">ZONA</th>
                                                    <th v-if="userStore.hasPermission('edit employees')" class="text-center text-gray-900 dark:text-gray-100">EDITAR</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(user, user_index) in paginatedUsers" :key="user.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                                    <td class="text-center text-gray-900 dark:text-green-300 py-1">{{ user.id }}</td>
                                                    <td class="text-center text-gray-900 dark:text-gray-100">{{ user.name }}</td>
                                                    <!-- DEPARTMENT SELECTOR -->
                                                    <td class="text-center text-gray-900 dark:text-gray-100">
                                                        <template v-if="isRowBeingEdited(user_index, 0) && edit_state.row === user_index && userStore.hasPermission('edit employees')">
                                                            <select v-model="edit_state.selected_department" class="text-xs bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded p-1">
                                                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                                            </select>
                                                        </template>
                                                        <template v-else>
                                                            {{ user.department?.name }}
                                                        </template>
                                                    </td>
                                                    <!-- ZONE SELECTOR -->
                                                    <td class="text-center text-gray-900 dark:text-gray-100">
                                                        <template v-if="isRowBeingEdited(user_index, 0) && edit_state.row === user_index && userStore.hasPermission('edit employees')">
                                                            <select v-model="edit_state.selected_zone" class="text-xs bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded p-1">
                                                                <option v-for="zone in zones" :key="zone.id" :value="zone.id">{{ zone.zone }}</option>
                                                            </select>
                                                        </template>
                                                        <template v-else>
                                                            {{ user.nonworkingdayzone?.zone }}
                                                        </template>
                                                    </td>

                                                    <td  v-if="userStore.hasPermission('edit employees')" class="text-center text-gray-900 dark:text-gray-100">
                                                        <template v-if="isRowBeingEdited(user_index, 0) && edit_state.row === user_index && edit_state.table === 0 && userStore.hasPermission('edit employees')">
                                                            <button @click="saveEmployeesChanges(user.id)">
                                                                <i class="mx-1 text-lg text-green-400 fa-solid fa-check"></i>
                                                            </button>
                                                            <button @click="closeEditing(user_index, 0)">
                                                                <i class="mx-1 text-lg text-red-400 fa-solid fa-xmark"></i>
                                                            </button>
                                                        </template>
                                                        <template v-else>
                                                            <button @click="startEditing(user_index, 0)">
                                                                <i class="space-x-1 sm:ml-1 text-lg text-yellow-200 fa-regular fa-pen-to-square"></i>
                                                            </button>
                                                        </template>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div class="sm:w-[32rem] w-[24rem] sm:text-md text-sm text-left text-gray-100 dark:text-gray-400 bg-slate-600 px-2">
                                            <button class="text-gray-500 border rounded px-1 bg-slate-200" :disabled="currentPage === 1" :class="[(currentPage===1) ? 'bg-slate-800' : '']" @click="currentPage--">Anterior</button>
                                            <span class="text-gray-100"> Pág. <<<span class="font-bold text-yellow-400 text-md">{{ currentPage }}</span> de {{ totalPages }}>> </span>
                                            <button class="text-gray-500 border rounded px-1 bg-slate-200" :disabled="currentPage === totalPages" :class="[(currentPage===totalPages) ? 'bg-slate-800' : '']" @click="currentPage++"> Siguiente</button>
                                        </div>
                                    </div>
                                    <!-- END SLOT -->

                                </div>
                            </div> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
<!-- CHECKBOX USERS END -->

<!-- CHECKBOX DEPARTMENTS -->
        <div v-if="userStore.hasPermission('view departments')" class="w-full pt-4"> <!-- CHECKBOX DEPARTMENTS START -->
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="py-2 flex justify-center items-center space-x-5 text-gray-900 dark:text-gray-100">
                        <div class="w-full bg-white dark:bg-gray-800 rounded-md">
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg" :class="[(departments_toggled) ? 'admin_toggled' : '',]">
                                <div class="py-2 flex justify-between items-center space-x-5 text-gray-900 dark:text-gray-100 bg-gray-800 px-4">
                                    <div class="text-xl font-bold" style="font-family: 'Abel', sans-serif;"> DEPARTAMENTOS</div>
                                    <div class="flex items-center">
                                    <!-- Toggle Switch -->
                                    <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="sr-only peer" :checked="departments_toggled" @change="departments_toggled=!departments_toggled">
                                            <div
                                            class="w-12 h-6 bg-gray-300 rounded-full border-2 ring-white peer-checked:bg-gray-700 
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
                                <div v-if="departments_toggled" class="p-2 pb-8 bg-gray-800 grid justify-center items-center space-x-5 text-gray-900 dark:text-gray-100">

                                    <!-- SLOT -->
                                    <div class="bg-gray-800">
                                        <div class="bg-gray-800">
                                            <!-- Campo de búsqueda -->
                                            <input
                                            type="text"
                                            v-model="searchQueryDepartments"
                                            placeholder="Buscar por departamento..."
                                            class="w-full p-1 border text-gray-950 border-gray-300 rounded"
                                            />
                                        </div>
                                        <!-- ADD DEPARTMENT SECTION -->
                                        <div class="flex flex-col justify-center text-gray-900 dark:text-gray-100">
                                            <button v-if="!edit_state.add_dep_button_is_active" @click="edit_state.add_dep_button_is_active=!edit_state.add_dep_button_is_active" class="text-md border border-gray-700 text-green-400 bg-slate-600 rounded p-1 my-2" style="font-family: 'Abel', sans-serif;"><i class="fa-solid fa-plus text-md text-green-400"></i> AÑADIR DEPARTAMENTO</button>
                                            <button v-if="edit_state.add_dep_button_is_active" @click="reset_add_dep_state()" class="font-bold text-md border border-gray-700 text-red-400 bg-slate-600 rounded p-1 mt-2" style="font-family: 'Abel', sans-serif;"><i class="fa-solid fa-minus text-md text-red-400"></i> CANCELAR </button>
                                            <button v-if="edit_state.add_dep_button_is_active && validateNothingEmptyAddDepartment()" @click="insertNewDepartment()" class="font-bold text-md border border-gray-700 text-green-400 bg-slate-600 rounded p-1 my-1" style="font-family: 'Abel', sans-serif;"><i class="fa-solid fa-check text-md text-green-400"></i> GUARDAR </button>
                                            <template v-if="edit_state.add_dep_button_is_active">
                                                    <div class="flex flex-col justify-center text-gray-900 dark:text-gray-100 my-2" v-if="edit_state.add_dep_button_is_active">
                                                        <p class="font-bold text-md text-gray-400" style="font-family: 'Abel', sans-serif;">Nombre:</p>
                                                        <input class="mb-2 text-md p-1 border text-gray-950 border-gray-300 rounded" 
                                                        v-model="edit_state.add_dep_name" 
                                                        @input="validateAddTextInputDepName"
                                                        type="text" 
                                                        placeholder="Nombre del departamento"
                                                        />

                                                        <p class="font-bold text-md text-gray-400" style="font-family: 'Abel', sans-serif;">Máximo de usuarios en vacaciones en el mismo día:</p>
                                                        <input class="mb-2 text-md p-1 border text-gray-950 border-gray-300 rounded"
                                                            v-model.number="edit_state.add_dep_max_vac_per_day" 
                                                            @input="validateAddInputMaxVacationsPerDay"
                                                            type="number" 
                                                            placeholder="Máx. Vac/Día (0-5)"
                                                        />
                                                        
                                                        <p class="font-bold text-md text-gray-400" style="font-family: 'Abel', sans-serif;">Vacaciones por mes:</p>
                                                        <input class="mb-2 text-md p-1 border text-gray-950 border-gray-300 rounded"
                                                            v-model.number="edit_state.add_dep_max_vac_per_month" 
                                                            @input="validateAddInputVacationsPerMonth"
                                                            type="number" 
                                                            placeholder="Máx. Vac/Mes (1-3)"
                                                        />
                                                    </div>
                                            </template>
                                        </div>
                                        <!-- END ADD DEPARTMENT SECTION -->
                                        <table class="sm:w-[32rem] w-[24rem] sm:text-md text-sm text-left text-gray-500 dark:text-gray-400 bg-slate-600">
                                            <thead>
                                                <tr class="justify-center item-center">
                                                    <th class="text-center text-gray-900 dark:text-gray-100">ID</th>
                                                    <th class="text-center text-gray-900 dark:text-gray-100">DEPARTAMENTO</th>
                                                    <th class="text-center text-gray-900 dark:text-gray-100">MAX VAC/DÍA</th>
                                                    <th class="text-center text-gray-900 dark:text-gray-100">VAC/MES</th>
                                                    <th class="text-center text-gray-900 dark:text-gray-100">EDITAR</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(dep, dep_index) in filterDepartmentsByName()" :key="dep.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                                    <td class="text-center text-gray-900 dark:text-green-300 py-1"> {{ dep.id }} </td>
                                                    <!-- DEP.NAME SELECTOR -->
                                                    <td class="text-center text-gray-900 dark:text-gray-100">
                                                        <template v-if="isRowBeingEdited(dep_index, 1) && edit_state.row === dep_index && edit_state.table === 1">
                                                            <input type="text" v-model="edit_state.selected_department_name" @input="validateTextInputDepName" :placeholder="dep.name" class="w-24 text-xs text-left bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded p-1">
                                                        </template>
                                                        <template v-else>
                                                            {{ dep.name }}
                                                        </template>
                                                    </td>
                                                    <!-- MAX VAC SELECTOR -->
                                                    <td class="text-center text-gray-900 dark:text-gray-100">
                                                        <template v-if="isRowBeingEdited(dep_index, 1) && edit_state.row === dep_index && edit_state.table === 1">
                                                            <input type="number" min="0" max="5" v-model="edit_state.selected_department_users_same_day_holidays" @input="validateInputMaxVacationsPerDay" :placeholder="dep?.users_same_day_holidays" class="w-12 text-xs text-left bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded p-1">
                                                        </template>
                                                        <template v-else>
                                                            {{ dep?.users_same_day_holidays }}
                                                        </template>
                                                    </td>
                                                    <!-- ZONE SELECTOR -->
                                                    <td class="text-center text-gray-900 dark:text-gray-100">
                                                        <template v-if="isRowBeingEdited(dep_index, 1) && edit_state.row === dep_index && edit_state.table === 1">
                                                            <input type="number" min="0" max="5" v-model="edit_state.selected_department_holidays_per_month" @input="validateInputVacationsPerMonth" :placeholder="dep?.holidays_per_month" class="w-12 text-xs text-left bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded p-1">
                                                        </template>
                                                        <template v-else>
                                                            {{ dep?.holidays_per_month }}
                                                        </template>
                                                    </td>

                                                    <td class="text-center text-gray-900 dark:text-gray-100">
                                                        <template v-if="isRowBeingEdited(dep_index, 1) && edit_state.row === dep_index && edit_state.table === 1">
                                                            <button @click="saveDepartmentChanges(dep.id)">
                                                                <i class="mx-1 text-lg text-green-400 fa-solid fa-check"></i>
                                                            </button>
                                                            <button @click="closeEditing(dep_index, 1)">
                                                                <i class="mx-1 text-lg text-red-400 fa-solid fa-xmark"></i>
                                                            </button>
                                                        </template>
                                                        <template v-else>
                                                            <button @click="startEditing(dep_index, 1)">
                                                                <i class="space-x-1 sm:ml-1 text-lg text-yellow-200 fa-regular fa-pen-to-square"></i>
                                                            </button>
                                                        </template>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- END SLOT -->

                                </div>
                            </div> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
<!-- CHECKBOX DEPARTMENTS END -->

<!-- CHECKBOX CONTRACTS -->
        <div v-if="userStore.hasPermission('view contracts')" class="w-full pt-4"> <!-- CHECKBOX CONTRACTS START -->
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="py-2 flex justify-center items-center space-x-5 text-gray-900 dark:text-gray-100">
                        <div class="w-full bg-white dark:bg-gray-800 rounded-md">
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg" :class="[(contracts_toggled) ? 'admin_toggled' : '',]">
                                <div class="py-2 flex justify-between items-center space-x-5 text-gray-900 dark:text-gray-100 bg-gray-800 px-4">
                                    <div class="text-xl font-bold" style="font-family: 'Abel', sans-serif;"> CONTRATOS</div>
                                    <div class="flex items-center">
                                    <!-- Toggle Switch -->
                                    <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="sr-only peer" :checked="contracts_toggled" @change="contracts_toggled=!contracts_toggled">
                                            <div
                                            class="w-12 h-6 bg-gray-300 rounded-full border-2 ring-white peer-checked:bg-gray-700 
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
                                <div v-if="contracts_toggled" class=" overflow-hidden p-2 pb-8 bg-gray-800 grid justify-center items-center space-x-5 text-gray-900 dark:text-gray-100">

                                    <!-- SLOT -->
                                    <div class="bg-gray-800">
                                        <!-- Campo de búsqueda -->
                                        <input
                                        type="text"
                                        v-model="searchQueryContracts"
                                        placeholder="Buscar por nombre..."
                                        class="w-full p-1 border text-gray-950 border-gray-300 rounded mb-4"
                                        />
                                        <!-- Fin campo de búsqueda -->
                                        <div v-if="job_ranges.length" class="overflow-hidden rounded">
                                            <table class="sm:w-[32rem] w-[24rem] sm:text-md text-sm text-left text-gray-500 dark:text-gray-400 bg-slate-600">
                                                <thead>
                                                    <tr class="justify-center item-center">
                                                        <th class="text-center text-gray-900 dark:text-gray-100 border">ID</th>
                                                        <th class="text-center text-gray-900 dark:text-gray-100 border">DATOS</th>
                                                        <th class="text-center text-gray-900 dark:text-gray-100 border">EDITAR</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(job, job_index) in paginatedContracts" :key="job.id" class="bg-white border dark:bg-gray-800 dark:border-gray-700">
                                                        <td class="text-center text-sm text-gray-900 dark:text-green-300 bg-gray-700 py-1 border-y-2"> 
                                                            <div>
                                                                <span class="font-bold text-lg">{{ job.id }}</span> 
                                                            </div>
                                                        </td>
                                                        <td class="text-center text-sm text-gray-900 dark:text-white bg-gray-700 py-1 border-y-2"> 
                                                            <div class="flex-col justify-items-start">
                                                                <div class="bg-gray-700 w-full flex justify-between px-2">
                                                                    <span class="text-yellow-500 font-bold">Nombre:</span> {{ job.user_range.name }}
                                                                </div>
                                                                <div class="bg-gray-600 w-full flex justify-between px-2">
                                                                    <span class="text-green-300 font-bold">Fecha de inicio:</span> {{ job.start_date }}
                                                                </div>
                                                                <div class="bg-gray-700 w-full flex justify-between px-2">
                                                                    <span class="text-red-300 font-bold">Fecha de final:</span> {{ job.end_date }}
                                                                </div>
                                                                <div class="bg-gray-600 w-full flex justify-between px-2">
                                                                    <span class="text-yellow-300 font-bold">Tipo de contrato:</span> {{ job.contract_type.name }}
                                                                </div>
                                                                <div class="bg-gray-700 w-full flex justify-between px-2">
                                                                    <span class="text-yellow-300 font-bold">Horas laborables al día:</span> {{ job.contract_type.working_hours }}
                                                                </div>
                                                                <div class="bg-gray-600 w-full flex justify-between px-2">
                                                                    <span class="text-yellow-300 font-bold">Fines de semana laborables:</span> <span v-if="job.work_on_freedays" class="text-green-400"> <i class="fa-solid fa-check"></i></span> <span v-else class="text-red-400"><i class="fa-solid fa-x"></i> </span>
                                                                </div>
                                                                <div class="bg-gray-700 w-full flex justify-between px-2">
                                                                    <span class="text-cyan-200 font-bold">Departamento:</span> {{ job.user_range.department.name }}
                                                                </div>
                                                                <div class="bg-gray-600 w-full flex justify-between px-2">
                                                                    <span class="text-cyan-200 font-bold">Vacaciones por mes:</span>  {{ job.user_range.department.holidays_per_month }}
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <!-- DEP.NAME SELECTOR -->
                                                        <td class="text-center text-sm text-gray-900 dark:text-gray-100 bg-gray-700 py-1 border-y-2">
    <!--                                                         <template v-if="isRowBeingEdited(job_index, 2) && edit_state.row === job_index && edit_state.table === 2">
                                                                <input type="text" v-model="edit_state.selected_department_name" @input="validateTextInputDepName" :placeholder="dep.name" class="w-24 text-xs text-left bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded p-1">
                                                            </template>
                                                            <template v-else>
                                                                {{ dep.name }}
                                                            </template> -->
                                                            <i class="text-lg text-yellow-300 fa-regular fa-pen-to-square"></i>
                                                        </td>
                                                        
                                                        <!-- MAX VAC SELECTOR -->

    <!--                                                         <template v-if="isRowBeingEdited(job_index, 2) && edit_state.row === job_index && edit_state.table === 2">
                                                                <input type="number" min="0" max="5" v-model="edit_state.selected_department_users_same_day_holidays" @input="validateInputMaxVacationsPerDay" :placeholder="dep?.users_same_day_holidays" class="w-12 text-xs text-left bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded p-1">
                                                            </template>
                                                            <template v-else>
                                                                {{ dep?.users_same_day_holidays }}
                                                            </template> -->

                                                        <!-- ZONE SELECTOR -->
                                                        
    <!--                                                         <template v-if="isRowBeingEdited(job_index, 2) && edit_state.row === job_index && edit_state.table === 2">
                                                                <input type="number" min="0" max="5" v-model="edit_state.selected_department_holidays_per_month" @input="validateInputVacationsPerMonth" :placeholder="dep?.holidays_per_month" class="w-12 text-xs text-left bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded p-1">
                                                            </template>
                                                            <template v-else>
                                                                {{ dep?.holidays_per_month }}
                                                            </template> -->


                                                        
    <!--                                                         <template v-if="isRowBeingEdited(job_index, 2) && edit_state.row === job_index && edit_state.table === 2">
                                                                <button @click="saveDepartmentChanges(dep.id)">
                                                                    <i class="mx-1 text-lg text-green-400 fa-solid fa-check"></i>
                                                                </button>
                                                                <button @click="closeEditing(job_index, 2)">
                                                                    <i class="mx-1 text-lg text-red-400 fa-solid fa-xmark"></i>
                                                                </button>
                                                            </template>
                                                            <template v-else>
                                                                <button @click="startEditing(job_index, 2)">
                                                                    <i class="space-x-1 sm:ml-1 text-lg text-yellow-200 fa-regular fa-pen-to-square"></i>
                                                                </button>
                                                            </template> -->

                                                    </tr>
                                                </tbody>
                                            </table>
                                            <div class="sm:w-[32rem] w-[24rem] sm:text-md text-sm text-left text-gray-100 dark:text-gray-400 bg-slate-600 px-2">
                                                <button class="text-gray-500 border rounded px-1 bg-slate-200" :disabled="currentPageContracts === 1" :class="[(currentPageContracts===1) ? 'bg-slate-800' : '']" @click="currentPageContracts--">Anterior</button>
                                                <span class="text-gray-100"> Pág. <<<span class="font-bold text-yellow-400 text-md">{{ currentPageContracts }}</span> de {{ totalPagesContracts }}>> </span>
                                                <button class="text-gray-500 border rounded px-1 bg-slate-200" :disabled="currentPageContracts === totalPagesContracts" :class="[(currentPageContracts===totalPagesContracts) ? 'bg-slate-800' : '']" @click="currentPageContracts++"> Siguiente</button>
                                            </div>
                                        </div>
                                        <div v-else>
                                            <p class="text-red-400 py-8">Actualmente no hay ningún contrato activo.</p>
                                        </div>
                                    </div>
                                    <!-- END SLOT -->

                                </div>
                            </div> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
<!-- CHECKBOX CONTRACTS END -->

<!-- CHECKBOX CONTRACT TYPES -->
        <div v-if="userStore.hasPermission('view contract types')" class="w-full pt-4"> <!-- CHECKBOX CONTRACT TYPES -->
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="py-2 flex justify-center items-center space-x-5 text-gray-900 dark:text-gray-100">
                        <div class="w-full bg-white dark:bg-gray-800 rounded-md">
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg" :class="[(contract_types_toggled) ? 'admin_toggled' : '',]">
                                <div class="py-2 flex justify-between items-center space-x-5 text-gray-900 dark:text-gray-100 bg-gray-800 px-4">
                                    <div class="text-xl font-bold" style="font-family: 'Abel', sans-serif;"> TIPOS DE CONTRATO</div>
                                    <div class="flex items-center">
                                    <!-- Toggle Switch -->
                                    <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="sr-only peer" :checked="contract_types_toggled" @change="contract_types_toggled=!contract_types_toggled">
                                            <div
                                            class="w-12 h-6 bg-gray-300 rounded-full border-2 ring-white peer-checked:bg-gray-700 
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
                                <div v-if="contract_types_toggled" class="p-2 pb-8 bg-gray-800 grid justify-center items-center space-x-5 text-gray-900 dark:text-gray-100">

                                    <!-- SLOT -->
                                    <div class="sm:flex sm:justify-between items-center grid">
                                        SLOT
                                    </div>
                                    <!-- END SLOT -->

                                </div>
                            </div> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
<!-- CHECKBOX CONTRACT TYPES END -->

<!-- CHECKBOX ZONES -->
        <div v-if="userStore.hasPermission('view zones')" class="w-full pt-4"> <!-- CHECKBOX ZONES -->
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="py-2 flex justify-center items-center space-x-5 text-gray-900 dark:text-gray-100">
                        <div class="w-full bg-white dark:bg-gray-800 rounded-md">
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg" :class="[(zones_toggled) ? 'admin_toggled' : '',]">
                                <div class="py-2 flex justify-between items-center space-x-5 text-gray-900 dark:text-gray-100 bg-gray-800 px-4">
                                    <div class="text-xl font-bold" style="font-family: 'Abel', sans-serif;"> ZONAS</div>
                                    <div class="flex items-center">
                                    <!-- Toggle Switch -->
                                    <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="sr-only peer" :checked="zones_toggled" @change="zones_toggled=!zones_toggled">
                                            <div
                                            class="w-12 h-6 bg-gray-300 rounded-full border-2 ring-white peer-checked:bg-gray-700 
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
                                <div v-if="zones_toggled" class="p-2 pb-8 bg-gray-800 grid justify-center items-center space-x-5 text-gray-900 dark:text-gray-100">

                                    <!-- SLOT -->
                                    <div class="sm:flex sm:justify-between items-center grid">
                                        SLOT
                                    </div>
                                    <!-- END SLOT -->

                                </div>
                            </div> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
<!-- CHECKBOX ZONES END -->

<!-- CHECKBOX NON WORKING DAYS -->
        <div v-if="userStore.hasPermission('view non working days')" class="w-full pt-4"> <!-- CHECKBOX NON WORKING DAYS -->
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="py-2 flex justify-center items-center space-x-5 text-gray-900 dark:text-gray-100">
                        <div class="w-full bg-white dark:bg-gray-800 rounded-md">
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg" :class="[(non_working_days_toggled) ? 'admin_toggled' : '',]">
                                <div class="py-2 flex justify-between items-center space-x-5 text-gray-900 dark:text-gray-100 bg-gray-800 px-4">
                                    <div class="text-xl font-bold" style="font-family: 'Abel', sans-serif;"> FESTIVOS</div>
                                    <div class="flex items-center">
                                    <!-- Toggle Switch -->
                                    <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="sr-only peer" :checked="non_working_days_toggled" @change="non_working_days_toggled=!non_working_days_toggled">
                                            <div
                                            class="w-12 h-6 bg-gray-300 rounded-full border-2 ring-white peer-checked:bg-gray-700 
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
                                <div v-if="non_working_days_toggled" class="p-2 pb-8 bg-gray-800 grid justify-center items-center space-x-5 text-gray-900 dark:text-gray-100">

                                    <!-- SLOT -->
                                    <div class="sm:flex sm:justify-between items-center grid">
                                        SLOT
                                    </div>
                                    <!-- END SLOT -->

                                </div>
                            </div> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
<!-- CHECKBOX NON WORKING DAYS END -->

<!-- CHECKBOX EXTRA DAYS -->
        <div v-if="userStore.hasPermission('view extra days')" class="w-full pt-4"> <!-- CHECKBOX EXTRA DAYS -->
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="py-2 flex justify-center items-center space-x-5 text-gray-900 dark:text-gray-100">
                        <div class="w-full bg-white dark:bg-gray-800 rounded-md">
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg" :class="[(extra_days_toggled) ? 'admin_toggled' : '',]">
                                <div class="py-2 flex justify-between items-center space-x-5 text-gray-900 dark:text-gray-100 bg-gray-800 px-4">
                                    <div class="text-xl font-bold" style="font-family: 'Abel', sans-serif;"> DÍAS EXTRA</div>
                                    <div class="flex items-center">
                                    <!-- Toggle Switch -->
                                    <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="sr-only peer" :checked="extra_days_toggled" @change="extra_days_toggled=!extra_days_toggled">
                                            <div
                                            class="w-12 h-6 bg-gray-300 rounded-full border-2 ring-white peer-checked:bg-gray-700 
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
                                <div v-if="extra_days_toggled" class="p-2 pb-8 bg-gray-800 grid justify-center items-center space-x-5 text-gray-900 dark:text-gray-100">

                                    <!-- SLOT -->
                                    <div class="sm:flex sm:justify-between items-center grid">
                                        SLOT
                                    </div>
                                    <!-- END SLOT -->

                                </div>
                            </div> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
<!-- CHECKBOX EXTRA DAYS END -->

<!-- CHECKBOX EXTRA DAY TYPES -->
        <div v-if="userStore.hasPermission('view extra day types')" class="w-full pt-4"> <!-- CHECKBOX EXTRA DAY TYPES -->
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="py-2 flex justify-center items-center space-x-5 text-gray-900 dark:text-gray-100">
                        <div class="w-full bg-white dark:bg-gray-800 rounded-md">
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg" :class="[(extra_day_types_toggled) ? 'admin_toggled' : '',]">
                                <div class="py-2 flex justify-between items-center space-x-5 text-gray-900 dark:text-gray-100 bg-gray-800 px-4">
                                    <div class="text-xl font-bold" style="font-family: 'Abel', sans-serif;"> TIPOS DE DÍAS EXTRA</div>
                                    <div class="flex items-center">
                                    <!-- Toggle Switch -->
                                    <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="sr-only peer" :checked="extra_day_types_toggled" @change="extra_day_types_toggled=!extra_day_types_toggled">
                                            <div
                                            class="w-12 h-6 bg-gray-300 rounded-full border-2 ring-white peer-checked:bg-gray-700 
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
                                <div v-if="extra_day_types_toggled" class="p-2 pb-8 bg-gray-800 grid justify-center items-center space-x-5 text-gray-900 dark:text-gray-100">

                                    <!-- SLOT -->
                                    <div class="sm:flex sm:justify-between items-center grid">
                                        SLOT
                                    </div>
                                    <!-- END SLOT -->

                                </div>
                            </div> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
<!-- CHECKBOX EXTRA DAY TYPES END -->

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