<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, reactive, onMounted, onBeforeMount, toRaw, watch, defineProps, defineEmits } from 'vue';
import axios from 'axios';
import moment from 'moment';
import { DateTime } from 'luxon';
import { toast } from 'vue3-toastify';
import ModalSchedules from '@/Components/ModalSchedules.vue';

const props = defineProps({
    departments: Array,
});

const weekly_schedules_for_month = reactive([]);
const weekly_schedule = reactive([]);
const existing_template_data = reactive([]);
const existing_template_error = reactive([]);
const users_available = ref(null);
const last_weekly_schedule_image = reactive([]);
const today = new Date();
const consolidated_schedules = reactive([]);
const week_date_range_dates = reactive([]);
const week_numbers_by_month = reactive([]);
const week_dates_by_month = reactive([]);
const selected_department_id = ref(null);
const selected_department_id_admin = ref(null);
const selected_month = ref(null);
const selected_user_id = ref('');
const months = reactive([]);
const selected_month_index = ref(null);
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
const admin_toggled = ref(false);
const is_editing = ref(false);
const edit_state = reactive({
    table: null,
    row: null,
})
const showAddScheduleButton = ref(false);

const modal_schedules = reactive({
    department_id: selected_department_id,
    user_availability: '',
    form: [],
});

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

const form = useForm({
    department_id: null,
    users_available: null,
    users_schedules: []
});

const days_of_week = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Série', 'Domingo'];

const initializeForm = () => {
    form.users_schedules = Array.from({ length: form.users_available }, (_, index) => ({
        user_group: index,
        schedules: days_of_week.map((_, index) => ({
            day_of_week: index + 1,
            start_time: '',
            end_time: '',
            user_availability_id: '',
            is_free_day: '',
        })),
    }));
};

const addScheduleTemplate = async () => {
    await axios.post('/schedule/savescheduletemplate', { department_id: form['department_id'],
                                                        users_available: form['users_available'],
                                                        users_schedules: form['users_schedules']
    }).then((response) => {

    }).catch((error) => {
       if(error.response && error.response.status === 400){
        toast.error(error.response.data.message, toast_options);
       };
    });
};

const getWeeklySchedule = async (week_numb_param, dep_id_param, year) => {
    if(week_numb_param !== undefined){
        console.log("ENTRAAAAAAA")
        console.log(selected_week)
        await axios.post('/schedule/weekly/' + week_numb_param + '/' + dep_id_param + '/' + year).then((response) => {
            weekly_schedule.length = 0;
            weekly_schedule.push(response.data.weekly_schedule);
            console.log(weekly_schedule);
            if (response.data.status){
                toast.success(`${response.data.message}`, toast_options);
            }
        });
    }
};

/**
 * @function getWeeklySchedulesForMonth
 * @description Generates weekly schedules for the given month. If the weekly schedule already exists, it will not be overwritten.
 * @param {number} department_id - The id of the department to generate the schedules for.
 * @param {number} year - The year of the month to generate the schedules for.
 * @param {number} month - The month of the year to generate the schedules for.
 * @returns {Promise<void>}
 */
const getWeeklySchedulesForMonth = async (department_id, year, month) => {
    loading.value = true;
    weekly_schedules_for_month.length = 0;
    let weeks_generated = reactive([]);
    let weeks_available_data = reactive([]);
    try {
        let url = '/api/weekly_schedules_for_month/' + department_id + '/' + year + '/' + month;
        let response = await axios.get(url);
        if (response.data.length > 0) {
            console.log(response.data);
            response.data.forEach((data) =>{
                loading.value = false;
                if (data.original) {
                    weekly_schedules_for_month.push(data.original.weekly_schedule);
                    weeks_generated.push(data.original.weekly_schedule.week_number);
                } else {
                    weekly_schedules_for_month.push(data.weekly_schedule);
                    weeks_available_data.push(data.weekly_schedule.week_number);
                }

            })
            if (weeks_generated.length > 0) {
                toast.success(`Se han generado horarios para las semanas: ${weeks_generated}.`, toast_options);
            }
            if (weeks_available_data.length > 0) {
                toast.warning(`Horarios para las semanas: ${weeks_available_data} ya existen.`, toast_options);
            }
        }
        saveLastBurnedImage();
        //console.log(weekly_schedules_for_month);
    } catch (error) {
        toast.error(`Ha habido un error.`, toast_options);
        console.log(error);
    }
    

}

const checkForScheduleTemplates = async (department_id, users_available) => {
    existing_template_data.length = 0;
    Object.keys(existing_template_error).forEach((key) => delete existing_template_error[key]);
    console.log(department_id);
    console.log(users_available);
    await axios.get('/api/schedules_template_check/' + department_id + '/' + users_available).then((response) => {
        console.log(response.data);
        if(response.data.status === "Error"){
            Object.assign(existing_template_error, response.data);
            showAddScheduleButton.value = true;
        }
        else {
        existing_template_data.push(...response.data);
        console.log(existing_template_data);
        }
    });
}

const removeScheduleTemplate = async (department_id, users_available) => {
    await axios.delete('/schedules_template_delete/' + department_id + '/' + users_available).then((response) => {
        console.log(response.data);
    })
}

const loadLastBurnedImage = () => {
    console.log('Loading last burned image:', last_weekly_schedule_image);
    weekly_schedules_for_month.splice(0, weekly_schedules_for_month.length, ...last_weekly_schedule_image); 
    console.log('Weekly schedules after load:', weekly_schedules_for_month);
    saveLastBurnedImage();
};

const saveLastBurnedImage = () => {
    last_weekly_schedule_image.splice(0, last_weekly_schedule_image.length, ...JSON.parse(JSON.stringify(toRaw(weekly_schedules_for_month))));
    console.log('Saved last burned image:', last_weekly_schedule_image);
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


const formatDate = (date) => { // Format the dates (e.g., '24-11-22')
    const year = date.getFullYear().toString().slice(-2); // Get last 2 digits of the year
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Add leading zero if needed
    const day = String(date.getDate()).padStart(2, '0'); // Add leading zero if needed
    return `${day}-${month}-${year}`;
};

function formatDateToDDMMYYYY(dateString) {
    if (!dateString) return null;

    const [year, month, day] = dateString.split("-");
    return `${day}-${month}-${year}`;
}

const fetchWeekDates = async (year, week_number) => { //Dates for the table thead
    loading.value = true;
    await axios.get('/api/week_dates/' + year + '/' + week_number)
        .then((response) => {
            week_date_range_dates.length = 0; // Clear the existing data (optional)
            week_date_range_dates.push(...response.data); // Add new data to the reactive array
            loading.value = false;
            console.log("fetchWeekDates -> " + response.data);
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
    console.log(week_dates_by_month);
};

const checkTodayMatch = (date) => {
    let today = moment().format('DD-MM-YYYY');
    return today === date;
}

const fetchWeekNumbersByMonth = async (year, month) => {
    await axios.get('/api/week_numbers_by_month/' + year + '/' + month)
        .then((response) => {
            week_numbers_by_month.length = 0; // Clear the existing data (optional)
            week_numbers_by_month.push(...response.data); // Add new data to the reactive array
            console.log("entra getWeekNumbersForMonth");
            console.log(week_numbers_by_month);
        })
}
    
const fetchDepartmentNameById = async (department_id) => {
    console.log(department_id);
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
            value: `${currentYear}-${String(i + 1).padStart(2, '0')}`,
            month: (i + 1),
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
        selected_month_index.value = selectedMonth.month;
        console.log("Selected month label:", selected_month_name.value);
    } else {
        selected_month_name.value = "null"; // Set to "null" if not found
        console.log("No month found for value:", selected_month.value);
    }
};

// Function to get the year
function getSelectedYear() {
    console.log("SELECTED YEAR: " + selected_month.value.split('-')[0]);
    return selected_month.value.split('-')[0]; // Splits by "-" and returns the first part (the year)
}

// Function to get the month
function getSelectedMonth() {
    let month = parseInt(selected_month.value.split('-')[1], 10);
    console.log("MONTH: " + month);
    return month; // Splits by "-" and returns the second part (the month)
}

const capitalize = (value) => {
    if (!value) return '';
    return value.charAt(0).toUpperCase() + value.slice(1);
};

function formatTimeString(timeString) { // Split the time string by `:` and take the first two components (hours and minutes)
    let [hours, minutes] = timeString.split(':');
    return `${hours}:${minutes}`;
}

const startEditing = (row_index, table_index) =>  {
    console.log("StartEditing --> Table index: " + table_index);
    is_editing.value = true;
    edit_state.row = row_index;
    edit_state.table = table_index;
}

const closeEditing = (row_index, table_index) => {
    is_editing.value = false;
    edit_state.row = null;
    edit_state.table = null;
}

const saveChanges = async (week_number, department_id, schedule_data) => {
    loading.value = true;
    let data;
    if (schedule_data && schedule_data.weekly_schedule && schedule_data.weekly_schedule.schedule_data) {
        data = schedule_data.weekly_schedule.schedule_data; // Access schedule_data if available
        console.log("saveChanges (saveWeeklySchedule) --> Access schedule_data if available" + data);
        console.log(data);
    } else {
        data = schedule_data; // Fall back to the original schedule_data
        console.log("saveChanges (saveWeeklySchedule) --> Fall back to the original schedule_data" + data);
        console.log(data);
    }
    let toRaw_data = toRaw(data);
    await axios.post('/schedule/saveweeklyschedule', {
        week_number: week_number, 
        department_id: department_id, 
        schedule_data: toRaw_data,
        rotation: data.rotation ? data.rotation : 0
    }, 
        {headers: {
            'Content-Type': 'application/json',
        }
    },)
        .then((response) => {
            weekly_schedule.length = 0;
            weekly_schedule.push(response.data.weekly_schedule);
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

const consolidateSchedules = () => {
    consolidated_schedules.length = 0;
    weekly_schedule.schedule_data.users.forEach((user, index) => {
        if (index > weekly_schedule.schedule_data.schedules.length) {
            let schedule = weekly_schedule.schedule_data.schedules[index];

            consolidated_schedules.push({
                date: user.date,
                day_of_week: schedule.day_of_week,
                is_weekend_day: schedule.is_weekend_day,
                start_time: schedule.start_time,
                end_time: schedule.end_time,
                id: user.id,
                name: user.name,
                is_non_working_day: user.is_non_working_day,
                is_holiday: user.is_holiday,
                holiday_state: user.holiday_state,
                is_not_available: user.is_not_available,
                is_night_shift: user.is_night_shift,
                is_fixed: user.is_fixed,
            })
        }
    })
}

const moveUser = (object, object_index, index, direction) => {
    const users = object[object_index].schedule_data.users;

      if (direction === "up" && index >= 1) {
        // Swap the current user with the one above
        [users[index], users[index - 1]] = [users[index - 1], users[index]];
        edit_state.row = (index - 1);
      } else if (direction === "down" && index < users.length - 1) {
        // Swap the current user with the one below
        [users[index], users[index + 1]] = [users[index + 1], users[index]];
        edit_state.row = (index + 1);
      }
}

const getFirstElement = (arr) => { return arr[0] };

const getLastElement = (arr) => { return arr[arr.length - 1] };

const change_selected_week_to_all = () => {
    selected_week.value = 'all';
}

const checkShowAddScheduleButton = () => {
    if (existing_template_error.status === "Error") {
        showAddScheduleButton.value = true;
    }
}

onBeforeMount(async () => {
    getWeekDateRange(today);
    admin_toggled.value = false;
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

        <div class="pt-3" :class="[admin_toggled ? 'pt-3' : '']">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-900 overflow-hidden shadow-sm sm:rounded-lg" :class="[(admin_toggled) ? 'admin_toggled' : '',]">
                    <div class="p-2 flex justify-end items-center space-x-5 text-gray-900 dark:text-gray-100">
                        <div class="flex items-center">
                        <!-- Toggle Switch -->
                        <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" :checked="admin_toggled" @change="admin_toggled = !admin_toggled">
                                <div
                                class="w-12 h-6 bg-gray-300 rounded-full border-2 ring-white peer-checked:bg-gray-500 
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
                    <div v-if="admin_toggled" class="p-2 pb-8 grid justify-center items-center space-x-5 text-gray-900 dark:text-gray-100">
                        <div class="sm:flex sm:justify-between items-center grid pr-9">
                            <!-- SCHEDULES FORM -->
                            <div class="sm:flex sm:justify-center sm:ml-5 grid items-center space-x-5 items_spacing_y w-[350px]"><!-- Dep -->
                                <h4 class="flex ml-5">Departamento:</h4>
                                <select id="departments" v-model="selected_department_id_admin"
                                    @change="fetchDepartmentNameById(selected_department_id_admin);"
                                    class="sm:max-w-[145px] max-w-[350px] min-w-24 flex-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                    <option selected disabled value="">Selecciona un departamento</option>
                                    <option v-for="department in departments" :key="department.id"
                                        :value="department.id">
                                        {{ capitalize(department.name) }}
                                    </option>
                                </select>
                            </div>
                            <div class="sm:flex sm:justify-center grid items-center space-x-5 items_spacing_y "><!-- Dep -->
                                <h4 class="flex ml-5">Número de empleados:</h4>
                                <input type="number" v-model="users_available" :disabled="!selected_department_id_admin" @change="form.reset(), showAddScheduleButton = false" class="sm:max-w-[145px] max-w-[350px] min-w-14 flex-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" min="1" placeholder="mín 1" required />
                                <button @click="checkForScheduleTemplates(selected_department_id_admin, users_available)" class="sm:max-w-[145px] max-w-[350px] min-w-14 flex-1 text-white text-lg rounded-lg bg-green-500/80 hover:bg-green-500/70 block w-full p-1.5 my-2" :disabled="!selected_department_id_admin || !users_available"> Comprobar</button>
                            </div>
                        </div>
                        <div v-if="existing_template_data.length > 0" class="overflow-auto p-4 justify-center items-center">
                            <table class="w-full text-sm text-left rtl:text-right border dark:border-gray-600 text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Turno</th>
                                        <th scope="col" class="px-6 py-3"
                                        v-for="(day_of_week, index) in formatted_data_indexes" :key="index"> {{ day_of_week }}
                                    </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(user_data, user_index) in existing_template_data" :key="user_index" class=" bg-white border-b dark:bg-gray-800 dark:border-gray-500">
                                        <td class="px-3 py-4 font-medium text-gray-900 whitespace-nowrap border-r border-l bg-gray-600 dark:border-gray-500 dark:text-white">
                                            {{ 'Usuario' }}
                                        </td>
                                        <td v-for="(day, day_index) in user_data" :key="day_index" class="px-3 py-4 font-medium text-gray-900 whitespace-nowrap border-r border-l dark:border-gray-500 dark:text-white"
                                        :class="[(day.start_time === '00:00' && day.end_time === '00:00' && (day.is_free_day)) ? 'free_day' : day.is_free_day ? 'free_day' : '']">
                                            {{ day.start_time === '00:00' && day.end_time === '00:00' && day.is_free_day ? 'LIBRE' : 
                                            day.is_free_day ? 'LIBRE' :
                                            formatTimeString(day.start_time) + ' - ' + formatTimeString(day.end_time) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-if="existing_template_data.length > 0" class="sm:flex sm:justify-between items-center grid pr-9 sm:pr-4">
                            <button @click="removeScheduleTemplate(selected_department_id_admin, users_available), checkForScheduleTemplates(selected_department_id_admin, users_available);" class="sm:ml-5 text-white text-lg rounded-lg bg-red-500 hover:bg-red-500/80 p-1 my-2 sm:w-full"> ELIMINAR PLANTILLA </button>
                        </div>
                        <div v-if="existing_template_error.status && showAddScheduleButton" class="sm:flex sm:justify-center items-center flex-col space-y-2 pr-9">

                                <p class="text-red-400"> {{ existing_template_error.message }}</p>
                                <button @click="form.department_id = selected_department_id_admin, form.users_available = users_available, initializeForm(), showAddScheduleButton = false;" class="flex items-center w-full justify-center  sm:ml-0 sm:mt-0 bg-green-600/70 hover:bg-green-500/60 text-white font-bold py-2 px-4 mt-4 rounded-lg"> AÑADIR PLANTILLA </button>

                        </div>
                        <div v-if="form.users_schedules.length > 0" class="sm:flex sm:justify-center items-center flex-col space-y-2 pr-9">

                                <button v-if="form.users_schedules.length > 0" @click="addScheduleTemplate()" class="flex items-center w-full justify-center  sm:ml-0 sm:mt-0 bg-green-600/70 hover:bg-green-500/60 text-white font-bold py-2 px-4 mt-4 rounded-lg">
                                    GUARDAR
                                </button>
                                <button v-if="form.users_schedules.length > 0" @click="form.reset()" class="flex items-center w-full justify-center sm:ml-0 sm:mt-0 bg-red-600/70 hover:bg-red-500/60 text-white font-bold py-2 px-4 mt-4 rounded-lg">
                                    CANCELAR
                                </button>

                        </div>
                        <div v-if="form.users_schedules.length > 0" class="overflow-auto px-12 py-5">
                            <table>
                                <thead>
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Turno</th>
                                        <th scope="col" class="px-6 py-3"
                                        v-for="(day_of_week, index) in formatted_data_indexes" :key="index"> {{ day_of_week }}
                                    </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(user, userIndex) in form.users_schedules" :key="userIndex">
                                        <td> {{ 'Usuario: ' + (userIndex + 1) }} </td>
                                        <td v-for="(day, dayIndex) in user.schedules" :key="dayIndex" :class="[day.is_free_day ? 'bg-green-500/30 rounded-lg' : '']">
                                            <div class="flex flex-wrap justify-center py-1">
                                                <input type="time" v-model="day.start_time" step="1" class="bg-gray-50 border leading-none border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" min="00:00" max="00:00" value="00:00" required :class="[day.start_time === '' ? 'dark:text-red-500/40' : '']"/>
                                                <input type="time" v-model="day.end_time" step="1" class="bg-gray-50 border leading-none border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" min="00:00" max="00:00" value="00:00" required :class="[day.end_time === '' ? 'dark:text-red-500/40' : '']"/>
                                                <label>Día libre: <input type="checkbox" v-model="day.is_free_day" class="mr-1 w-3.5 h-3.5 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-green-500 dark:border-gray-600"/></label>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- <template> -->
        <div class="pb-6 pt-3" :class="[admin_toggled ? 'pt-6 pb-6' : '']">
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
                                        {{ capitalize(department.name) }}
                                    </option>
                                </select>
                            </form>
                        </div>
                        <div class="flex justify-center items-center space-x-5 items_spacing_y">
                            <h4 class="flex items-">Mes:</h4>
                            <form class="flex max-w-sm mx-auto items-center">
                                <label for="month"></label>
                                <select
                                class="flex-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    :disabled="!selected_department_id" v-model="selected_month"
                                    @change="selected_month_label(); getWeeksForMonth(getSelectedYear(selected_month), getSelectedMonth(selected_month)); getWeeklySchedulesForMonth(selected_department_id, getSelectedYear(selected_month), getSelectedMonth(selected_month)); change_selected_week_to_all();">
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
                                    @change="getWeeklySchedule(selected_week.weekNumber, selected_department_id, getSelectedYear(selected_month)); fetchWeekDates(getSelectedYear(selected_month), selected_week.weekNumber);">
                                    <option value="all" :disabled="!selected_week" selected>Todas las semanas del mes</option>
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
<!--                                 <p class="flex today_th text-xs">{{ moment().format('DD-MM-YYYY') }}</p> -->
                            </div>
                            <div>
                                <p class="text-white">Hoy.</p>
                            </div>
                        </div>

                        <div class="flex justify-around mx-2 items-center items_spacing_y"> <!-- Legend div -->
                            <div class="w-16 h-6 mr-1.5 bg-[#f5ac10] rounded-md"><!-- Confirmed holidays -->
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

                            <!-- START MULTI TABLES -->
                <div v-if="(selected_month && !selected_week) || (selected_month && selected_week === 'all')" class="pb-14 grid items-center justify-center text-gray-900 dark:text-gray-100">
                    <div v-for="(weekly_schedule, weekly_schedule_index) in weekly_schedules_for_month" :key="weekly_schedule_index" class="relative overflow-x-auto my-5">
                        <h4 v-if="weekly_schedule.schedule_data.schedules.length > 0" class="header m-2"> Semana: <span class="bold text-violet-400"> {{ week_dates_by_month[weekly_schedule_index].weekNumber }} </span> | <span class="bold text-green-200"> {{ week_dates_by_month[weekly_schedule_index].startDate }} </span> a <span class="bold text-green-200"> {{ week_dates_by_month[weekly_schedule_index].endDate }} </span> | 
                            <button @click="loadLastBurnedImage(), closeEditing()" class="text-yellow-300"><i class="text-md text-yellow-300 fa-solid fa-rotate"></i> Recargar tabla</button> |
                            <button @click="saveChanges(weekly_schedule.week_number, selected_department_id, weekly_schedule), closeEditing();" class="text-green-400"><i class="text- text-green-400 fa-solid fa-floppy-disk"></i> Grabar imagen</button>
                        </h4>
                        <table v-if="weekly_schedule.schedule_data.schedules.length > 0" class="w-full text-sm text-left rtl:text-right border dark:border-gray-600 text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Turno</th>
                                    <th scope="col" class="px-6 py-3"
                                        v-for="(day_of_week, index) in formatted_data_indexes" :key="index"> {{ day_of_week }}
                                    </th>
                                    <th class="">Editar</th>
                                    <!-- <th scope="col" class="px-6 py-3"></th> -->
                                </tr>
                                <tr><!-- v-if="(weekly_schedule.schedule_data.schedules.length > 0)" -->
                                    <th scope="col" class="px-6 py-1"></th>
                                    <th scope="col" class="px-6 py-1" v-for="(date, date_index) in weekly_schedule.schedule_data.schedules[0]" :key="date_index">
                                        <span class="text-green-100" :class="[checkTodayMatch(formatDateToDDMMYYYY(date.date)) ? 'today_th' : '', ]">{{ formatDateToDDMMYYYY(date.date) }}</span>
                                    </th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Loop through each user data array -->
                                <tr v-for="(user, user_index) in weekly_schedule.schedule_data.users" :key="user_index" class=" bg-white border-b dark:bg-gray-800 dark:border-gray-500" :class="[(user[user_index].id == 8 || user[user_index].id == 9) ? 'reduced_contract' : '']">
                                    <td scope="row"
                                    class="px-3 py-4 font-medium text-gray-900 whitespace-nowrap border-r border-l bg-gray-600 dark:border-gray-500 dark:text-white"
                                    :class="[(user[user_index].id == 8 || user[user_index].id == 9) ? 'reduced_contract' : '',]"
                                    >
                                        <template v-if="is_editing && edit_state.row === user_index && edit_state.table === weekly_schedule_index">
                                            <i class="fa-solid fa-user"></i> {{ user[user_index].name }}
                                            <button
                                            @click="moveUser(weekly_schedules_for_month, weekly_schedule_index , user_index, 'up')" 
                                            :disabled="user_index === 0" 
                                            class="btn btn-sm btn-primary border mx-1 border-green-200 transition ease-in-out duration-300 hover:text-green-400"
                                            >
                                                    <i class="transition ease-in-out duration-300 text-xm text-green-500 hover:text-green-600 m-2 fa-solid fa-arrow-up"></i>
                                                </button>
                                                <button
                                                @click="moveUser(weekly_schedules_for_month, weekly_schedule_index , user_index, 'down')" 
                                                :disabled="user_index === weekly_schedule.schedule_data.users.length - 1" 
                                                class="btn btn-sm btn-secondary border mx-1 border-red-200 transition ease-in-out duration-300 hover:text-red-400"
                                                >
                                                <i class="transition ease-in-out duration-300 text-xm text-red-500 hover:text-red-400 m-2 fa-solid fa-arrow-down"></i>
                                            </button>
                                        </template>
                                        <template v-else>
                                                <i class="fa-solid fa-user"></i> {{ user[user_index].name }}
                                            </template>
                                    </td>
                                    <td v-for="(schedule, schedule_index) in weekly_schedule.schedule_data.schedules[user_index]" :key="schedule_index" scope="row"
                                        class="px-3 py-4 font-medium text-gray-900 whitespace-nowrap border-r border-l dark:border-gray-500 dark:text-white"
                                        :class="[(user[schedule_index].is_night_shift) ? 'is_night_shift' : '',
                                                (schedule.start_time === '00:00' && schedule.end_time === '00:00' && (user[user_index].id == 8 || user[user_index].id == 9)) ? 'free_day reduced_contract' : 
                                                (schedule.start_time === '00:00' && schedule.end_time === '00:00' && (schedule.is_weekend_day)) ? 'free_day' : schedule.is_weekend_day ? 'free_day' : '', 
                                                (user[schedule_index].is_holiday && user[schedule_index].holiday_state === 2) ? 'is_confirmed_holiday' : '', 
                                                (user[schedule_index].is_holiday && user[schedule_index].holiday_state === 1) ? 'is_not_confirmed_holiday' : '', 
                                                (user[schedule_index].is_not_available) ? 'is_not_available' : '', ]">
                                        <template v-if="is_editing && edit_state.row === user_index && edit_state.table === weekly_schedule_index">
                                            <select v-model="schedule.start_time" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-blue-500 focus:border-blue-500 block w-full p-1.5 my-1 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                                <option selected disabled default :value="schedule.start_time">{{ schedule.start_time }}</option>
                                                <option v-for="(value, key) in schedule_hour_selector" :key="key" :value="key">{{ value }}</option>
                                            </select>
                                            <select v-model="schedule.end_time" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-blue-500 focus:border-blue-500 block w-full p-1.5 my-1 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                                <option selected :value="schedule.end_time">{{ schedule.end_time }}</option>
                                                <option v-for="(value, key) in schedule_hour_selector" :key="key" :value="key">{{ value }}</option>
                                            </select>
                                            <div class="flex justify-center">
                                                <input type="checkbox" v-model="schedule.is_weekend_day" :checked="schedule.is_weekend_day === true" class="mr-1 w-3.5 h-3.5 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-green-500 dark:border-gray-600"/>
                                                <input type="checkbox" v-model="user[schedule_index].is_holiday" @change="updateHolidayCheckbox(user[schedule_index], $event.target.checked)" :checked="user[schedule_index].is_holiday === true" class="mr-1 w-3.5 h-3.5 text-yellow-500 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-yellow-500 dark:border-gray-600"/>
                                                <input type="checkbox" v-model="user[schedule_index].is_night_shift" :checked="user[schedule_index].is_night_shift === true" class="mr-1 w-3.5 h-3.5 text-red-400 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-red-400 dark:border-gray-600"/>
                                                <input type="checkbox" v-model="user[schedule_index].is_not_available" :checked="user[schedule_index].is_not_available === true" class="mr-1 w-3.5 h-3.5 text-yellow-300 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-yellow-200 dark:border-gray-600"/>
                                            </div>
                                        </template>
                                        <template v-else>
                                            <!-- {{ day_index }}: {{ day }} -->
                                            {{ user[schedule_index].is_not_available ? 'BAJA' :
                                            schedule.start_time === '00:00' && schedule.end_time === '00:00' && schedule.is_weekend_day ? 'LIBRE' : 
                                            schedule.is_weekend_day ? 'LIBRE' :
                                            user[schedule_index].is_holiday && user[schedule_index].holiday_state ===2 ? 'VACACIONES' :
                                            user[schedule_index].is_holiday && user[schedule_index].holiday_state ===1 ? schedule.start_time + ' - ' + schedule.end_time + ' ?' :
                                            schedule.start_time + ' - ' + schedule.end_time }}
                                        </template>
                                    </td>
                                    <td class="w-14 pl-2 flex-end">
                                        <template v-if="is_editing && edit_state.row === user_index && edit_state.table === weekly_schedule_index">
                                            <button @click="closeEditing(user_index, weekly_schedule_index)"><i class="p-1 m-1 text-2xl text-red-400 fa-solid fa-xmark"></i></button>
                                        </template>
                                        <template v-else>
                                            <button @click="startEditing(user_index, weekly_schedule_index)" :disabled="is_editing" :class="[(is_editing) ? 'disabled' : '']"><i class="p-1 m-1 text-2xl text-yellow-200 fa-regular fa-pen-to-square" :class="[(is_editing) ? 'disabled' : '']"></i></button>
                                        </template>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-else class="flex justify-center">
                            <p v-if="weekly_schedule_index === 2" class="text-green-200">No hay datos sobre el departamento para este mes y semana.</p>
                        </div>
                    </div>
                </div>
                
                
                <!-- START SINGLE TABLE -->
                <template v-if="weekly_schedule.length > 0 && selected_week !== 'all'">
                    <div v-if="selected_week" class="pb-14 flex items-center justify-center text-gray-900 dark:text-gray-100">
                        <div v-if="weekly_schedule.length > 0" class="relative overflow-x-auto mt-5">
                                <h4 v-if="weekly_schedule.length > 0 && selected_week.weekNumber" class="header m-2"> Semana: <span class="bold text-violet-400"> {{ selected_week?.weekNumber }} </span> | <span class="bold text-green-200"> {{ selected_week?.startDate }} </span> a <span class="bold text-green-200"> {{ selected_week?.endDate }} </span> | 
                                    <button @click="getWeeklySchedule(selected_week.weekNumber, selected_department_id), closeEditing()" class="text-yellow-300"><i class="text-md text-yellow-300 fa-solid fa-rotate"></i> Recargar tabla</button> |
                                    <button @click="saveChanges(selected_week.weekNumber, selected_department_id, weekly_schedule[0]), closeEditing()" class="text-green-400"><i class="text- text-green-400 fa-solid fa-floppy-disk"></i> Grabar imagen</button>
                                </h4>
                                <table v-if="weekly_schedule.length > 0" class="w-full text-sm text-left rtl:text-right border dark:border-gray-600 text-gray-500 dark:text-gray-400">
                                    <thead
                                    class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                        <tr>
                                            <th scope="col" class="px-6 py-3">Turno</th>
                                            <th scope="col" class="px-6 py-3"
                                                v-for="(day_of_week, index) in formatted_data_indexes" :key="index"> {{ day_of_week }}
                                            </th>
                                            <th class="">Editar</th>
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
                                        <template v-if="weekly_schedule[0] && selected_week !== 'all'">
                                            <tr v-for="(user, user_index) in weekly_schedule[0].schedule_data.users" :key="user_index" class=" bg-white border-b dark:bg-gray-800 dark:border-gray-500" :class="[(user[user_index].id == 8 || user[user_index].id == 9) ? 'reduced_contract' : '']">
                                                <td scope="row"
                                                    class="px-3 py-4 font-medium text-gray-900 whitespace-nowrap border-r border-l bg-gray-600 dark:border-gray-500 dark:text-white"
                                                    :class="[(user[user_index].id == 8 || user[user_index].id == 9) ? 'reduced_contract' : '',]"
                                                    >
                                                    <template v-if="is_editing && edit_state.row === user_index">
                                                        <i class="fa-solid fa-user"></i> {{ user[user_index].name }}
                                                            <button
                                                                @click="moveUser(weekly_schedule, 0, user_index, 'up')" 
                                                                :disabled="user_index === 0" 
                                                                class="btn btn-sm btn-primary border mx-1 border-green-200 transition ease-in-out duration-300 hover:text-green-400"
                                                                >
                                                                <i class="transition ease-in-out duration-300 text-xm text-green-500 hover:text-green-600 m-2 fa-solid fa-arrow-up"></i>
                                                            </button>
                                                            <button
                                                            @click="moveUser(weekly_schedule, 0, user_index, 'down')" 
                                                            :disabled="user_index === weekly_schedule[0].schedule_data.users.length - 1" 
                                                            class="btn btn-sm btn-secondary border mx-1 border-red-200 transition ease-in-out duration-300 hover:text-red-400"
                                                            >
                                                                <i class="transition ease-in-out duration-300 text-xm text-red-500 hover:text-red-400 m-2 fa-solid fa-arrow-down"></i>
                                                            </button>
                                                    </template>
                                                    <template v-else>
                                                        <i class="fa-solid fa-user"></i> {{ user[user_index].name }}
                                                    </template>
                                                </td>
                                                <td v-for="(schedule, schedule_index) in weekly_schedule[0].schedule_data.schedules[user_index]" :key="schedule_index" scope="row"
                                                    class="px-3 py-4 font-medium text-gray-900 whitespace-nowrap border-r border-l dark:border-gray-500 dark:text-white"
                                                    :class="[(user[schedule_index].is_night_shift) ? 'is_night_shift' : '',
                                                            (schedule.start_time === '00:00' && schedule.end_time === '00:00' && (user[user_index].id == 8 || user[user_index].id == 9)) ? 'free_day reduced_contract' : 
                                                            (schedule.start_time === '00:00' && schedule.end_time === '00:00' && (schedule.is_weekend_day)) ? 'free_day' : schedule.is_weekend_day ? 'free_day' : '', 
                                                            (user[schedule_index].is_holiday && user[schedule_index].holiday_state ===2) ? 'is_confirmed_holiday' : '', 
                                                            (user[schedule_index].is_not_available) ? 'is_not_available' : '', ]">
                                                    <template v-if="is_editing && edit_state.row === user_index">
                                                        <select v-model="schedule.start_time" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-blue-500 focus:border-blue-500 block w-full p-1.5 my-1 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                                            <option selected disabled default :value="schedule.start_time">{{ schedule.start_time }}</option>
                                                            <option v-for="(value, key) in schedule_hour_selector" :key="key" :value="key">{{ value }}</option>
                                                        </select>
                                                        <select v-model="schedule.end_time" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-blue-500 focus:border-blue-500 block w-full p-1.5 my-1 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                                            <option selected :value="schedule.end_time">{{ schedule.end_time }}</option>
                                                            <option v-for="(value, key) in schedule_hour_selector" :key="key" :value="key">{{ value }}</option>
                                                        </select>
                                                        <div class="flex justify-center">
                                                            <input type="checkbox" v-model="schedule.is_weekend_day" :checked="schedule.is_weekend_day === true" class="mr-1 w-3.5 h-3.5 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-green-500 dark:border-gray-600"/>
                                                            <input type="checkbox" v-model="user[schedule_index].is_holiday" @change="updateHolidayCheckbox(user[schedule_index], $event.target.checked)" :checked="user[schedule_index].is_holiday === true" class="mr-1 w-3.5 h-3.5 text-yellow-500 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-yellow-500 dark:border-gray-600"/>
                                                            <input type="checkbox" v-model="user[schedule_index].is_night_shift" :checked="user[schedule_index].is_night_shift === true" class="mr-1 w-3.5 h-3.5 text-red-400 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-red-400 dark:border-gray-600"/>
                                                            <input type="checkbox" v-model="user[schedule_index].is_not_available" :checked="user[schedule_index].is_not_available === true" class="mr-1 w-3.5 h-3.5 text-yellow-300 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-yellow-200 dark:border-gray-600"/>
                                                        </div>
                                                    </template>
                                                    <template v-else>
                                                        <!-- {{ day_index }}: {{ day }} -->
                                                        {{ user[schedule_index].is_not_available ? 'BAJA' :
                                                        schedule.start_time === '00:00' && schedule.end_time === '00:00' && schedule.is_weekend_day ? 'LIBRE' : 
                                                        schedule.is_weekend_day ? 'LIBRE' :
                                                        user[schedule_index].is_holiday && user[schedule_index].holiday_state ===2 ? 'VACACIONES' :
                                                        user[schedule_index].is_holiday && user[schedule_index].holiday_state ===1 ? schedule.start_time + ' - ' + schedule.end_time + ' ?' :
                                                        schedule.start_time + ' - ' + schedule.end_time }}
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
                                        </template>
                                    </tbody>
                                </table>
                                <p v-else>No hay datos sobre el departamento para este mes y semana.</p>
                            </div>
                            <div v-else-if="!loading" class="flex justify-center pt-14 pb-8">
                                <p class="text-green-200">No hay datos sobre el departamento para este mes y semana.</p>
                            </div>
                        </div>
                    </template>
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
            </div>
        </div>
    </div>
<!-- </template> -->
</AuthenticatedLayout>
</template>

<style scoped>
.today{
    border: 2px solid rgb(175, 175, 175) !important;
    cursor: context-menu;
}
.today_th{
    font-size: 12px;
    font-weight: bold;
    justify-items: center;
    align-content: center;
    line-height: 20px;
    margin: 0 0 0 2px;
    color: #4ade80 !important;
    cursor: context-menu;
}
.is_not_confirmed_holiday{
    font-size: 12px;
    font-weight: bold;
    line-height: 20px;
    border: 2px dashed rgb(58, 58, 58) !important;
    cursor: context-menu;
    border-radius: 8px;
    background:linear-gradient(#f5ac10, #f5ac10 40%, #f5ac10 3%, #f0ab16) !important;
    color: rgb(83, 81, 81);
    box-shadow: inset 0px 0px 15px rgba(251, 255, 0, 0.432) !important;
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
    background:linear-gradient(#4ade80, #4ade80 40%, #4ade80 3%, #3bbe6b) !important;
    color: rgb(100, 107, 107);
}
.is_confirmed_holiday{
    border-radius: 8px;
    background:linear-gradient(#f5ac10, #f5ac10 40%, #f5ac10 3%, #f0ab16) !important;
    color: rgb(92, 88, 88);
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
    /* border: 2px #f87171; */
    box-shadow: inset 0 0 0 5px #f85e5e;
    z-index: 15;
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