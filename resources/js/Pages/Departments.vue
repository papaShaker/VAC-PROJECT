<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import { ref, reactive, onBeforeMount, onMounted, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    departments: Array,
});

const calendar_data = reactive({});
const expand = ref(null);
const users = ref([]);
const selected_department_id = ref('');
const selected_department_name = ref('');
const selected_month = ref('');
const selected_month_name = ref('');
const user = ref(null);
const year = ref(null);
const is_admin = ref(null);
const vacations_index = reactive({});
const vacations_calendar = reactive({});
const currentDate = new Date();
const loading = ref(true);
const spinner = ref(false);
const months = reactive([]);
const week_days = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sáb', 'Dom'];
const holidays_data = reactive([]);
const selected_month_check = ref(false);


const fetchUsers = (department_id) => {
    axios.get(`/departments/${department_id}/users`).then((response) => {
        users.value = response.data;
    }).catch(error => {
        console.error('Error', error);
    })
};

const fetchDepartmentNameById = async (department_id) => {
    while (holidays_data.length > 0) {
        holidays_data.pop();
    }
    console.log("entra");
    try {
        await axios.get('/api/department_name/' + department_id)
            .then((response) => {
                selected_department_name.value = response.data;
                console.log(selected_department_name.value);
            });
    } catch (error) {

    }
}

const getUserById = async (user_id) => {
    try {
        console.log(user_id);
        const response = await axios.get('/api/user/' + user_id);
        return response.data;
        // Now you can safely access the user data here
    } catch (error) {
        console.error('Error fetching user:', error);
    }
};

const fetchCalendarData = async (consulted_year = null, consulted_department_id, consulted_month) => {
    loading.value = true;
    spinner.value = false;
    selected_month_check.value = false;
    year.value = consulted_year === null ? new Date().getFullYear() : consulted_year
    let url = (consulted_department_id === null && consulted_month == null ? 'api/calendar_department/' + year.value : (consulted_month) ? 'api/calendar_department/' + year.value + '/' + consulted_department_id + '/' + consulted_month : 'api/calendar_department/' + year.value + '/' + consulted_department_id);
    consulted_year = year.value;
    await axios.get(url)
        .then((response) => {
            loading.value = false;
            spinner.value = true;
            // Empty the existing content of calendar_data
            Object.keys(calendar_data).forEach(key => delete calendar_data[key]);
            Object.assign(calendar_data, response.data);
            console.log(calendar_data);
            getHolidaysRelevantInfo(response.data);

            // Check if any entry has is_holiday == 1
            const hasHoliday = response.data.some((index) =>
                index.days && Object.values(index.days).some(day => day.is_holiday === 1)
            );

            // Set selected_month_check based on the presence of is_holiday == 1
            selected_month_check.value = hasHoliday;
        })
        .catch((error) => { console.error(error) });
}

const getHolidaysRelevantInfo = (passed_data) => {
    console.log("Entra a Relevant");
    while (holidays_data.length > 0) {
        holidays_data.pop();
    }

    passed_data.forEach(index => {
        let now = new Date().toISOString().slice(0, 10);
        let data = [{ days: {} }];
        let days = index.days;
        console.log(now);
        for (let date in days) {
            let dayEntry = days[date];
            if (dayEntry.is_holiday > 0) {
                dayEntry.holidays.forEach(holiday => {
                    if (date < now) {

                    }
                    holidays_data.push({
                        date: date,
                        user_name: holiday.user_name,
                        user_holiday_status: (date <= now) ? "Disfrutadas" : (holiday.user_holiday_status === 2) ? "Confirmadas" : "Pendientes"
                    });
                });
            }

        }
        holidays_data.reverse();
    });
    console.log(holidays_data);
};

const generateMonthOptions = async () => {
    let now = new Date();
    let currentYear = now.getFullYear();
    let monthNames = [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    for (let i = 0; i < 12; i++) {
        months.push({
            label: `${monthNames[i]} ${currentYear}`,
            value: `${currentYear}-${String(i + 1).padStart(2, '0')}`
        });
    }
    months.push({
        label: `Enero ${currentYear + 1}`,
        value: `${currentYear + 1}-01`
    });

    months.push({
        label: `Todos los meses`,
        value: ``
    });
};

const selected_month_label = () => {
    let selectedMonth = months.find(month => month.value === selected_month.value);
    if (selectedMonth) {
        selected_month_name.value = selectedMonth.label; // Set the label if found
        console.log("Selected month label:", selected_month_name.value);
    } else {
        selected_month_name.value = "null"; // Set to "null" if not found
        console.log("No month found for value:", selected_month.value);
    }
};

const capitalize = (value) => {
    if (!value) return '';
    return value.charAt(0).toUpperCase() + value.slice(1);
};

onBeforeMount( async () => {
    await generateMonthOptions();
})


</script>

<template>

    <Head title="Departamentos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl text-gray-800 dark:text-gray-200 leading-tight"
                style="font-family: 'Abel', sans-serif;">Bienvenido a Departamentos
            </h2>
        </template>

        <div class="w-full py-12"> <!-- Container-fluid -->
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 flex justify-center items-center space-x-5 text-gray-900 dark:text-gray-100">
                        <h4 class="flex">Departamento:</h4>
                        <form class="flex w-3/12 max-w-sm mx-auto items-center" @submit.prevent="">
                            <label for="departments" class="text-xl font-medium text-gray-900 dark:text-white">
                            </label>
                            <select id="departments" v-model="selected_department_id"
                                @change="fetchDepartmentNameById(selected_department_id);"
                                class="flex-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option selected disabled value="">Selecciona un departamento</option>
                                <option value="">
                                    Todos los departamentos
                                </option>
                                <option v-for="department in departments" :key="department.id" :value="department.id">
                                    {{ department.name }}
                                </option>
                            </select>
                        </form>
                        <h4 class="flex items-center">Mes:</h4>
                        <form class="flex w-2/12 max-w-sm mx-auto items-center">
                            <label for="month"></label>
                            <select
                                class="flex-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                :disabled="!selected_department_id" v-model="selected_month"
                                @change="fetchCalendarData(null, selected_department_id, selected_month); selected_month_label();">
                                <option value="" disabled selected>Selecciona un mes</option>
                                <!-- Placeholder option -->
                                <option v-for="month in months" :key="month.value" :value="month.value">
                                    {{ month.label }}
                                </option>
                            </select>
                        </form>
                    </div>
                    <div class="p-4 flex items-center justify-center text-gray-900 dark:text-gray-100">
                        <!-- START BOX -->
                        <div v-if="!loading">
                            <!-- PY-5 -->
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="px-3 text-gray-900 dark:text-gray-100">
                                    <!-- Vacations header info (user-year) -->
                                    <p v-if="selected_month" class="mt-2 mb-4 text-xl text-gray-300"
                                        style="font-family: 'Abel', sans-serif;"> Vacaciones de <span
                                            class="text-white">{{
                                                selected_department_name }}</span> - <span class="text-white">
                                            {{ selected_month_name }} </span>
                                    </p>
                                </div>
                                <div class="flex justify-around mb-5"> <!-- Legend container -->
                                    <div class="flex justify-around mx-2 items-center"> <!-- Legend div -->
                                        <div class="w-6 h-6 mr-1.5 today rounded-md"><!-- Today -->
                                        </div>
                                        <div>
                                            <p class="text-white">Hoy.</p>
                                        </div>
                                    </div>
                                    <div class="flex justify-around mx-2 items-center"> <!-- Legend div -->
                                        <div class="w-6 h-6 mr-1.5 bg-yellow-100 rounded-md">
                                            <!-- Pending holidays -->
                                        </div>
                                        <div>
                                            <p class="text-white">Vacaciones pendientes.</p>
                                        </div>
                                    </div>
                                    <div class="flex justify-around mx-2 items-center"> <!-- Legend div -->
                                        <div class="w-6 h-6 mr-1.5 bg-green-400 rounded-md">
                                            <!-- Confirmed holidays -->
                                        </div>
                                        <div>
                                            <p class="text-white">Vacaciones confirmadas.</p>
                                        </div>
                                    </div>
                                    <div class="flex justify-around mx-2 items-center"> <!-- Legend div -->
                                        <div class="w-6 h-6 mr-1.5 bg-red-300 rounded-md"><!-- Free days -->
                                        </div>
                                        <div>
                                            <p class="text-white">Día festivo / Fin de semana.</p>
                                        </div>
                                    </div>
                                    <div class="flex justify-around mx-2 items-center"> <!-- Legend div -->
                                        <div class="w-6 h-6 mr-1.5 work_on_freedays rounded-md">
                                            <!-- Work on free days -->
                                        </div>
                                        <div>
                                            <p class="text-white">Fin de semana laborable.</p>
                                        </div>
                                    </div>
                                    <div class="flex justify-around mx-2 items-center"> <!-- Legend div -->
                                        <div class="w-6 h-6 mr-1.5 locked_already_requested_day rounded-md">
                                            <!-- Limit users vacations same day -->
                                        </div>
                                        <div>
                                            <p class="text-white">No disponible.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 flex justify-around flex-wrap text-white mb-4"
                                    > <!-- START OF DIV -->
                                    <!-- calendarios - Calendar outer div -->
                                    <div class="relative w-72 box-border shadow-lg rounded-lg overflow-hidden border border-solid border-gray-600 m-2"
                                        :class="[month.previous_month ? 'past_month' : '']"
                                        v-for="(month, key) in calendar_data" :key="key"> <!-- calendar -->
                                        <div class="w-full text-center text-[22px] m-0 text-gray-200 bg-gray-700">
                                            <!-- calendar__mes - Month name - Header -->
                                            {{ capitalize(month.month_name) }}
                                        </div>
                                        <div
                                            class="p-5 grid grid-cols-[repeat(auto-fit,minmax(25px,1fr))] gap-2.5 box-border">
                                            <!-- calendar__date - Month body -->
                                            <div v-for="day in week_days" :key="day"
                                                class="flex items-center justify-center h-[25px] font-semibold text-white">
                                                <!-- calendar__day - Week headings - v-for -->
                                                <div>{{ day }}</div>
                                            </div>
                                            <div @click="(!days.day.locked_already_requested_day && days.day.range_working_hours != 0) || user.value.admin ? selectDay(days, key, month) : false"
                                                v-for="(days, key) in month.days" :key="key"
                                                class="flex items-center justify-center h-6 text-gray-300 cursor-pointer rounded-md"
                                                :class="[month.prevous_month ? 'past_month_text' : '',
                                                (days.free_day && !days.work_on_freedays) ? 'free_day' : '',
                                                (days.free_day && days.work_on_freedays) ? 'work_on_freedays' : '',
                                                days.holiday == 2 ? 'confirmed_holiday' : '',
                                                days.day_selected ? 'day_selected' : '',
                                                days.locked_already_requested_day && days.holiday == 0 ? 'locked_already_requested_day' : '',
                                                days.range_working_hours == 0 ? 'not_ranged_day' : '',
                                                days.holiday == 1 ? 'pending_holiday' : '',
                                                days.day_state == 1 ? 'today' : !month.previous_month && days.day_state == 0 ? 'passed_day' : '']">
                                                <!-- calendar__number - actual month dates-->
                                                <div class="">{{ days.day }}</div>
                                            </div>
                                        </div>
                                    </div> <!-- v-for - each Month div -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- START TABLE -->

                    <div class="pb-14 flex items-center justify-center text-gray-900 dark:text-gray-100"
                        v-if="selected_month_check">
                        <div v-if="holidays_data.length > 0" class="relative overflow-x-auto mt-5">
                            <h4 class="header mt-2"> {{ selected_month_name.value }}</h4>
                            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                <thead
                                    class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Fecha</th>
                                        <th scope="col" class="px-6 py-3">Usuario</th>
                                        <th scope="col" class="px-6 py-3">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700"
                                        v-for="holiday in holidays_data" :key="holiday">
                                        <td scope="row"
                                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{
                                                holiday.date }}</td>
                                        <td scope="row"
                                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{
                                                holiday.user_name }}</td>
                                        <td scope="row"
                                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-gray-600 italic"
                                            :class="[(holiday.user_holiday_status == 'Confirmadas') ? 'bg-green-300' : (holiday.user_holiday_status == 'Disfrutadas') ? 'bg-orange-300' : 'bg-yellow-100']">
                                            {{ holiday.user_holiday_status }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div v-else-if="!spinner && selected_department_name && selected_month" role="status"
                        class="flex items-center justify-center mb-12"> <!-- WHILE LOADING -->
                        <svg aria-hidden="true"
                            class="inline w-10 h-10 text-gray-200 animate-spin dark:text-gray-600 fill-green-500"
                            viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                fill="currentColor" />
                            <path
                                d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                fill="currentFill" />
                        </svg>
                        <span class="sr-only">Loading...</span>
                    </div>

                    <!-- END TABLE -->

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
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
</style>