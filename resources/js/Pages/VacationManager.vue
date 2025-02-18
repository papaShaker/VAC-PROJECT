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

const calendar_data = reactive({});
const expand = ref(null);
const users = ref([]);
const holidays_to_be_confirmed_list = reactive([]);
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
const loaded_to_be_confirmed_data = ref(false);
const vac_states = reactive([]);
const ranges_checkbox_list = reactive([]);
const show_modal_holidays_to_be_confirmed = ref(false);
const show_modal_job_range = ref(false);
const selected_moda_job_range = ref(false);
const selected_modal_holidays_to_be_confirmed = ref(false);
const modal_holidays_to_be_confirmed = reactive({
    title: '',
    id: 0,
    loading: false,
    holidays: [],
});
const modal_job_range = reactive({
    title: '',
    current_ranges: [],
    paid_holidays: [],
    contract_types: [],
    paid_holidays_days: {
        days: 0,
        comment: '',
        date: null
    },
    new_start_date: null,
    new_end_date: null,
    new_contract_type: null,
    new_work_on_freedays: false,
    form_add_visible: false,
    form_add_paid_holidays: false
});
const selected_modal_job_range = ref(false);

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

const getHolidaysToBeConfirmed = async () => {
    while(holidays_to_be_confirmed_list.length > 0) {
        holidays_to_be_confirmed_list.pop();
    }
    loaded_to_be_confirmed_data.value = true;
    await axios.get("api/vacations_to_be_confirmed")
        .then((response => {
            holidays_to_be_confirmed_list.push(...response.data);
            console.log(holidays_to_be_confirmed_list);
        }
        ));
}

const manageHolidaysToBeConfirmed = (item) => {
    modal_holidays_to_be_confirmed.holidays = item.holidays_state.holidays_to_be_confirmed
    console.log(modal_holidays_to_be_confirmed.holidays);
    modal_holidays_to_be_confirmed.title = item.user_info.name;
    show_modal_holidays_to_be_confirmed.value = true;
}

const updateHolidays = (state) => {
    modal_holidays_to_be_confirmed.loading = true;
    let ids_to_be_updated = [];
    modal_holidays_to_be_confirmed.holidays.forEach(element => {
        if (element.selected) {
            ids_to_be_updated.push(element.id);
        }
        else {
            modal_holidays_to_be_confirmed.loading = false;
        }
    });
    if (ids_to_be_updated.length > 0) {
        axios.post('/manager/update/holidays', {ids_to_be_updated: ids_to_be_updated, update_to: state})
            .then((response) => {
                if(response.data.status == 'Success'){
                    toast.success(`${response.data.text}`, toast_options);
                    if (response.data.list != undefined) {
                        items = response.data.list;
                        modal_holidays_to_be_confirmed.holidays = [];
                        
                    }
                    
                } else {
                    toast.error(`${response.data.text}`, toast_options);
                    
                
                }
                modal_holidays_to_be_confirmed.loading = false;
            }).finally(() => {
                modal_holidays_to_be_confirmed.loading = false;
                closeModalInfoHolidays();
            });
    }
}

const closeModalInfoHolidays = () => {
    console.log("MODAL ON CLOSE");
    show_modal_holidays_to_be_confirmed.value = false;
    show_modal_job_range.value = false;
};

const handleCheckboxChangeHolidaysToBeConfirmed = (vac) => {
    if (vac.selected) {
        vac_states.push(vac);
        console.log(vac_states);
    } else if (!vac.selected) {
        vac_states.pop();
    }

    if(vac_states.length > 0) {
            selected_modal_holidays_to_be_confirmed.value = true;
    } else {
        selected_modal_holidays_to_be_confirmed.value = false;
    }
};


const manageRanges = (user) => {
    modal_job_range.current_ranges = [];
    axios.get('/manager/get_ranges/' + user.id).then((response) => {
        modal_job_range.current_ranges = response.data.ranges;
        modal_job_range.paid_holidays = response.data.paid_holidays;
        modal_job_range.contract_types = response.data.contract_types;
    });
    modal_job_range.title = user.name;
    modal_job_range.user_id = user.id;
    modal_job_range.form_add_visible = false;
    show_modal_job_range.value = true;
};

const handleCheckboxChangeJobRange = (contract) => {
    if (contract.selected) {
        ranges_checkbox_list.push(contract);
        console.log(ranges_checkbox_list);
    } else if (!contract.selected) {
        ranges_checkbox_list.pop();
    }

    if(ranges_checkbox_list.length > 0) {
            selected_modal_job_range.value = true;
    } else {
        selected_modal_job_range.value = false;
    }
};

onBeforeMount(async () => {
    await generateMonthOptions();
})


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
                        <h4 class="flex">Departamento:</h4>
                    <form class="flex w-3/12 max-w-sm mx-auto items-center" @submit.prevent="">
                        <label for="departments" class="text-xl font-medium text-gray-900 dark:text-white">
                        </label>
                        <select id="departments" v-model="selected_department_id"
                        @change="fetchDepartmentNameById(selected_department_id);"
                        class="flex-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option selected disabled value="">Selecciona un departamento</option>
                        <option value="all">
                            Todos los departamentos
                        </option>
                        <option v-for="department in departments" :key="department.id" :value="department.id">
                            {{ department.name }}
                        </option>
                    </select>
                    </form>
                    <!-- START TABLE -->
                    </div>
                    <div class="pb-12 flex items-center justify-center text-gray-900 dark:text-gray-100"
                        v-if="selected_month_check">
                        <div v-if="holidays_data.length > 0" class="relative overflow-x-auto mt-1">
                            <h4 class="header mt-2"> {{ selected_month_name.value }}</h4>
                            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                <thead
                                    class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-6 py-2">Fecha</th>
                                        <th scope="col" class="px-6 py-2">Usuario</th>
                                        <th scope="col" class="px-6 py-2">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700"
                                        v-for="holiday in holidays_data" :key="holiday">
                                        <td scope="row"
                                            class="px-6 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{
                                                holiday.date }}</td>
                                        <td scope="row"
                                            class="px-6 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{
                                                holiday.user_name }}</td>
                                        <td scope="row"
                                            class="px-6 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-gray-600 italic"
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

                                        <!-- START TO BE CONFIRMED VACATIONS -->
                    <div class="flex items-center justify-center mb-6 text-gray-900 dark:text-gray-100">
                        <button @click="getHolidaysToBeConfirmed"
                            class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Cargar lista de
                            vacaciones pendientes</button>
                    </div>

                    <div class="relative overflow-x-auto my-5"
                        v-if="loaded_to_be_confirmed_data">
                        <table class="text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead class="text-xs border border-gray-600 text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="border-r border-l border-gray-500">Usuario</th>
                                    <th class="border-r border-l border-gray-500">Contrato</th>
                                    <th class="border-r border-l border-gray-500">Zona</th>
                                    <th class="border-r border-l border-gray-500">Vacaciones</th>
                                    <th class="border-r border-l border-gray-500">Acumuladas</th>
                                    <th class="border-r border-l border-gray-500">Extras</th>
                                    <th class="border-r border-l border-gray-500">Confirmadas</th>
                                    <th class="border-r border-l border-gray-500">Disfrutadas</th>
                                    <th class="border-r border-l border-gray-500">Pagadas</th>
                                    <th class="border-r border-l border-gray-500">Pe. Disfrutar</th>
                                    <th class="border-r border-l border-gray-500">Pe. Confirmar</th>
                                    <th class="border-r border-l border-gray-500">Pe. Solicitar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="bg-gray-600" v-for="(item, index) in holidays_to_be_confirmed_list" :key="index">
                                    <td class="border-l border-b border-gray-500">
                                        <span @click="item.holidays_state.pending_holiday_hours_to_be_confirmed > 0 ? manageHolidaysToBeConfirmed(item) : false" class="tooltip-mvc">
                                            <a
                                                class="linkUsr text-gray-400">
                                                {{ item.user_info.name }}
                                            </a>
                                        </span>
                                    </td>
                                    <td class="border-l border-r border-b border-gray-500">
                                        <div class="to_be_confirmed flex justify-center">
                                            <a class="jobRangeIcon" @click="manageRanges(item.user_info)"> <!--  -->
                                                <i class="fas fa-file-contract fa-2x text-[#39a33c]"></i>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="border-l border-b border-gray-500">
                                        <div class="to_be_confirmed tooltip-mvc">
                                            <a class="jobRangeIcon"
                                               > <!-- @click="gestionarZonaFestius(item.user_info)" -->
                                                <span v-if="item.user_info.nonworkingdayzone">{{ item.user_info.nonworkingdayzone.zone
                                                    }}</span>
                                                <span class="red" v-else>No configurada</span>
                                            </a> <!-- v-if="comanderVacances" -->
                                        </div>
                                    </td>
                                    <td class="border-l border-gray-500">
                                        <div class="numeric_Container">
                                            <span data-bs-toggle="tooltip" data-placement="bottom"
                                                :title="item.holidays_state.hours_per_contract.toFixed(2) + ' horas.'"
                                                class="numeric">
                                                {{ (item.holidays_state.hours_per_contract /
                                                    item.holidays_state.current_working_hours).toFixed(0) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="numeric_Container">
                                            <span class="numeric">{{ item.holidays_state.accumulated_holidays_days }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="numeric_Container">
                                            <span data-bs-toggle="tooltip" data-placement="bottom"
                                                :title="item.holidays_state.hours_per_extra_days.toFixed(2) + ' horas.'"
                                                class="numeric">
                                                {{ item.holidays_state.extra_holidays_days }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="numeric_Container">
                                            <span class="numeric">
                                                {{ (item.holidays_state.confirmed_holiday_hours /
                                                    item.holidays_state.current_working_hours).toFixed(0) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="numeric_Container">
                                            <span data-bs-toggle="tooltip" data-placement="bottom"
                                                :title="item.holidays_state.enjoyed_holiday_hours.toFixed(2) + ' horas.'"
                                                class="numeric">
                                                {{ item.holidays_state.enjoyed_holidays_days }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="numeric_Container">
                                            <span class="numeric">{{ item.holidays_state.paid_holidays_days }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="numeric_Container">
                                            <span class="numeric">
                                                {{ (item.holidays_state.pending_holiday_hours_to_be_enjoyed /
                                                    item.holidays_state.current_working_hours).toFixed(0) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="numeric_Container">
                                            <span class="numeric to_be_confirmed tooltip-mvc"
                                                @click="item.holidays_state.pending_holiday_hours_to_be_confirmed > 0 ? manageHolidaysToBeConfirmed(item) : false"
                                                :class="[item.holidays_state.pending_holiday_hours_to_be_confirmed > 0 ? 'pendientes' : '']">
                                                {{ (item.holidays_state.pending_holiday_hours_to_be_confirmed /
                                                    item.holidays_state.current_working_hours).toFixed(0) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="numeric_Container">
                                            <span data-bs-toggle="tooltip" data-placement="bottom"
                                                :title="item.holidays_state.holiday_hours_to_be_requested.toFixed(2) + ' horas.'"
                                                class="numeric">
                                                {{ item.holidays_state.pending_holidays_total_to_be_requested }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- MODAL UPDATE VACATIONS START -->

                    <ModalInfoHolidays v-if="show_modal_holidays_to_be_confirmed" :isOpen="show_modal_holidays_to_be_confirmed" @modal-close="closeModalInfoHolidays" name="modal-info-holidays" role="document">
                        <template v-slot:modal-header>
                            <p class="text-xl"> <span class="font-bold text-gray-300">Vacaciones de</span> <span class="text-white">{{modal_holidays_to_be_confirmed.title}} </span> </p>
                        </template>
                        <template v-slot:modal-content>
                                <div class="flex justify-around items-center" v-for="(vac, key) in modal_holidays_to_be_confirmed.holidays" :key="key">
                                    <div><span class="font-bold text-gray-300">Fecha solicitada:</span> <span class="font-bold text-gray-100">{{ vac.date }}</span></div>
                                    <input type="checkbox" @change="handleCheckboxChangeHolidaysToBeConfirmed(vac)" v-model="vac.selected" :id="key" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"/>
                                </div>
                                <div v-if="modal_holidays_to_be_confirmed.loading" class="flex justify-center items-center min-h-24 pos-relative">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-transparent dark:bg-transparent">
                                        <div role="status">
                                            <svg aria-hidden="true" class="h-10 w-10 animate-spin fill-red-600 text-gray-200 dark:text-gray-400" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor" />
                                            <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill" />
                                            </svg>
                                            <span class="sr-only">Cancelando...</span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template v-slot:modal-footer>
                                <div v-if="selected_modal_holidays_to_be_confirmed" class="flex justify-between mt-5 mx-2">
                                    <button @click="updateHolidays(1)"
                                        class="bg-green-600 hover:bg-green-800 text-white font-bold py-1 px-2 m-1 rounded">Confirmar
                                        vacaciones</button>
                                    <button @click="updateHolidays(0)"
                                        class="bg-red-600 hover:bg-red-800 text-white font-bold py-1 px-2 m-1 rounded">Denegar
                                        vacaciones</button>
                                </div>
                            </template>
                    </ModalInfoHolidays>

                    <ModalInfoHolidays v-if="show_modal_job_range" :isOpen="show_modal_job_range" @modal-close="closeModalInfoHolidays" name="modal-info-holidays" role="document">
                        <template v-slot:modal-header>
                            <p class="text-xl"> <span class="font-bold text-gray-300">Contratos de</span> <span class="text-white">{{modal_job_range.title}} </span> </p>
                        </template>
                        <template v-slot:modal-content>
                            <div class="flex justify-center items-center" v-for="(contract, key) in modal_job_range.current_ranges" :key="key">
                                <div class="grid justify-center items-center">
                                    <div><span class="font-bold text-gray-300">Tipo de contrato:</span> <span class="font-bold text-gray-100">{{ contract.contract_type.name }}</span></div>
                                    <div><span class="font-bold text-gray-300">Jornada horas/día:</span> <span class="font-bold text-gray-100">{{ contract.contract_type.working_hours }}</span></div>
                                    <div><span class="font-bold text-gray-300">Fecha inicio:</span> <span class="font-bold text-gray-100">{{ contract.start_date }}</span></div>
                                    <div><span class="font-bold text-gray-300">Fecha fin:</span> <span class="font-bold text-gray-100">{{ contract.end_date }}</span></div>
                                </div>
                                <div class="grid justify-center items-center">
                                    <input @change="handleCheckboxChangeHolidaysToBeConfirmed(contract)" v-model="contract.selected" :id="key" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"/>
                                </div>
                            </div>
                                <div v-if="modal_holidays_to_be_confirmed.loading" class="flex justify-center items-center min-h-24 pos-relative">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-transparent dark:bg-transparent">
                                        <div role="status">
                                            <svg aria-hidden="true" class="h-10 w-10 animate-spin fill-red-600 text-gray-200 dark:text-gray-400" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor" />
                                            <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill" />
                                            </svg>
                                            <span class="sr-only">Cancelando...</span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template v-slot:modal-footer>
                                <div v-if="!show_modal_job_range" class="flex justify-between mt-5 mx-2">
                                    <button @click="updateHolidays(1)"
                                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-1 px-2 m-1 rounded">Añadir contrato</button>
                                </div>
                                <div v-if="show_modal_job_range" class="flex justify-between mt-5 mx-2">
                                    <button @click="updateHolidays(1)"
                                        class="bg-red-600 hover:bg-red-700 text-white font-bold py-1 px-2 m-1 rounded">Eliminar contrato</button>
                                    <button @click="closeModalInfoHolidays()"
                                        class="bg-gray-700 hover:bg-gray-500 text-white font-bold py-1 px-2 m-1 rounded">Cancelar</button>
                                </div>
                            </template>
                    </ModalInfoHolidays>

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
</style>