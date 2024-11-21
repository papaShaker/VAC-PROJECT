<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, reactive, onMounted, onBeforeMount, toRaw } from 'vue';
import axios from 'axios';
import moment from 'moment';
import { DateTime } from 'luxon';
import { toast } from 'vue3-toastify';

const props = defineProps({
    departments: Array,
});

const weekly_schedule = reactive({});
const today = new Date();
const week_date_range_dates = reactive([]);
const week_numbers_by_month = reactive([]);
const week_dates_by_month = reactive([]);
const selected_department_id = ref(null);
const selected_month = ref(null);
const months = reactive([]);
const selected_department_name = ref(null);
const selected_month_name = ref(null);
const selected_week = ref(null);
const loading = ref(false);
const formatted_data_indexes = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
const schedule_hour_selector = {
    '00:00': '00:00h', '01:00': '01:00h', '02:00': '02:00h', '03:00': '03:00h',
    '04:00': '04:00h', '05:00': '05:00h', '06:00': '06:00h', '07:00': '07:00h',
    '08:00': '08:00h', '09:00': '09:00h', '10:00': '10:00h', '11:00': '11:00h',
    '12:00': '12:00h', '13:00': '13:00h', '14:00': '14:00h', '15:00': '15:00h',
    '16:00': '16:00h', '17:00': '17:00h', '18:00': '18:00h', '19:00': '19:00h',
    '20:00': '20:00h', '21:00': '21:00h', '22:00': '22:00h', '23:00': '23:00h'
}
const is_editing = ref(false);
const edit_state = reactive({
    row: null,
    //column: null,
})

const toast_options = {
    closeOnClick: true,
    toastStyle: {
        marginRight: '5px',
        backgroundColor: '#374151',
        color: '#DFECEC',
        borderRadius: '4px',
        border: '1px solid #9CA3AF'
    }
}

const getWeeklySchedule = async (week_numb_param, dep_id_param) => {
    console.log("week_numb: " + week_numb_param)
    await axios.post('/schedule/weekly/' + week_numb_param + '/' + dep_id_param).then((response) => {
        Object.keys(weekly_schedule).forEach(key => {
            delete weekly_schedule[key];
        });
        Object.assign(weekly_schedule, response.data);
        console.log(response.data);
        if (response.data.status){
            toast.success(`${response.data.message}`, toast_options);
        }
    });
};


/* const getWeekNumber = (date) => {
    let first_jan = new Date(date.getFullYear(), 0, 1);
    let past_days_of_year = (date - first_jan + (first_jan.getTimezoneOffset() - date.getTimezoneOffset()) * 60000) / 86400000;
    return Math.ceil((past_days_of_year + first_jan.getDay() + 1) / 7);
} */

const getWeekNumber = (date) => {
    let today = moment();
    let week_number = today.isoWeek();
    return week_number;
}

function getWeekDateRange(date) {
      let day_of_week = date.getDay() || 7; // Get the current day or set to 7 if Sunday
      let start_date = new Date(date);
      start_date.setDate(date.getDate() - (day_of_week - 1)); // Get Monday of the current week
      let end_date = new Date(start_date);
      end_date.setDate(start_date.getDate() + 6); // Get Sunday of the current week
      return { start_date, end_date };
}

// Format the dates (e.g., '24-11-22')
const formatDate = (date) => {
    const year = date.getFullYear().toString().slice(-2); // Get last 2 digits of the year
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Add leading zero if needed
    const day = String(date.getDate()).padStart(2, '0'); // Add leading zero if needed
    return `${day}-${month}-${year}`;
};

const fetchWeekDates = async (year, week_number) => { //Dates for the table thead
    loading.value = true;
    await axios.get('/api/week_dates/' + year + '/' + week_number)
        .then((response) => {
            week_date_range_dates.length = 0; // Clear the existing data (optional)
            week_date_range_dates.push(...response.data); // Add new data to the reactive array
            console.log(week_date_range_dates);
            loading.value = false;
        })
}

// Example of a function to get week numbers and their date ranges
const getWeeksForMonth = (year, month) => {
    week_dates_by_month.length = 0;
    let startOfMonth = DateTime.fromObject({ year, month, day: 1 });

    let currentWeekNumber = startOfMonth.weekNumber;
    let currentStartDate = startOfMonth.startOf('week');
    let currentEndDate = currentStartDate.plus({ days: 6 });

    // Loop through and create an array of week objects with the start and end date
    for (let day = 1; day <= startOfMonth.daysInMonth; day++) {
        let currentDate = DateTime.fromObject({ year, month, day });
        if (currentDate.weekNumber !== currentWeekNumber) {
            week_dates_by_month.push({
            weekNumber: currentWeekNumber,
            startDate: currentStartDate.toFormat('dd-MM-yyyy'),
            endDate: currentEndDate.toFormat('dd-MM-yyyy'),
            });
            currentWeekNumber = currentDate.weekNumber;
            currentStartDate = currentDate.startOf('week');
            currentEndDate = currentStartDate.plus({ days: 6 });
        }
    }

        // Push the last week
        week_dates_by_month.push({
        weekNumber: currentWeekNumber,
        startDate: currentStartDate.toFormat('dd-MM-yyyy'),
        endDate: currentEndDate.toFormat('dd-MM-yyyy'),
    });
    console.log("weekdate year: " + week_dates_by_month.year);
};

const checkTodayMatch = (date) => {
    let today = moment().format('DD-MM-YYYY');
    console.log("Checking date:", date, "today:", today);
    return today === date;
}

const fetchWeekNumbersByMonth = async (year, month) => {
    await axios.get('/api/week_numbers_by_month/' + year + '/' + month)
        .then((response) => {
            week_numbers_by_month.length = 0; // Clear the existing data (optional)
            week_numbers_by_month.push(...response.data); // Add new data to the reactive array
            console.log("entra");
        })
}
    
const fetchDepartmentNameById = async (department_id) => {
    try {
        await axios.get('/api/department_name/' + department_id)
            .then((response) => {
                selected_department_name.value = response.data;
                console.log(selected_department_name.value);
            });
    } catch (error) {

    }
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
};

const selected_month_label = () => {
    console.log(selected_month.value);
    let selectedMonth = months.find(month => month.value === selected_month.value);
    if (selectedMonth) {
        selected_month_name.value = selectedMonth.label; // Set the label if found
        console.log("Selected month label:", selected_month_name.value);
    } else {
        selected_month_name.value = "null"; // Set to "null" if not found
        console.log("No month found for value:", selected_month.value);
    }
};

// Function to get the year
function getSelectedYear() {
    return selected_month.value.split('-')[0]; // Splits by "-" and returns the first part (the year)
}

// Function to get the month
function getSelectedMonth() {
    let month = parseInt(selected_month.value.split('-')[1], 10);
    return month; // Splits by "-" and returns the second part (the month)
}

const capitalize = (value) => {
    if (!value) return '';
    return value.charAt(0).toUpperCase() + value.slice(1);
};

function formatTimeString(timeString) {
    // Split the time string by `:` and take the first two components (hours and minutes)
    let [hours, minutes] = timeString.split(':');
    return `${hours}:${minutes}`;
}

const startEditing = (row_index) =>  {
    console.log("entra | row: " + row_index);
    is_editing.value = true;
    edit_state.row = row_index;
}

const closeEditing = (row_index) => {
    is_editing.value = false;
    edit_state.row = null;
}

const saveChanges = async (week_number, department_id, schedule_data) => {
    let raw_data = toRaw(schedule_data.weekly_schedule.schedule_data);
    let data_to_stringify = structuredClone(raw_data);

    loading.value = true;
    await axios.post('/schedule/saveweeklyschedule', {week_number: week_number, department_id: department_id, schedule_data: data_to_stringify}, 
        {headers: {
            'Content-Type': 'application/json',
    }},)
        .then((response) => {
            Object.keys(weekly_schedule).forEach(key => {
                delete weekly_schedule[key];
            });
            console.log(response.data);
            Object.assign(weekly_schedule, response.data);
            fetchWeekDates(getSelectedYear(selected_month), selected_week.weekNumber);
            toast.success(`${response.data.message}`, toast_options);
        });

    is_editing.value = false;
    edit_state.row = null;    
}

const updateHolidayCheckbox = (user_day_data, is_checked) => {
    user_day_data.is_holiday = is_checked; // Update `is_holiday`
    user_day_data.holiday_state = is_checked ? 2 : 0; // Update `holiday_state` (set to null or another default when unchecked)
}

onBeforeMount(async () => {
    getWeekDateRange(today);

    await generateMonthOptions();
})

onMounted(async () => {

});

</script>

<template>

    <Head title="Horario" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl text-gray-800 dark:text-gray-200 leading-tight"
                style="font-family: 'Abel', sans-serif;">Bienvenido a Horario</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 flex justify-center items-center space-x-5 text-gray-900 dark:text-gray-100">
                        <div class="flex justify-center items-center space-x-5 items_spacing_y"><!-- Dep -->
                            <h4 class="flex">Departamento:</h4>
                            <form class="flex max-w-sm mx-auto items-center" @submit.prevent="">
                                <label for="departments" class="text-xl font-medium text-gray-900 dark:text-white">
                                </label>
                                <select id="departments" v-model="selected_department_id"
                                    @change="fetchDepartmentNameById(selected_department_id);"
                                    class="flex-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                    <option selected disabled value="">Selecciona un departamento</option>
                                    <option v-for="department in departments" :key="department.id"
                                        :value="department.id">
                                        {{ department.name }}
                                    </option>
                                </select>
                            </form>
                        </div>
                        <div class="flex justify-center items-center space-x-5 items_spacing_y">
                            <h4 class="flex items-center">Mes:</h4>
                            <form class="flex max-w-sm mx-auto items-center">
                                <label for="month"></label>
                                <select
                                    class="flex-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    :disabled="!selected_department_id" v-model="selected_month"
                                    @change="selected_month_label(); getWeeksForMonth(getSelectedYear(selected_month), getSelectedMonth(selected_month))">
                                    <option value="" disabled selected>Selecciona un mes</option>
                                    <!-- Placeholder option -->
                                    <option v-for="month in months" :key="month.value" :value="month.value">
                                        {{ month.label }}
                                    </option>
                                </select>
                            </form>
                        </div>
                        <div class="flex justify-center items-center space-x-5 items_spacing_y">
                            <h4 class="flex items-center">Semana:</h4>
                            <form class="flex max-w-sm mx-auto items-center">
                                <label for="month"></label>
                                <select
                                    class="flex-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    :disabled="!selected_month" v-model="selected_week"
                                    @change="getWeeklySchedule(selected_week.weekNumber, selected_department_id); fetchWeekDates(getSelectedYear(selected_month), selected_week.weekNumber);">
                                    <option value="" disabled selected>Selecciona una semana</option>
                                    <!-- Placeholder option -->
                                    <option v-for="week in week_dates_by_month" :key="week" :value="week">
                                        Semana[{{ week.weekNumber }}] {{ week.startDate }} a {{ week.endDate }}
                                    </option>
                                </select>
                            </form>
                        </div>
                    </div>
                    <div class="flex justify-around mb-5 grid_style_legend"> <!-- Legend container -->
                        <div class="flex justify-around mx-2 items-center items_spacing_y"> <!-- Legend div -->
                            <div class="w-16 h-6 mr-1.5 today rounded-md"><!-- Today -->
                            </div>
                            <div>
                                <p class="text-white">Hoy.</p>
                            </div>
                        </div>
                        <div class="flex justify-around mx-2 items-center items_spacing_y"> <!-- Legend div -->
                            <div class="w-16 h-6 mr-1.5 this_week rounded-md"><!-- Today -->
                            </div>
                            <div>
                                <p class="text-white">Semana actual.</p>
                            </div>
                        </div>
                        <div class="flex justify-around mx-2 items-center items_spacing_y"> <!-- Legend div -->
                            <div class="w-16 h-6 mr-1.5 bg-yellow-600 rounded-md"><!-- Confirmed holidays -->
                            </div>
                            <div>
                                <p class="text-white">Vacaciones.</p>
                            </div>
                        </div>
                        <div class="flex justify-around mx-2 items-center items_spacing_y"> <!-- Legend div -->
                            <div class="w-16 h-6 mr-1.5 bg-green-400 rounded-md"><!-- Confirmed holidays -->
                            </div>
                            <div>
                                <p class="text-white">Día libre.</p>
                            </div>
                        </div>
                        <div class="flex justify-around mx-2 items-center items_spacing_y"> <!-- Legend div -->
                            <div class="w-16 h-6 mr-1.5 border-[2px] border-red-400 rounded-md"><!-- Free days -->
                            </div>
                            <div>
                                <p class="text-white">Guardia.</p>
                            </div>
                        </div>
                        <div class="flex justify-around mx-2 items-center items_spacing_y"> <!-- Legend div -->
                            <div class="w-16 h-6 mr-1.5 bg-yellow-100 rounded-md"><!-- Pending holidays -->
                            </div>
                            <div>
                                <p class="text-white">Baja.</p>
                            </div>
                        </div>
                    </div>

                    <!-- START TABLE -->

                    <div v-if="!loading" class="pb-14 flex items-center justify-center text-gray-900 dark:text-gray-100">
                        <div class="relative overflow-x-auto mt-5">
                            <h4 v-if="Object.keys(weekly_schedule).length > 0 && selected_week?.weekNumber" class="header m-2"> Semana: <span class="bold text-violet-400"> {{ selected_week?.weekNumber }} </span> | <span class="bold text-green-200"> {{ selected_week?.startDate }} </span> a <span class="bold text-green-200"> {{ selected_week?.endDate }} </span> | 
                                <button @click="getWeeklySchedule(selected_week.weekNumber, selected_department_id), closeEditing()" class="text-yellow-300"><i class="text-md text-yellow-300 fa-solid fa-rotate"></i> Recargar tabla</button> |
                                <button @click="saveChanges(selected_week.weekNumber, selected_department_id, weekly_schedule), closeEditing()" class="text-green-400"><i class="text- text-green-400 fa-solid fa-floppy-disk"></i> Grabar imagen</button>
                            </h4>
                            <table v-if="Object.keys(weekly_schedule).length > 0" class="w-full text-sm text-left rtl:text-right border dark:border-gray-600 text-gray-500 dark:text-gray-400">
                                <thead
                                    class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Turno</th>
                                        <th scope="col" class="px-6 py-3"
                                            v-for="(day_of_week, index) in formatted_data_indexes" :key="index"> {{ day_of_week }}
                                        </th>
                                        <th class="">Editar</th>
                                        <!-- <th scope="col" class="px-6 py-3"></th> -->
                                    </tr>
                                    <tr v-if="(week_date_range_dates.length > 0)">
                                        <th scope="col" class="px-6 py-1"></th>
                                        <th scope="col" class="px-6 py-1" v-for="date in week_date_range_dates" :key="date" >
                                            <span class="text-green-100" :class="[checkTodayMatch(date) ? 'today_th' : '', ]">{{ date }}</span>
                                        </th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Loop through each user data array -->
                                    <tr v-for="(user, user_index) in weekly_schedule.weekly_schedule.schedule_data" :key="user_index" class=" bg-white border-b dark:bg-gray-800 dark:border-gray-500" :class="[(user.user.id == 8 || user.user.id == 9) ? 'reduced_contract' : '']"><!-- :class="[(user.user.id == 8 || user.user.id == 9) ? 'reduced_contract' : '']" -->
                                            <td scope="row"
                                                class="px-3 py-4 font-medium text-gray-900 whitespace-nowrap border-r border-l bg-gray-600 dark:border-gray-500 dark:text-white"
                                                :class="[(user.user.id == 8 || user.user.id == 9) ? 'reduced_contract' : '',]"
                                                > <i class="fa-solid fa-user"></i> {{ user.user.name }}</td>
                                            <td v-for="(day, day_index) in Object.keys(user).filter(key => key !== 'user')" :key="day_index" scope="row"
                                                class="px-3 py-4 font-medium text-gray-900 whitespace-nowrap border-r border-l dark:border-gray-500 dark:text-white"
                                                :class="[(user[day].is_night_shift) ? 'is_night_shift' : '',
                                                        (user[day].start_time === '00:00' && user[day].end_time === '00:00' && (user.user.id == 8 || user.user.id == 9)) ? 'free_day reduced_contract' : 
                                                        (user[day].start_time === '00:00' && user[day].end_time === '00:00' && (user[day].is_weekend_day)) ? 'free_day' : user[day].is_weekend_day ? 'free_day' : '', 
                                                        (user[day].is_holiday) ? 'is_confirmed_holiday' : '', 
                                                        (user[day].is_not_available) ? 'is_not_available' : '', ]">
                                                <template v-if="is_editing && edit_state.row === user_index">
                                                    <select v-model="user[day].start_time" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-blue-500 focus:border-blue-500 block w-full p-1.5 my-1 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                                        <option selected disabled default :value="user[day].start_time">{{ user[day].start_time }}</option>
                                                        <option v-for="(value, key) in schedule_hour_selector" :key="key" :value="key">{{ value }}</option>
                                                    </select>
                                                    <select v-model="user[day].end_time" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-blue-500 focus:border-blue-500 block w-full p-1.5 my-1 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                                        <option selected :value="user[day].end_time">{{ user[day].end_time }}</option>
                                                        <option v-for="(value, key) in schedule_hour_selector" :key="key" :value="key">{{ value }}</option>
                                                    </select>
                                                    <div class="flex justify-center">
                                                        <input type="checkbox" v-model="user[day].is_weekend_day" :checked="user[day].is_weekend_day === true" class="mr-1 w-3.5 h-3.5 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-green-500 dark:border-gray-600"/>
                                                        <input type="checkbox" v-model="user[day].is_holiday" @change="updateHolidayCheckbox(user[day], $event.target.checked)" :checked="user[day].is_holiday === true" class="mr-1 w-3.5 h-3.5 text-yellow-500 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-yellow-500 dark:border-gray-600"/>
                                                        <input type="checkbox" v-model="user[day].is_night_shift" :checked="user[day].is_night_shift === true" class="mr-1 w-3.5 h-3.5 text-red-400 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-red-400 dark:border-gray-600"/>
                                                        <input type="checkbox" v-model="user[day].is_not_available" :checked="user[day].is_not_available === true" class="mr-1 w-3.5 h-3.5 text-yellow-300 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-yellow-200 dark:border-gray-600"/>
                                                    </div>
                                                </template>
                                                <template v-else>
                                                    <!-- {{ day_index }}: {{ day }} -->
                                                    {{ user[day].is_not_available ? 'BAJA' :
                                                    user[day].start_time === '00:00' && user[day].end_time === '00:00' && user[day].is_weekend_day ? 'LIBRE' : 
                                                    user[day].is_weekend_day ? 'LIBRE' :
                                                    user[day].is_holiday && user[day].holiday_state ===2 ? 'VACACIONES' :
                                                    user[day].start_time + ' - ' + user[day].end_time }}
                                                </template>
                                            </td>
                                            <td class="w-14 pl-2 flex-end">
                                                <template v-if="is_editing && edit_state.row === user_index">
                                                    <button @click="closeEditing(user_index)"><i class="p-1 m-1 text-2xl text-red-400 fa-solid fa-xmark"></i></button>
                                                </template>
                                                <template v-else>
                                                    <button @click="startEditing(user_index)"><i class="p-1 m-1 text-2xl text-yellow-200 fa-regular fa-pen-to-square"></i></button>
                                                </template>
                                            </td>
                                    </tr>
                                </tbody>
                            </table>
                            <p v-else>No hay datos sobre el departamento para este mes y semana.</p>
                        </div>
                    </div>
                                    <!--<tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td scope="row"
                                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white"></td>
                                        <td scope="row"
                                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white"></td>
                                        <td scope="row"
                                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-gray-600 italic"></td>
                                    </tr> -->

                                    <!-- END TABLE -->

                    <div v-if="loading" role="status" class="flex items-center justify-center mb-12"> <!-- WHILE LOADING -->
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

                    <button @click="fetchWeekDates(today.getFullYear(), getWeekNumber(today))">
                        <span class="text-white">Get week dates.</span>
                    </button>
                    <div></div>
                    <button @click="fetchWeekNumbersByMonth(today.getFullYear(), getWeekNumber(today))">
                        <span class="text-white">Get week numbers for month.</span>
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.today{
    border: 2px solid rgb(175, 175, 175) !important;
    cursor: context-menu;
}
.today_th{
    color: #4ade80 !important;
    cursor: context-menu;
}
.this_week{
    border: 2px dashed rgb(175, 175, 175) !important;
    cursor: context-menu;
}
.reduced_contract{
    box-shadow: inset 0px 0px 15px rgb(0, 255, 255, 0.3) !important;
}
.free_day{
    border-radius: 8px;
    background-color:#4ade80 !important;
    color: rgb(100, 107, 107);
}
.is_confirmed_holiday{
    border-radius: 8px;
    background-color:#ca8a04 !important;
    color: rgb(71, 71, 71);
    box-shadow: inset 0px 0px 15px rgba(251, 255, 0, 0.432) !important;
}
.is_not_available{
    border-radius: 8px;
    background-color:#fef9c3 !important;
    color: rgb(71, 71, 71);
    box-shadow: inset 0px 0px 15px rgba(251, 255, 0, 0.432) !important;
}
.is_night_shift{
    border-radius: 8px;
    border: 2px #f87171;
    box-shadow: inset 0 0 0 4px #f87171;
    z-index: 15;
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

<!-- 
{{ getWeekNumber(moment()) }}</span> | <span class="bold text-green-200">{{ formatDate(week_date_range.start_date) }}</span> a <span class="bold text-green-200">{{ formatDate(week_date_range.end_date) }}</span> -->


